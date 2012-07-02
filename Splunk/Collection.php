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
 * Represents a collection of entities accessible through Splunk's REST API.
 * 
 * @package Splunk
 */
class Splunk_Collection extends Splunk_Endpoint
{
    private $entitySubclass;
    
    /**
     * @param Splunk_Service $service
     * @param string $path
     * @param string $entitySubclass    (optional) name of the entity subclass
     *                                  that this collection's children will
     *                                  be instantiated with.
     */
    public function __construct($service, $path, $entitySubclass='Splunk_Entity')
    {
        parent::__construct($service, $path);
        $this->entitySubclass = $entitySubclass;
    }
    
    // === Operations ===
    
    /**
     * Lists this collection's entities, returning a list of loaded entities.
     * 
     * By default, all items in the collection are returned. For large
     * collections, it is advisable to fetch items using multiple calls with
     * the paging options (i.e. 'offset' and 'count').
     * 
     * @return array    the entities in the listing.
     */
    // NOTE: This method isn't called 'list' only because PHP treats 'list' as a
    //       pseudo-keyword and gets confused when it's used as a method name.
    public function enumerate()
    {
        $response = $this->service->get($this->path);
        $xml = new SimpleXMLElement($response->body);
        
        $entities = array();
        foreach ($xml->entry as $entry)
        {
            $entities[] = $this->loadEntityFromEntry($entry);
        }
        
        return $entities;
    }
    
    private function loadEntityFromEntry($entry)
    {
        return new $this->entitySubclass(
            $this->service,
            $this->path . urlencode($entry->title),
            $entry);
    }
    
    /**
     * Returns the unique entity with the specified name in this collection.
     * 
     * @param string $name
     * @return Splunk_Entity
     * @throws Splunk_NoSuchKeyException
     * @throws Splunk_AmbiguousKeyException
     */
    public function get($name)
    {
        $entities = $this->enumerate();
        
        $results = array();
        foreach ($entities as $entity)
        {
            if ($entity->getName() == $name)
            {
                $results[] = $entity;
            }
        }
        
        if (count($results) == 0)
        {
            throw new Splunk_NoSuchKeyException(
                "No value exists with key '{$name}'.");
        }
        else if (count($results) == 1)
        {
            return $results[0];
        }
        else
        {
            throw new Splunk_AmbiguousKeyException(
                "Multiple values exist with key '{$name}'. " .
                "Specify a namespace to disambiguate.");
        }
    }
    
    /**
     * Returns a reference to the unique entity with the specified name in this
     * collection. Loading of the entity is deferred until its first use.
     * 
     * @param string $name
     * @return Splunk_Entity
     */
    public function getReference($name)
    {
        return new $this->entitySubclass(
            $this->service,
            $this->path . urlencode($name));
    }
}
