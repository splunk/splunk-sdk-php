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

require_once '../Splunk.php';
require_once 'settings.php';

class SavedSearchTest extends PHPUnit_Framework_TestCase
{
    // (This search is installed by default on Splunk 4.x.)
    const SAVED_SEARCH_NAME = 'Errors in the last 24 hours';
    const SAVED_SEARCH_QUERY = 'error OR failed OR severe OR ( sourcetype=access_* ( 404 OR 500 OR 503 ) )';
    
    public function testGetSavedSearchCollectionUsingContext()
    {
        global $Splunk_testSettings;
        $context = new Splunk_Service($Splunk_testSettings['connectArgs']);
        $context->login();
        
        $response = $context->get('/servicesNS/nobody/search/saved/searches/');
        $this->assertContains(
            '<title>' . self::SAVED_SEARCH_NAME . '</title>',
            $response->body);
    }
    
    public function testGetSavedSearch()
    {
        global $Splunk_testSettings;
        $service = new Splunk_Service($Splunk_testSettings['connectArgs']);
        $service->login();
        
        $savedSearch = $service->savedSearches()->get(self::SAVED_SEARCH_NAME);
        return $savedSearch;
    }
    
    /**
     * @depends testGetSavedSearch
     */
    public function testGetPropertyOfSavedSearch($savedSearch)
    {
        $this->assertEquals(
            self::SAVED_SEARCH_QUERY,
            $savedSearch['search']);
    }
}