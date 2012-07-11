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
        
        $this->assertParsedResultsEquals($expectedResults, $xmlText);
    }
    
    public function testReadResultsWithoutMessages()
    {
        $xmlText = trim("
<?xml version='1.0' encoding='UTF-8'?>
<results preview='0'>
    <meta>
        <fieldOrder>
            <field>series</field>
            <field>sum(kb)</field>
        </fieldOrder>
    </meta>
	<result offset='0'>
		<field k='series'>
			<value><text>twitter</text></value>
		</field>
		<field k='sum(kb)'>
			<value><text>14372242.758775</text></value>
		</field>
	</result>
</results>
");
        $expectedResults = array(
            array(
                'series' => 'twitter',
                'sum(kb)' => '14372242.758775',
            ),
        );
        
        $this->assertParsedResultsEquals($expectedResults, $xmlText);
    }
    
    public function testReadResultsWithoutResults()
    {
        $xmlText = trim("
<?xml version='1.0' encoding='UTF-8'?>
<results preview='0'>
    <meta>
        <fieldOrder>
            <field>series</field>
            <field>sum(kb)</field>
        </fieldOrder>
    </meta>
    <messages>
        <msg type='DEBUG'>base lispy: [ AND ]</msg>
    </messages>
</results>
");
        $expectedResults = array(
            new Splunk_Message('DEBUG', 'base lispy: [ AND ]'),
        );
        
        $this->assertParsedResultsEquals($expectedResults, $xmlText);
    }
    
    public function testReadResultsWithMultipleValues()
    {
        $xmlText = trim("
<?xml version='1.0' encoding='UTF-8'?>
<results preview='0'>
<meta>
<fieldOrder>
<field>values(sourcetype)</field>
</fieldOrder>
</meta>
	<result offset='0'>
		<field k='values(sourcetype)'>
			<value><text>scheduler</text></value>
			<value><text>searches</text></value>
			<value><text>splunk_web_access</text></value>
			<value><text>splunk_web_service</text></value>
			<value><text>splunkd</text></value>
			<value><text>splunkd_access</text></value>
		</field>
	</result>
</results>
");
        $expectedResults = array(
            array(
                'values(sourcetype)' => array(
                    'scheduler',
                    'searches',
                    'splunk_web_access',
                    'splunk_web_service',
                    'splunkd',
                    'splunkd_access',
                )
            ),
        );
        
        $this->assertParsedResultsEquals($expectedResults, $xmlText);
    }
    
    private function assertParsedResultsEquals($expectedResults, $xmlText)
    {
        $resultsReader = new Splunk_ResultsReader($xmlText);
        
        $results = array();
        foreach ($resultsReader as $result)
            $results[] = $result;
        
        $this->assertEquals($expectedResults, $results);
    }
}