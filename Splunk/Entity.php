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
    private $loaded = FALSE;
    private $entry;
    private $content;
    
    /**
     * @param Splunk_Service $service
     * @param string $path
     * @param SimpleXMLElement $entry   (optional) the <entry> for this entity,
     *                                  as received from the REST API.
     *                                  If omitted, will be loaded on demand.
     */
    public function __construct($service, $path, $entry=NULL)
    {
        parent::__construct($service, $path);
        
        $this->entry = $entry;
        if ($this->entry != NULL)
            $this->loadContentsOfEntry();
    }
    
    // === Load ===
    
    /** Loads this resource if not already done. Returns self. */
    protected function validate()
    {
        if (!$this->loaded)
        {
            $this->load();
            assert($this->loaded);
        }
        return $this;
    }
    
    /** Loads this resource. */
    private function load()
    {
        $response = $this->loadResponseFromService();
        $xml = new SimpleXMLElement($response->body);
        
        $this->entry = $this->loadEntryFromResponse($xml);
        $this->loadContentsOfEntry();
    }
    
    /** Fetches this entity's Atom feed from the Splunk server. */
    protected function loadResponseFromService()
    {
        return $this->service->get($this->path);
    }
    
    /** Returns the <entry> element inside the root element. */
    protected function loadEntryFromResponse($xml)
    {
        return $xml->entry;
    }
    
    private function loadContentsOfEntry()
    {
        $this->content = Splunk_AtomFeed::parseValueInside($this->entry->content);
        $this->loaded = TRUE;
    }
    
    // === Accessors ===
    
    public function getName()
    {
        return (string) $this->validate()->entry->title;
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