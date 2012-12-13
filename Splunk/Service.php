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
 * Provides an object-oriented interface to access entities of a Splunk server.
 * 
 * @package Splunk
 */
class Splunk_Service extends Splunk_Context
{
    /**
     * @see Splunk_Context::__construct
     */
    public function __construct($args=array())
    {
        parent::__construct($args);
    }
    
    // === Endpoints ===
    
    /**
     * @return Splunk_Collection    The collection of indexes on this server.
     */
    public function getIndexes()
    {
        return new Splunk_Collection($this, 'data/indexes/', 'Splunk_Index');
    }
    
    /**
     * @return Splunk_Jobs          The collection of search jobs on this server.
     */
    public function getJobs()
    {
        return new Splunk_Jobs($this, 'search/jobs/', 'Splunk_Job');
    }
    
    /**
     * @return Splunk_Receiver      An interface to send events to this server.
     */
    public function getReceiver()
    {
        return new Splunk_Receiver($this);
    }
    
    /**
     * @return Splunk_Collection    The collection of saved searches on this server.
     */
    public function getSavedSearches()
    {
        return new Splunk_Collection($this, 'saved/searches/', 'Splunk_SavedSearch');
    }
    
    // === Convenience ===
    
    /**
     * Creates a new search job.
     * 
     * @param string $search    The search query for the job to perform.
     * @param array $args   (optional) Job-specific creation arguments,
     *                      merged with {
     *     'namespace' => (optional) {Splunk_Namespace} The namespace in which
     *                    to create the entity. Defaults to the service's
     *                    namespace.
     * }
     *                      For details, see the
     *                      "POST search/jobs"
     *                      endpoint in the REST API Documentation.
     * @return Splunk_Job
     * @throws Splunk_IOException
     * @link http://docs.splunk.com/Documentation/Splunk/4.3.3/RESTAPI/RESTsearch#search.2Fjobs
     */
    public function search($search, $args=array())
    {
        return $this->getJobs()->create($search, $args);
    }
    
    /**
     * Executes the specified search query and returns results immediately.
     * 
     * @param string $search    The search query for the job to perform.
     * @param array $args   (optional) Job-specific creation arguments,
     *                      merged with {
     *     'namespace' => (optional) {Splunk_Namespace} The namespace in which
     *                    to create the entity. Defaults to the service's
     *                    namespace.
     * }
     *                      For details, see the
     *                      "POST search/jobs"
     *                      endpoint in the REST API Documentation.
     * @return string           The search results, which can be parsed with
     *                          Splunk_ResultsReader.
     * @throws Splunk_IOException
     * @link http://docs.splunk.com/Documentation/Splunk/4.3.3/RESTAPI/RESTsearch#search.2Fjobs
     */
    public function oneshotSearch($search, $args=array())
    {
        return $this->getJobs()->createOneshot($search, $args);
    }
}