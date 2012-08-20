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

require_once 'SplunkTest.php';

class SavedSearchTest extends SplunkTest
{
    // (This search is installed by default on Splunk 4.x.)
    const SAVED_SEARCH_NAME = 'Errors in the last 24 hours';
    
    public function testGet()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->getReference(
            self::SAVED_SEARCH_NAME);
        return $savedSearch;
    }
    
    /** @depends testGet */
    public function testDispatch($savedSearch)
    {
        $job = $savedSearch->dispatch();
        $this->assertEquals('1', $job['isSavedSearch']);
    }
    
    public function testDispatchInNamespace()
    {
        $service = $this->loginToRealService();
        
        // Setup
        $savedSearchName = $this->createTempName();
        $savedSearch = $service->getSavedSearches()->create($savedSearchName, array(
            'namespace' => Splunk_Namespace::createApp('launcher'),
            'search' => 'index=_internal | head 1',
        ));
        
        // Test
        $job = $savedSearch->dispatch();
        
        // Teardown
        $savedSearch->delete();
    }
}
