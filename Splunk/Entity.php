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
 * Represents an entity accessible through Splunk's REST API.
 * 
 * @package Splunk
 */
class Splunk_Entity extends Splunk_Endpoint implements ArrayAccess
{
    private $data;
    private $content;
    
    /**
     * @param Splunk_Service $service
     * @param string $path
     * @param SimpleXMLElement $data    (optional) The XML of this entity,
     *                                  as received from the REST API.
     *                                  If omitted, will be loaded on demand.
     */
    public function __construct($service, $path, $data=NULL)
    {
        parent::__construct($service, $path);
        
        $this->data = $data;
        if ($this->data != NULL)
            $this->loadContentsOfData();
    }
    
    // === Load ===
    
    protected function load()
    {
        $response = $this->service->get($this->path);
        $xml = new SimpleXMLElement($response->body);
        
        $this->data = $xml->entry;
        $this->loadContentsOfData();
    }
    
    private function loadContentsOfData()
    {
        $this->content = Splunk_AtomFeed::parseValueInside($this->data->content);
        $this->loaded = TRUE;
    }
    
    // === Accessors ===
    
    public function getName()
    {
        return (string) $this->validate()->data->title;
    }
    
    // === ArrayAccess Methods ===
    
    public function offsetGet($key)
    {
        return $this->validate()->content[$key];
    }
    
    public function offsetSet($key, $value)
    {
        throw new Splunk_UnsupportedOperationException();
    }
    
    public function offsetUnset($key)
    {
        throw new Splunk_UnsupportedOperationException();
    }
    
    public function offsetExists($key)
    {
        return isset($this->validate()->content[$key]);
    }
}