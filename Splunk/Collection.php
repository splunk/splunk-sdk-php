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
     * @param array $args (optional) {
     *     'namespace' => (optional) {Splunk_Namespace} The namespace in which
     *                    to list entities. Defaults to the service's namespace.
     *     
     *     'count' => (optional) The maximum number of items to return,
     *                or -1 to return as many as possible.
     *                Defaults to returning as many as possible.
     *     'offset' => (optional) The offset of the first item to return.
     *                 Defaults to 0.
     *     
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
     * @throws Splunk_HttpException
     */
    // NOTE: This method isn't called 'list' only because PHP treats 'list' as a
    //       pseudo-keyword and gets confused when it's used as a method name.
    public function items($args=array())
    {
        $args = array_merge(array(
            'count' => -1,
        ), $args);
        
        if ($args['count'] <= 0 && $args['count'] != -1)
            throw new InvalidArgumentException(
                'Count must be positive or -1 (infinity).');
        
        if ($args['count'] == -1)
            $args['count'] = 0;     // infinity value for the REST API
        
        $response = $this->service->get($this->path, $args);
        return $this->loadEntitiesFromResponse($response);
    }
    
    /**
     * @param $response
     * @return array                        array of Splunk_Entry.
     */
    private function loadEntitiesFromResponse($response)
    {
        $xml = new SimpleXMLElement($response->body);
        
        $entities = array();
        foreach ($xml->entry as $entry)
        {
            $entities[] = $this->loadEntityFromEntry($entry);
        }
        return $entities;
    }
    
    /**
     * @param SimpleXMLElement $entry       an <entry> element.
     * @return Splunk_Entry
     */
    private function loadEntityFromEntry($entry)
    {
        return new $this->entitySubclass(
            $this->service,
            $this->getEntityPath($entry->title),
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
     * @throws Splunk_NoSuchEntityException
     *                      If no such entity exists.
     * @throws Splunk_AmbiguousEntityNameException
     *                      If multiple entities with the specified name
     *                      exist in the specified namespace.
     * @throws Splunk_HttpException
     */
    public function get($name, $namespace=NULL)
    {
        $this->checkName($name);
        
        try
        {
            $response = $this->service->get($this->getEntityPath($name), array(
                'namespace' => $namespace,
                'count' => 0,
            ));
            $entities = $this->loadEntitiesFromResponse($response);
        }
        catch (Splunk_HttpException $e)
        {
            if ($e->getResponse()->status == 404)
                $entities = array();
            else
                throw $e;
        }
        
        if (count($entities) == 0)
        {
            throw new Splunk_NoSuchEntityException($name);
        }
        else if (count($entities) == 1)
        {
            return $entities[0];
        }
        else
        {
            throw new Splunk_AmbiguousEntityNameException($name);
        }
    }
    
    /**
     * Returns a reference to the unique entity with the specified name in this
     * collection. Loading of the entity is deferred until its first use.
     * 
     * @param string $name  The name of the entity to search for.
     * @param Splunk_Namespace|NULL $namespace
     *                      (optional) {Splunk_Namespace} The namespace in which
     *                      to search. Defaults to the service's namespace.
     * @return Splunk_Entity
     */
    public function getReference($name, $namespace=NULL)
    {
        $this->checkName($name);
        
        return new $this->entitySubclass(
            $this->service,
            $this->getEntityPath($name),
            NULL,
            $namespace);
    }
    
    /**
     * Creates a new entity in this collection.
     * 
     * @param string $name  The name of the entity to create.
     * @param array $args (optional) Entity-specific creation arguments,
     *                    merged with {
     *     'namespace' => (optional) {Splunk_Namespace} The namespace in which
     *                    to create the entity. Defaults to the service's
     *                    namespace.
     * }
     * @return Splunk_Entity
     * @throws Splunk_HttpException
     */
    public function create($name, $args=array())
    {
        $this->checkName($name);
        
        $args = array_merge(array(
            'name' => $name,
        ), $args);
        
        $response = $this->service->post($this->path, $args);
        if ($response->body === '')
        {
            // This endpoint doesn't return the content of the new entity.
            return $this->getReference($name);
        }
        else
        {
            $xml = new SimpleXMLElement($response->body);
            return $this->loadEntityFromEntry($xml->entry);
        }
    }
    
    /**
     * Deletes an entity from this collection.
     * 
     * @param string $name  The name of the entity to delete.
     * @param array $args (optional) Entity-specific deletion arguments,
     *                    merged with {
     *     'namespace' => (optional) {Splunk_Namespace} The namespace in which
     *                    to find the entity. Defaults to the service's
     *                    namespace.
     * }
     * @throws Splunk_HttpException
     */
    public function delete($name, $args=array())
    {
        $this->checkName($name);
        
        $this->service->delete($this->getEntityPath($name), $args);
    }
    
    // === Utility ===
    
    /**
     * Returns the path of the child entity with the specified name.
     */
    private function getEntityPath($name)
    {
        return $this->path . urlencode($name);
    }
    
    /**
     * Ensures that the specified name is not NULL or empty.
     */
    private function checkName($name)
    {
        if ($name === NULL || $name === '')
            throw new InvalidArgumentException('Invalid empty name.');
    }
}
