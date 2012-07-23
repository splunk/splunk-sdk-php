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
 * Represents a response received from an HTTP request.
 * 
 * Has the following read-only properties:
 * - status {integer}   HTTP status code (ex: 200).
 * - reason {string}    HTTP reason string (ex: 'OK').
 * - headers {array}    Dictionary of headers. (ex: array('Content-Length' => '0')).
 * - body {string}      Content of the response.
 */
class Splunk_HttpResponse
{
    private $state;
    
    public function __construct($state)
    {
        $this->state = $state;
    }
    
    public function __get($key)
    {
        return $this->state[$key];
    }
}
