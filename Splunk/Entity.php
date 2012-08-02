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
        if ($this->entry != NULL)
            $this->parseContentsFromEntry();
        
        $this->namespace = $namespace;
    }
    
    // === Load ===
    
    /**
     * Loads this resource if not already done. Returns self.
     * 
     * @return Splunk_Entity            This entity.
     * @throws Splunk_HttpException
     */
    protected function validate($fetchArgs=array())
    {
        if (!$this->loaded)
        {
            $this->load($fetchArgs);
            assert($this->loaded);
        }
        return $this;
    }
    
    /**
     * Loads this resource.
     * 
     * @throws Splunk_HttpException
     */
    private function load($fetchArgs)
    {
        $response = $this->fetch($fetchArgs);
        $xml = new SimpleXMLElement($response->body);
        
        $this->entry = $this->extractEntryFromRootXmlElement($xml);
        $this->parseContentsFromEntry();
    }
    
    /**
     * Fetches this entity's Atom feed from the Splunk server.
     * 
     * @throws Splunk_HttpException
     */
    protected function fetch($fetchArgs)
    {
        return $this->service->get($this->path, array(
            'namespace' => $this->namespace,
        ));
    }
    
    /** Returns the <entry> element inside the root element. */
    protected function extractEntryFromRootXmlElement($xml)
    {
        if (!Splunk_XmlUtil::isSingleElement($xml->entry))
        {
            // Extract name from path since we can't extract it from the
            // entity content here.
            $pathComponents = explode('/', $this->path);
            $name = $pathComponents[count($pathComponents) - 1];
            
            throw new Splunk_AmbiguousEntityNameException($name);
        }
        
        return $xml->entry;
    }
    
    private function parseContentsFromEntry()
    {
        $this->content = Splunk_AtomFeed::parseValueInside($this->entry->content);
        $this->loaded = TRUE;
    }
    
    protected function isLoaded()
    {
        return $this->loaded;
    }
    
    /**
     * Forcefully reloads this entity from the Splunk server.
     * 
     * @return Splunk_Entity            This entity.
     * @throws Splunk_HttpException
     */
    public function reload()
    {
        if ($this->loaded)
        {
            // Remember this entity's exact namespace, so that a reload
            // will occur in the correct namespace.
            $this->namespace = $this->getNamespace();
        }
        
        $this->loaded = FALSE;
        return $this->validate();
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
        // If this is an entity reference with an exact namespace, return it
        if (!$this->loaded)
        {
            $effectiveNamespace = $this->namespace;
            if ($effectiveNamespace === NULL)
                $effectiveNamespace = $this->service->getNamespace();
            if ($effectiveNamespace->isExact())
                return $effectiveNamespace;
        }
        
        // Extract the namespace from this entity's content
        $acl = $this['eai:acl'];
        return Splunk_Namespace::createExact(
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
    
    /**
     * Updates this entity's properties.
     * 
     * Note that the "name" property cannot be updated.
     * 
     * @param array $args   Dictionary of properties that will be changed,
     *                      along with their new values.
     * @return              This entity.
     * @throws Splunk_HttpException
     */
    public function update($args)
    {
        if (array_key_exists('name', $args))
            throw new InvalidArgumentException(
                'Cannot update the name of an entity.');
        if (array_key_exists('namespace', $args))
            throw new InvalidArgumentException(
                'Cannot override the entity\'s namespace.');
        
        $params = $args;    // copy by value
        
        // Update entity on server
        $args['namespace'] = $this->getNamespace();
        $this->service->post($this->path, $args);
        
        // Update cached content of entity
        if ($this->loaded)
            $this->content = array_merge($this->content, $params);
        
        return $this;
    }
}