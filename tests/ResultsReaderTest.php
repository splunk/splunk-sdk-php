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

class ResultsReaderTest extends SplunkTest
{
    public function testReadSimpleSearchResults()
    {
        $xmlText = file_get_contents('./tests/data/simpleSearchResults.xml');
        $expectedResults = array(
            new Splunk_Message('DEBUG', 'base lispy: [ AND ]'),
            new Splunk_Message('DEBUG', 'search context: user="admin", app="search", bs-pathname="/some/path"'),
            array(
                'series' => 'twitter',
                'sum(kb)' => '14372242.758775',
            ),
            array(
                'series' => 'splunkd',
                'sum(kb)' => '267802.333926',
            ),
            array(
                'series' => 'flurry',
                'sum(kb)' => '12576.454102',
            ),
            array(
                'series' => 'splunkd_access',
                'sum(kb)' => '5979.036338',
            ),
            array(
                'series' => 'splunk_web_access',
                'sum(kb)' => '5838.935649',
            ),
        );
        
        $resultsReader = new Splunk_ResultsReader($xmlText);
        
        $results = array();
        foreach ($resultsReader as $result)
            $results[] = $result;
        
        $this->assertEquals($expectedResults, $results);
    }
}