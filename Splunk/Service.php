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
    public function __construct($args)
    {
        parent::__construct($args);
    }
    
    // === Endpoints ===
    
    /**
     * @return Splunk_Jobs          The collection of search jobs on this server.
     */
    public function getJobs()
    {
        return new Splunk_Jobs($this, 'search/jobs/', 'Splunk_Job');
    }
    
    /**
     * @return Splunk_Collection    The collection of saved searches on this server.
     */
    public function getSavedSearches()
    {
        return new Splunk_Collection($this, 'saved/searches/', 'Splunk_SavedSearch');
    }
}