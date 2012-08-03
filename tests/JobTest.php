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

class JobTest extends SplunkTest
{
    public function testGetTimeout()
    {
        list($service, $http) = $this->loginToMockService();
        
        // Get job
        $httpResponse = (object) array(
            'status' => 204,
            'reason' => 'No Content',
            'headers' => array(),
            'body' => '');
        $http->expects($this->atLeastOnce())
             ->method('get')
             ->will($this->returnValue($httpResponse));
        $job = $service->getJobs()->getReference('A_JOB');
        
        // Try to touch job when server refuses to return it
        try
        {
            $this->touch($job);
            $this->assertTrue(FALSE, 'Expected Splunk_HttpException to be thrown.');
        }
        catch (Splunk_HttpException $e)
        {
            $this->assertEquals(204, $e->getResponse()->status);
        }
    }
    
    public function testMakeReady()
    {
        $maxTries = 7;
        $this->assertTrue(
            $maxTries != Splunk_Job::DEFAULT_FETCH_MAX_TRIES,
            'This test is only valid for a non-default number of fetch attempts.');
        
        list($service, $http) = $this->loginToMockService();
        
        $httpResponse = (object) array(
            'status' => 204,
            'reason' => 'No Content',
            'headers' => array(),
            'body' => '');
        $http->expects($this->exactly($maxTries))
             ->method('get')
             ->will($this->returnValue($httpResponse));
        $job = $service->getJobs()->getReference('A_JOB');
        
        $this->assertFalse($job->isReady());
        try
        {
            $job->makeReady(/*maxTries=*/$maxTries, /*delayPerRetry=*/0.1);
            $this->assertTrue(FALSE, 'Expected Splunk_HttpException to be thrown.');
        }
        catch (Splunk_HttpException $e)
        {
            $this->assertEquals(204, $e->getResponse()->status);
        }
    }
    
    public function testMakeReadyReturnsSelf()
    {
        list($service, $http) = $this->loginToMockService();
        
        $httpResponse = (object) array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => '
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:s="http://dev.splunk.com/ns/rest" xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">
  <content type="text/xml">
  </content>
</entry>
');
        $http->expects($this->once())
             ->method('get')
             ->will($this->returnValue($httpResponse));
        $job = $service->getJobs()->getReference('A_JOB');
        
        $this->assertEquals($job, $job->makeReady());
    }
    
    public function testValidResultsForNormalJob()
    {
        $service = $this->loginToRealService();
        
        // (This search is installed by default on Splunk 4.x.)
        $ss = $service->getSavedSearches()->get('Top five sourcetypes');
        $job = $ss->dispatch();
        
        while (!$job->isDone())
        {
            //printf("%03.1f%%\r\n", $job->getProgress() * 100);
            usleep(0.1 * 1000000);
            $job->reload();
        }
        
        $resultsStream = $job->getResultsPage();
        $results = new Splunk_ResultsReader($resultsStream);
        
        $minExpectedSeriesNames = array('splunkd', 'splunkd_access');
        $actualSeriesNames = array();
        foreach ($results as $result)
            if (is_array($result))
                $actualSeriesNames[] = $result['series'];
        
        $remainingSeriesNames = 
            array_diff($minExpectedSeriesNames, $actualSeriesNames);
        $this->assertEmpty(
            $remainingSeriesNames,
            'Results are missing some expected series names: ' . 
                implode(',', $remainingSeriesNames));
    }
    
    /**
     * @expectedException Splunk_JobNotDoneException
     */
    public function testResultsNotDone()
    {
        $service = $this->loginToRealService();
        
        // (This search is installed by default on Splunk 4.x.)
        $ss = $service->getSavedSearches()->get('Top five sourcetypes');
        $job = $ss->dispatch();
        
        $this->assertFalse($job->isDone(),
            'Job completed too fast. Please rewrite this unit test to avoid timing issues.');
        
        $job->getResultsPage();
    }
    
    /**
     * @group slow
     */
    public function testPreview()
    {
        /* Setup */
        
        $service = $this->loginToRealService();
        
        $rtjob = $service->getJobs()->create('search index=_internal', array(
            'earliest_time' => 'rt',
            'latest_time' => 'rt',
        ));
        
        $this->assertTrue($rtjob['isRealTimeSearch'] === '1',
            'This should be a realtime job.');
        
        $this->assertTrue($rtjob['isPreviewEnabled'] === '1',
            'Preview should be automatically enabled for all realtime jobs. ' +
            'Otherwise there would be no way to get results from them.');
        
        /*
         * Subtest #1
         * 
         * Previews that don't have any results yet should report an empty
         * page of results (and not throw any exception).
         */
        
        $this->assertEquals(0, $rtjob['resultPreviewCount'],
            'Job yielded preview results too fast. ' .
            'Please rewrite this unit test to avoid timing issues.');
        
        // NOTE: Should NOT throw a Splunk_HttpException (HTTP 204)
        $page = $rtjob->getResultsPreviewPage();
        $this->assertFalse($this->pageHasResults($page),
            'Job claimed to have no preview results, yet results were obtained. ' .
            'This might indicate a timing issue in this unit test.');
        
        /*
         * Subtest #2
         * 
         * It should be possible to obtain preview results from a job
         * without that job being done generating results.
         */
        
        // Wait until some results...
        // (NOTE: This takes about 5 seconds on Splunk 4.3.2. A lot of time.)
        while ($rtjob['resultPreviewCount'] == 0)
        {
            usleep(0.2 * 1000000);
            $rtjob->reload();
        }
        
        // ...but not all
        $this->assertFalse($rtjob->isDone(),
            'Realtime job reported self as completed. ' .
            'Realtime jobs should never complete.');
        
        $page = $rtjob->getResultsPreviewPage();
        $this->assertTrue($this->pageHasResults($page),
            'Job claimed to have preview results, yet none were obtained.');
        
        /* Teardown */
        
        $rtjob->cancel();
    }
    
    // === Utility ===
    
    private function pageHasResults($resultsPage)
    {
        $pageHasResults = FALSE;
        foreach (new Splunk_ResultsReader($resultsPage) as $result)
            $pageHasResults = TRUE;
        return $pageHasResults;
    }
}
