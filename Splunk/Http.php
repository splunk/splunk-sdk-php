<?php
/**
 * Copyright 2013 Splunk, Inc.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"): you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * HTTP abstraction layer.
 * 
 * @package Splunk
 * @internal
 */
class Splunk_Http
{
    /**
     * @param array $params     (optional) query parameters.
     * @see request()
     */
    public function get($url, $params=array(), $requestHeaders=array())
    {
        return $this->requestWithParams('get', $url, $params, $requestHeaders);
    }
    
    /**
     * @param array $params     (optional) form parameters to send in the request body.
     * @see request()
     */
    public function post($url, $params=array(), $requestHeaders=array())
    {
        $requestHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
        
        return $this->request(
            'post', $url, $requestHeaders, http_build_query($params));
    }
    
    /**
     * @param array $params     (optional) query parameters.
     * @see request()
     */
    public function delete($url, $params=array(), $requestHeaders=array())
    {
        return $this->requestWithParams('delete', $url, $params, $requestHeaders);
    }
    
    private function requestWithParams(
        $method, $url, $params, $requestHeaders)
    {
        $fullUrl = ($params === NULL || count($params) == 0)
            ? $url
            : $url . '?' . http_build_query($params);
        
        return $this->request($method, $fullUrl, $requestHeaders);
    }
    
    /**
     * Sends an HTTP request and returns the response.
     * 
     * @param string $method            HTTP request method (ex: 'get').
     * @param string $url               URL to fetch.
     * @param array $requestHeaders     (optional) dictionary of header names and values.
     * @param string $requestBody       (optional) content to send in the request.
     * @return Splunk_HttpResponse
     * @throws Splunk_IOException
     */
    public function request(
        $method, $url, $requestHeaders=array(), $requestBody='')
    {
        $isHttp = (substr($url, 0, strlen('http:')) === 'http:');
        $isHttps = (substr($url, 0, strlen('https:')) === 'https:');
        
        if (!$isHttp && !$isHttps)
        {
            throw new InvalidArgumentException(
                'URL scheme must be either HTTP or HTTPS.');
        }
        
        // The HTTP stream wrapper in PHP < 5.3.7 has a bug which
        // injects junk at the end of HTTP requests, which breaks
        // SSL connections. Fallback to cURL-based requests.
        if ($isHttps && (version_compare(PHP_VERSION, '5.3.7') < 0))
            return $this->requestWithCurl(
                $method, $url, $requestHeaders, $requestBody);
        
        $requestHeaderLines = array();
        foreach ($requestHeaders as $k => $v)
            $requestHeaderLines[] = "{$k}: {$v}";
        
        $fopenContext = stream_context_create(array(
            'http' => array(
                'method' => strtoupper($method),
                'header' => $requestHeaderLines,
                'content' => $requestBody,
                'follow_location' => 0,     // don't follow HTTP 3xx automatically
                'max_redirects' => 0,       // [PHP 5.2] don't follow HTTP 3xx automatically
                'ignore_errors' => TRUE,    // don't throw exceptions on bad status codes
            ),
        ));
        
        // NOTE: PHP does not perform certificate validation for HTTPS URLs.
        // NOTE: fopen() magically sets the $http_response_header local variable.
        $bodyStream = @fopen($url, 'rb', /*use_include_path=*/FALSE, $fopenContext);
        if ($bodyStream === FALSE)
        {
            $errorInfo = error_get_last();
            $errmsg = $errorInfo['message'];
            $errno = $errorInfo['type'];
            throw new Splunk_ConnectException($errmsg, $errno);
        }
        
        $headers = array();
        $headerLines = $http_response_header;
        $statusLine = array_shift($headerLines);
        foreach ($headerLines as $line)
        {
            list($key, $value) = explode(':', $line, 2);
            $headers[$key] = trim($value);
        }
        
        $statusLineComponents = explode(' ', $statusLine, 3);
        $httpVersion = $statusLineComponents[0];
        $status = intval($statusLineComponents[1]);
        $reason = (count($statusLineComponents) == 3)
            ? $statusLineComponents[2]
            : '';
        
        $response = new Splunk_HttpResponse(array(
            'status' => $status,
            'reason' => $reason,
            'headers' => $headers,
            'bodyStream' => $bodyStream,
        ));
        
        if ($status >= 400)
            throw new Splunk_HttpException($response);
        else
            return $response;
    }
    
    private function requestWithCurl(
        $method, $url, $requestHeaders=array(), $requestBody='')
    {
        $opts = array(
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 60,  // secs
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => TRUE,
            // disable SSL certificate validation
            CURLOPT_SSL_VERIFYPEER => FALSE,
        );
        
        foreach ($requestHeaders as $k => $v)
            $opts[CURLOPT_HTTPHEADER][] = "$k: $v";
        
        switch ($method)
        {
            case 'get':
                $opts[CURLOPT_HTTPGET] = TRUE;
                break;
            case 'post':
                $opts[CURLOPT_POST] = TRUE;
                $opts[CURLOPT_POSTFIELDS] = $requestBody;
                break;
            default:
                $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
                break;
        }
        
        if (!($curl = curl_init()))
            throw new Splunk_ConnectException('Unable to initialize cURL.');
        if (!(curl_setopt_array($curl, $opts)))
            throw new Splunk_ConnectException(curl_error($curl));
        // NOTE: The entire HTTP response is read into memory here,
        //       which could be very large. Unfortunately the cURL
        //       interface does not provide a streaming alternative.
        //       To avoid this problem, use PHP 5.3.7+, which doesn't
        //       need cURL to perform HTTP requests.
        if (!($response = curl_exec($curl)))
            throw new Splunk_ConnectException(curl_error($curl));
        
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerText = substr($response, 0, $headerSize);
        $body = (strlen($response) == $headerSize)
            ? ''
            : substr($response, $headerSize);
        
        $headers = array();
        $headerLines = explode("\r\n", trim($headerText));
        $statusLine = array_shift($headerLines);
        foreach ($headerLines as $line)
        {
            list($key, $value) = explode(':', $line, 2);
            $headers[$key] = trim($value);
        }
        
        $statusLineComponents = explode(' ', $statusLine, 3);
        $httpVersion = $statusLineComponents[0];
        $reason = count($statusLineComponents) == 3 ? $statusLineComponents[2] : '';
        
        $response = new Splunk_HttpResponse(array(
            'status' => $status,
            'reason' => $reason,
            'headers' => $headers,
            'body' => $body,
        ));
        
        if ($status >= 400)
            throw new Splunk_HttpException($response);
        else
            return $response;
    }
}
