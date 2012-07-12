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
    public function get($url, $params=array(), $request_headers=array())
    {
        return $this->requestWithParams('get', $url, $params, $request_headers);
    }
    
    /**
     * @param array $params     (optional) form parameters to send in the request body.
     * @see request()
     */
    public function post($url, $params=array(), $request_headers=array())
    {
        return $this->request(
            'post', $url, $request_headers, http_build_query($params));
    }
    
    /**
     * @param array $params     (optional) query parameters.
     * @see request()
     */
    public function delete($url, $params=array(), $request_headers=array())
    {
        return $this->requestWithParams('delete', $url, $params, $request_headers);
    }
    
    private function requestWithParams(
        $method, $url, $params, $request_headers)
    {
        $fullUrl = ($params === NULL || count($params) == 0)
            ? $url
            : $url . '?' . http_build_query($params);
        
        return $this->request($method, $fullUrl, $request_headers);
    }
    
    /**
     * @param string $method            HTTP request method (ex: 'get').
     * @param string $url               URL to fetch.
     * @param array $request_headers    (optional) dictionary of header names and values.
     * @param string $request_body      (optional) content to send in the request.
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
        $method, $url, $request_headers=array(), $request_body='')
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
        
        foreach ($request_headers as $k => $v)
            $opts[CURLOPT_HTTPHEADER][] = "$k: $v";
        
        switch ($method)
        {
            case 'get':
                $opts[CURLOPT_HTTPGET] = TRUE;
                break;
            case 'post':
                $opts[CURLOPT_POST] = TRUE;
                $opts[CURLOPT_POSTFIELDS] = $request_body;
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
        
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header_text = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        $headers = array();
        $header_lines = explode("\r\n", trim($header_text));
        $status_line = array_shift($header_lines);
        foreach ($header_lines as $line)
        {
            list($key, $value) = explode(':', $line, 2);
            $headers[$key] = trim($value);
        }
        
        $status_line_components = explode(' ', $status_line, 3);
        $http_version = $status_line_components[0];
        $reason = count($status_line_components) == 3 ? $status_line_components[2] : '';
        
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
