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
    public function get($url, $request_headers=array())
    {
        return $this->request('get', $url, $request_headers);
    }
    
    public function post($url, $params=array())
    {
        return $this->request('post', $url, array(), http_build_query($params));
    }
    
    /**
     * @param string $method            HTTP request method (ex: 'get').
     * @param string $url               URL to fetch.
     * @param array $request_headers    dictionary of header names and values.
     * @param string $request_body      content to send in the request.
     * @return array {
     *      'status' => HTTP status code (ex: 200).
     *      'reason' => HTTP reason string (ex: 'OK').
     *      'headers' => Dictionary of headers. (ex: array('Content-Length' => '0')).
     *      'body' => Content of the response.
     * }
     */
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
        
        list($http_version, $_, $reason) = explode(' ', $status_line, 3);
        
        $response = array(
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

/**
 * Thrown when unable to connect to a Splunk server.
 * 
 * @package Splunk
 */
class Splunk_ConnectException extends Exception {}

/**
 * Thrown when an HTTP request fails due to a non 2xx status code.
 * 
 * @package Splunk
 */
class Splunk_HttpException extends Exception
{
    private $response;
    
    // === Init ===
    
    public function __construct($response)
    {
        $detail = Splunk_HttpException::parseFirstMessageFrom($response);
        
        // FIXME: Include HTTP "reason" in message
        $message = "HTTP {$response['status']} {$response['reason']}";
        if ($detail != NULL)
            $message .= ' -- ' . $detail;
        
        $this->response = $response;
        parent::__construct($message);
    }
    
    private static function parseFirstMessageFrom($response)
    {
        return Splunk_Util::getTextContentAtXpath(
            new SimpleXMLElement($response['body']),
            '/response/messages/msg');
    }
    
    // === Accessors ===
    
    public function getResponse()
    {
        return $this->response;
    }
}
