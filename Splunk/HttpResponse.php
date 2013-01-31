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
 * Represents a response received from an HTTP request.
 * 
 * When reading a potentially large response, the 'bodyStream'
 * property should be used used in preference to the 'body' property,
 * since it will only keep the current part of the body loaded in memory.
 * 
 * @package Splunk
 * @internal
 * 
 * @property-read integer $status   HTTP status code (ex: 200).
 * @property-read string $reason    HTTP reason string (ex: 'OK').
 * @property-read array $headers    Dictionary of headers.
 *                                  (ex: array('Content-Length' => '0')).
 * @property-read string $body      Content of the response,
 *                                  as a single byte string.
 * @property-read resource $bodyStream
 *                                  Content of the response, as a stream
 *                                  (of the type returned by fopen()).
 */
class Splunk_HttpResponse
{
    private $state;
    private $body;          // lazy
    private $bodyStream;    // lazy
    
    /* @internal */
    public function __construct($state)
    {
        $this->state = $state;
        $this->body = NULL;
        $this->bodyStream = NULL;
    }
    
    // === Accessors ===
    
    /** @internal */
    public function __get($key)
    {
        if ($key === 'body')
            return $this->getBody();
        else if ($key === 'bodyStream')
            return $this->getBodyStream();
        else
            return $this->state[$key];
    }
    
    private function getBody()
    {
        if (array_key_exists('body', $this->state))
            return $this->state['body'];
        
        if ($this->body === NULL)
        {
            if (!array_key_exists('bodyStream', $this->state))
                throw new Splunk_UnsupportedOperationException(
                    'Response object does not contain body stream.');
            
            $this->body = Splunk_Util::stream_get_contents(
                $this->state['bodyStream']);
        }
        return $this->body;
    }
    
    private function getBodyStream()
    {
        if (array_key_exists('bodyStream', $this->state))
            return $this->state['bodyStream'];
        
        if ($this->bodyStream === NULL)
        {
            if (!array_key_exists('body', $this->state))
                throw new Splunk_UnsupportedOperationException(
                    'Response object does not contain body.');
            
            $this->bodyStream = Splunk_StringStream::create($this->state['body']);
        }
        return $this->bodyStream;
    }
}
