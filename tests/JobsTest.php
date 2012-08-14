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
        
        return array($service, $job);
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
    
    /** @depends testCreateNormal */
    public function testGet($service_job)
    {
        list($service, $job) = $service_job;
        
        // Ensure this doesn't blow up
        $service->getJobs()->get($job->getName());
    }
    
    /** @depends testCreateNormal */
    public function testGetReference($service_job)
    {
        list($service, $job) = $service_job;
        
        // Ensure this doesn't blow up
        $jobRef = $service->getJobs()->getReference($job->getName());
        $this->touch($jobRef);
    }
    
    /** @depends testCreateNormal */
    public function testItems($service_job)
    {
        list($service, $job) = $service_job;
        
        $allJobs = $service->getJobs()->items();
        
        $foundJob = FALSE;
        foreach ($allJobs as $curJob)
            if ($curJob->getName() === $job->getName())
                $foundJob = TRUE;
        $this->assertTrue($foundJob,
            'Could not find a recently created job in the list of all jobs.');
    }
    
    /** @depends testCreateNormal */
    public function testDelete($service_job)
    {
        list($service, $job) = $service_job;
        
        // Ensure this doesn't blow up
        $service->getJobs()->delete($job->getName());
    }
}
