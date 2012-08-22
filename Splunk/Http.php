<?php
/**
 * Copyright 2012 Splunk, Inc.
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
     * Performs an HTTP request and returns the response.
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
        if ((substr($url, 0, strlen('http:')) !== 'http:') &&
            (substr($url, 0, strlen('https:')) !== 'https:'))
        {
            throw new InvalidArgumentException(
                'URL scheme must be either HTTP or HTTPS.');
        }
        
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
            if (version_compare(PHP_VERSION, '5.2.0') >= 0)
            {
                $errorInfo = error_get_last();      // requires PHP >= 5.2.0
                $errmsg = $errorInfo['message'];
                $errno = $errorInfo['type'];
            }
            else
            {
                $errmsg = 'fopen failed.';
                $errno = 0;
            }
            throw new Splunk_ConnectException($errmsg, $errno);
        }
        
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
}
