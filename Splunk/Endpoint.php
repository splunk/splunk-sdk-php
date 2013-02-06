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
 * Represents a single endpoint in the Splunk REST API.
 * 
 * @package Splunk
 */
abstract class Splunk_Endpoint
{
    protected $service;
    protected $path;
    
    /** @internal */
    public function __construct($service, $path)
    {
        $this->service = $service;
        $this->path = $path;
    }
    
    // === Accessors ===
    
    /**
     * Returns the namespace in which this endpoint resides, or NULL to use 
     *      the context's default namespace.
     *
     * @return Splunk_Namespace|NULL    The namespace in which this endpoint
     *                                  resides, or NULL to use the context's
     *                                  default namespace.
     *                                  Possibly a non-exact namespace.
     */
    protected abstract function getSearchNamespace();
    
    // === HTTP ===
    
    /**
     * Sends an HTTP GET request relative to this endpoint.
     * 
     * @param string $relativePath  relative URL path.
     * @param array $args   (optional) query parameters, merged with {<br/>
     *     **namespace**: (optional) namespace to use, or NULL to use
     *                    the context's default namespace.<br/>
     * }
     * @return Splunk_HttpResponse
     * @throws Splunk_IOException
     * @see Splunk_Http::get()
     */
    public function sendGet($relativePath, $args=array())
    {
        return $this->sendSimpleRequest('sendGet', $relativePath, $args);
    }
    
    /**
     * Sends an HTTP POST request relative to this endpoint.
     * 
     * @param string $relativePath  relative URL path.
     * @param array $args   (optional) form parameters to send in the request 
     *                      body, merged with {<br/>
     *     **namespace**: (optional) namespace to use, or NULL to use
     *                    the context's default namespace.<br/>
     * }
     * @return Splunk_HttpResponse
     * @throws Splunk_IOException
     * @see Splunk_Http::post()
     */
    public function sendPost($relativePath, $args=array())
    {
        return $this->sendSimpleRequest('sendPost', $relativePath, $args);
    }
    
    /**
     * Sends an HTTP DELETE request relative to this endpoint.
     * 
     * @param string $relativePath  relative URL path.
     * @param array $args   (optional) query parameters, merged with {<br/>
     *     **namespace**: (optional) namespace to use, or NULL to use
     *                    the context's default namespace.<br/>
     * }
     * @return Splunk_HttpResponse
     * @throws Splunk_IOException
     * @see Splunk_Http::delete()
     */
    public function sendDelete($relativePath, $args=array())
    {
        return $this->sendSimpleRequest('sendDelete', $relativePath, $args);
    }
    
    /** Sends a simple request relative to this endpoint. */
    private function sendSimpleRequest($method, $relativePath, $args=array())
    {
        $args = array_merge(array(
            'namespace' => $this->getSearchNamespace(),
        ), $args);
        
        return $this->service->$method("{$this->path}{$relativePath}", $args);
    }
}