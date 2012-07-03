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
     * @param $args (optional) {
     *     'namespace' => (optional) {Splunk_Namespace} The namespace in which
     *                    to list entities. Defaults to the service's namespace.
     *     'count' => (optional) The maximum number of items to return.
     *                Defaults to returning all items.
     *     'offset' => (optional) The offset of the first item to return.
     *                 Defaults to 0.
     *     'search' => (optional) The search expression to filter responses
     *                 with. For example, "foo" matches any object that has
     *                 "foo" in a substring of a field. Similarly the
     *                 expression "field_name=field_value" matches only objects
     *                 that have a "field_name" field with the value
     *                 "field_value".
     *     'sort_dir' => (optional) The direction to sort returned items.
     *                   Valid values:
     *                   - "asc": Sort in ascending order.
     *                   - "desc": Sort in descending order.
     *                   Defaults to "asc".
     *     'sort_key' => (optional) The field to use for sorting.
     *                   Defaults to "name".
     *     'sort_mode' => (optional) The sorting algorithm to use. Valid values:
     *                    - "auto": If all values of the field are numbers,
     *                              sort numerically. Otherwise, sort
     *                              alphabetically.
     *                    - "alpha": Sort alphabetically.
     *                    - "alpha_case": Sort alphabetically, case-sensitive.
     *                    - "num": Sort numerically.
     *                    Defaults to "auto".
     * }
     * @return array    the entities in the listing.
     */
    // NOTE: This method isn't called 'list' only because PHP treats 'list' as a
    //       pseudo-keyword and gets confused when it's used as a method name.
    public function enumerate($args=array())
    {
        $args = array_merge(array(
            'count' => 0,
        ), $args);
        
        $response = $this->service->get($this->path, $args);
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
     * @param string $name  The name of the entity to search for.
     * @param Splunk_Namespace|NULL $namespace
     *                      (optional) {Splunk_Namespace} The namespace in which
     *                      to search. Defaults to the service's namespace.
     * @return Splunk_Entity
     * @throws Splunk_NoSuchKeyException
     *                      If no such entity exists.
     * @throws Splunk_AmbiguousKeyException
     *                      If multiple entities with the specified name
     *                      exist in the specified namespace.
     */
    public function get($name, $namespace=NULL)
    {
        $entities = $this->enumerate(array(
            'namespace' => $namespace,
        ));
        
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
     * @param Splunk_Namespace|NULL $namespace
     * @return Splunk_Entity
     */
    public function getReference($name, $namespace=NULL)
    {
        return new $this->entitySubclass(
            $this->service,
            $this->path . urlencode($name),
            NULL,
            $namespace);
    }
}
