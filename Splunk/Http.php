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
     * @param string $method            HTTP request method (ex: 'get').
     * @param string $url               URL to fetch.
     * @param array $requestHeaders     (optional) dictionary of header names and values.
     * @param string $requestBody       (optional) content to send in the request.
     * @return object {
     *      'status' => HTTP status code (ex: 200).
     *      'reason' => HTTP reason string (ex: 'OK').
     *      'headers' => Dictionary of headers. (ex: array('Content-Length' => '0')).
     *      'body' => Content of the response.
     * }
     * @throws Splunk_ConnectException
     * @throws Splunk_HttpException
     */
    // TODO: Avoid reading the entire response into memory.
    //       
    //       cURL provides no straightforward way to avoid this, short of
    //       downloading a response to file and reading that file.
    //       
    //       For continuous streams (that don't end), this solution
    //       won't work because the temporary file will never finish being
    //       written.
    private function request(
        $method, $url, $requestHeaders=array(), $requestBody='')
    {
        $opts = array(
            CURLOPT_HTTPGET => TRUE,
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
        if (!($response = curl_exec($curl)))
            throw new Splunk_ConnectException(curl_error($curl));
        
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerText = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
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
        
        $response = (object) array(
            'status' => $status,
            'reason' => $reason,
            'headers' => $headers,
            'body' => $body,
        );
        
        if ($status >= 400)
            throw new Splunk_HttpException($response);
        else
            return $response;
    }
}
