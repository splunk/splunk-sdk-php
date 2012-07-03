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
    private $namespace;
    private $content;
    
    /**
     * @param Splunk_Service $service
     * @param string $path
     * @param SimpleXMLElement|NULL $entry
     *                                  (optional) The <entry> for this entity,
     *                                  as received from the REST API.
     *                                  If omitted, will be loaded on demand.
     * @param Splunk_Namespace|NULL $namespace
     *                                  (optional) The namespace from which to
     *                                  load this entity, or NULL to use the
     *                                  $service object's default namespace.
     *                                  Does not apply if this entity is already
     *                                  loaded (i.e. if $entry is not NULL).
     */
    public function __construct($service, $path, $entry=NULL, $namespace=NULL)
    {
        parent::__construct($service, $path);
        
        $this->entry = $entry;
        $this->namespace = $namespace;
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
        return $this->service->get($this->path, array(
            'namespace' => $this->namespace,
        ));
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
    
    /**
     * @return array                The properties of this entity.
     */
    public function getContent()
    {
        return $this->validate()->content;
    }
    
    /**
     * @return string               The name of this entity.
     */
    public function getName()
    {
        return (string) $this->validate()->entry->title;
    }
    
    /**
     * @return Splunk_Namespace     The non-wildcarded namespace that this
     *                              entity resides in.
     */
    public function getNamespace()
    {
        $acl = $this['eai:acl'];
        return Splunk_Namespace::exact(
            $acl['owner'], $acl['app'], $acl['sharing']);
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
    
    // === Operations ===
    
    /**
     * Deletes this entity.
     * 
     * @throws Splunk_HttpException
     */
    public function delete()
    {
        $this->service->delete($this->path, array(
            'namespace' => $this->getNamespace(),
        ));
    }
}