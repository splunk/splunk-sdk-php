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

class JobsTest extends SplunkTest
{
    const SEARCH_QUERY = 'search index=_internal | head 10 | top sourcetype';
    
    public function testCreateNormal()
    {
        $service = $this->loginToRealService();
        
        $job = $service->getJobs()->create(JobsTest::SEARCH_QUERY, array(
            'exec_mode' => 'normal',
        ));
        $this->touch($job);
    }
    
    public function testCreateBlocking()
    {
        $service = $this->loginToRealService();
        
        $job = $service->getJobs()->create(JobsTest::SEARCH_QUERY, array(
            'exec_mode' => 'blocking',
        ));
        $this->touch($job);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Use createOneshot() instead.
     */
    public function testCreateOneshotImproperly()
    {
        $service = $this->loginToRealService();
        
        $job = $service->getJobs()->create(JobsTest::SEARCH_QUERY, array(
            'exec_mode' => 'oneshot',
        ));
        $this->touch($job);
    }
    
    public function testCreateOneshot()
    {
        $service = $this->loginToRealService();
        
        $resultsText = $service->getJobs()->createOneshot(JobsTest::SEARCH_QUERY, array(
            'exec_mode' => 'oneshot',
        ));
        $results = new Splunk_ResultsReader($resultsText);
        
        $gotResults = FALSE;
        foreach ($results as $result)
        {
            $gotResults = TRUE;
        }
        $this->assertTrue($gotResults);
    }
}
