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
        $httpResponse = new Splunk_HttpResponse(array(
            'status' => 204,
            'reason' => 'No Content',
            'headers' => array(),
            'body' => ''));
        $http->expects($this->atLeastOnce())
             ->method('get')
             ->will($this->returnValue($httpResponse));
        $job = $service->getJobs()->getReference('A_JOB');
        
        // Try to touch job when server refuses to return it
        try
        {
            $this->touch($job);
            $this->assertTrue(FALSE, 'Expected Splunk_JobNotReadyException to be thrown.');
        }
        catch (Splunk_JobNotReadyException $e)
        {
            // Good
        }
    }
    
    public function testGetTimeoutSimulated()
    {
        $bodyForJobInParsingState =
'<?xml version="1.0" encoding="UTF-8"?>
<!--This is to override browser formatting; see server.conf[httpServer] to disable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .-->
<?xml-stylesheet type="text/xml" href="/static/atom.xsl"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:s="http://dev.splunk.com/ns/rest" xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">
  <title>search index=_internal latest=-5m | stats count | appendcols [search index=_internal latest=-5m | stats count]</title>
  <id>https://localhost:8089/services/search/jobs/1404154730.29</id>
  <updated>2014-06-30T11:58:51.000-07:00</updated>
  <link href="/services/search/jobs/1404154730.29" rel="alternate"/>
  <published>2014-06-30T11:58:50.000-07:00</published>
  <link href="/services/search/jobs/1404154730.29/search.log" rel="search.log"/>
  <link href="/services/search/jobs/1404154730.29/events" rel="events"/>
  <link href="/services/search/jobs/1404154730.29/results" rel="results"/>
  <link href="/services/search/jobs/1404154730.29/results_preview" rel="results_preview"/>
  <link href="/services/search/jobs/1404154730.29/timeline" rel="timeline"/>
  <link href="/services/search/jobs/1404154730.29/summary" rel="summary"/>
  <link href="/services/search/jobs/1404154730.29/control" rel="control"/>
  <author>
    <name>admin</name>
  </author>
  <content type="text/xml">
    <s:dict>
      <s:key name="bundleVersion">36800464769513394</s:key>
      <s:key name="cursorTime">2038-01-18T19:14:07.000-08:00</s:key>
      <s:key name="defaultSaveTTL">604800</s:key>
      <s:key name="defaultTTL">600</s:key>
      <s:key name="delegate"></s:key>
      <s:key name="diskUsage">0</s:key>
      <s:key name="dispatchState">PARSING</s:key>
      <s:key name="doneProgress">0</s:key>
      <s:key name="dropCount">0</s:key>
      <s:key name="earliestTime">1969-12-31T16:00:00.000-08:00</s:key>
      <s:key name="eventAvailableCount">0</s:key>
      <s:key name="eventCount">0</s:key>
      <s:key name="eventFieldCount">0</s:key>
      <s:key name="eventIsStreaming">1</s:key>
      <s:key name="eventIsTruncated">1</s:key>
      <s:key name="eventSearch"></s:key>
      <s:key name="eventSorting">desc</s:key>
      <s:key name="isBatchModeSearch">0</s:key>
      <s:key name="isDone">0</s:key>
      <s:key name="isFailed">0</s:key>
      <s:key name="isFinalized">0</s:key>
      <s:key name="isPaused">0</s:key>
      <s:key name="isPreviewEnabled">0</s:key>
      <s:key name="isRealTimeSearch">0</s:key>
      <s:key name="isRemoteTimeline">1</s:key>
      <s:key name="isSaved">0</s:key>
      <s:key name="isSavedSearch">0</s:key>
      <s:key name="isZombie">0</s:key>
      <s:key name="keywords"></s:key>
      <s:key name="label"></s:key>
      <s:key name="numPreviews">0</s:key>
      <s:key name="pid">2359</s:key>
      <s:key name="priority">5</s:key>
      <s:key name="remoteSearch"></s:key>
      <s:key name="reportSearch"></s:key>
      <s:key name="resultCount">0</s:key>
      <s:key name="resultIsStreaming">1</s:key>
      <s:key name="resultPreviewCount">0</s:key>
      <s:key name="runDuration">0.001000</s:key>
      <s:key name="scanCount">0</s:key>
      <s:key name="sid">1404154730.29</s:key>
      <s:key name="statusBuckets">0</s:key>
      <s:key name="ttl">600</s:key>
      <s:key name="performance">
        <s:dict>
          <s:key name="dispatch.writeStatus">
            <s:dict>
              <s:key name="duration_secs">0.001000</s:key>
              <s:key name="invocations">1</s:key>
            </s:dict>
          </s:key>
          <s:key name="startup.handoff">
            <s:dict>
              <s:key name="duration_secs">0.045000</s:key>
              <s:key name="invocations">1</s:key>
            </s:dict>
          </s:key>
        </s:dict>
      </s:key>
      <s:key name="messages">
        <s:dict/>
      </s:key>
      <s:key name="request">
        <s:dict>
          <s:key name="search">search index=_internal latest=-5m | stats count | appendcols [search index=_internal latest=-5m | stats count]</s:key>
        </s:dict>
      </s:key>
      <s:key name="runtime">
        <s:dict>
          <s:key name="auto_cancel">0</s:key>
          <s:key name="auto_pause">0</s:key>
        </s:dict>
      </s:key>
      <s:key name="eai:acl">
        <s:dict>
          <s:key name="perms">
            <s:dict>
              <s:key name="read">
                <s:list>
                  <s:item>admin</s:item>
                </s:list>
              </s:key>
              <s:key name="write">
                <s:list>
                  <s:item>admin</s:item>
                </s:list>
              </s:key>
            </s:dict>
          </s:key>
          <s:key name="owner">admin</s:key>
          <s:key name="modifiable">1</s:key>
          <s:key name="sharing">global</s:key>
          <s:key name="app">search</s:key>
          <s:key name="can_write">1</s:key>
          <s:key name="ttl">600</s:key>
        </s:dict>
      </s:key>
      <s:key name="searchProviders">
        <s:list/>
      </s:key>
    </s:dict>
  </content>
</entry>';
        
        list($service, $http) = $this->loginToMockService();
        
        // Get job
        $httpResponse = (object) array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => $bodyForJobInParsingState);
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
        $additionalGetCalls = 1; //the new isRead Method makes an http call now
        $this->assertTrue(
            $maxTries != Splunk_Job::DEFAULT_FETCH_MAX_TRIES,
            'This test is only valid for a non-default number of fetch attempts.');
        
        list($service, $http) = $this->loginToMockService();
        
        $httpResponse = new Splunk_HttpResponse(array(
            'status' => 204,
            'reason' => 'No Content',
            'headers' => array(),
            'body' => ''));
        $http->expects($this->exactly($maxTries+$additionalGetCalls))
             ->method('get')
             ->will($this->returnValue($httpResponse));
        $job = $service->getJobs()->getReference('A_JOB');
        
        $this->assertFalse($job->isReady()); //calls http->get() an additional time
        try
        {
            $job->makeReady(/*maxTries=*/$maxTries, /*delayPerRetry=*/0.1);
            $this->assertTrue(FALSE, 'Expected Splunk_HttpException to be thrown.');
        }
        catch (Splunk_HttpException $e)
        {
            // Good
        }
    }
    
    public function testMakeReadyReturnsSelf()
    {
        list($service, $http) = $this->loginToMockService();
        
        $httpResponse = new Splunk_HttpResponse(array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => '
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:s="http://dev.splunk.com/ns/rest" xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">
  <content type="text/xml">
  </content>
</entry>
'));
        $http->expects($this->exactly(2)) // make ready now needs two calls
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
        
        $this->makeDone($job);
        
        $resultsStream = $job->getResultsPage();
        $results = new Splunk_ResultsReader($resultsStream);
        
        // NOTE: Disabled because this is a brittle test.
        //       There might not be events with the "splunkd" or
        //       "splunkd_access" sourcetype immediately after Splunk
        //       is installed.
        /*
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
        */
        
        $hasFieldOrder = FALSE;
        $hasAnyRows = FALSE;
        foreach ($results as $result)
        {
            if ($result instanceof Splunk_ResultsFieldOrder)
                $hasFieldOrder = TRUE;
            else if (is_array($result))
                $hasAnyRows = TRUE;
        }
        $this->assertTrue($hasFieldOrder,
            'Field order was not reported in the job results.');
        $this->assertTrue($hasAnyRows,
            'No rows were reported in the job results.');
    }
    
    public function testResultsNotDone()
    {
        $service = $this->loginToRealService();
        
        $job = $service->getJobs()->create('search index=_internal');
        
        $this->assertFalse($job->isDone(),
            'Job completed too fast. Please rewrite this unit test to avoid timing issues.');
        
        try
        {
            $job->getResultsPage();
            $this->fail('Expected Splunk_JobNotReadyException or Splunk_JobNotDoneException.');
        }
        catch (Splunk_JobNotReadyException $e)
        {
            // Good
        }
        catch (Splunk_JobNotDoneException $e)
        {
            // Good
        }
        
        $job->delete();
    }
    
    public function testResultsEmpty()
    {
        $SEARCHES = array(
            'search index=_internal x NOT x',
            'search index=_does_not_exist',
        );
        
        $service = $this->loginToRealService();
        
        foreach ($SEARCHES as $search)
        {
            $job = $service->getJobs()->create('search index=_internal x NOT x', array(
                'exec_mode' => 'blocking'
            ));
            
            $results = new Splunk_ResultsReader($job->getResultsPage());
            $hasResults = FALSE;
            foreach ($results as $result)
            {
                $hasResults = TRUE;
            }
            $this->assertFalse($hasResults);
        }
    }
    
    public function testResultsPageInNamespace()
    {
        $service = $this->loginToRealService();
        
        // Setup
        $job = $service->getJobs()->create('search index=_internal | head 1', array(
            'namespace' => Splunk_Namespace::createUser('admin', 'launcher'),
        ));
        
        // Test
        // (Make sure this doesn't report an HTTP 404)
        $job->makeReady();
        $this->makeDone($job);
        $job->getResultsPage();
        
        // Teardown
        $job->delete();
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
        
        //wait for the search to become ready
        $this->makeReady($rtjob);
        
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
            $rtjob->refresh();
        }
        
        // ...but not all
        $this->assertFalse($rtjob->isDone(),
            'Realtime job reported self as completed. ' .
            'Realtime jobs should never complete.');
        
        $page = $rtjob->getResultsPreviewPage();
        $this->assertTrue($this->pageHasResults($page),
            'Job claimed to have preview results, yet none were obtained.');
        
        /* Teardown */
        
        $rtjob->delete();
    }
    
    public function testControlActions()
    {
        /* Setup */
        
        $service = $this->loginToRealService();
        
        $rtjob = $service->getJobs()->create('search index=_internal', array(
            'earliest_time' => 'rt',
            'latest_time' => 'rt',
        ));
        
        //wait for the search to become ready
        $this->makeReady($rtjob);
        
        $this->assertTrue($rtjob['isRealTimeSearch'] === '1',
            'This should be a realtime job.');
        
        /* Tests & Teardown */
        
        $rtjob->pause();
        $rtjob->refresh();
        $this->assertEquals(1, $rtjob['isPaused']);
        
        $rtjob->unpause();
        $rtjob->refresh();
        $this->assertEquals(0, $rtjob['isPaused']);
        
        $rtjob->finalize();
        $rtjob->refresh();
        $this->assertEquals(1, $rtjob['isFinalized']);
        
        $rtjob->cancel();
        try
        {
            $rtjob->refresh();
            $this->fail('Expected a cancelled job to be deleted.');
        }
        catch (Splunk_HttpException $e)
        {
            $this->assertEquals(404, $e->getResponse()->status);
        }
    }
    
    /**
     * Ensures that a job can be looked up by its reported name.
     * That is: $service->getJobs()->get($job->getName(), ...) == $job
     * 
     * NOTE: As currently written, this test actually invokes a lot of
     *       special-cased behavior beyond the core of what it is supposed to
     *       test. Therefore if multiple unit tests are failing, look at the
     *       others first.
     */
    public function testGetName()
    {
        $service = $this->loginToRealService();
        
        // (This search is installed by default on Splunk 4.x.)
        $ss = $service->getSavedSearches()->get('Top five sourcetypes');
        $job = $ss->dispatch();
        
        //wait for the search to become ready
        $this->makeReady($job);

        // Ensure that we have a fully loaded Job
        $this->touch($job);
        
        // Sanity check: Make sure refresh is possible.
        // If refresh breaks here then GET probably won't work.
        $job->refresh();
        
        $job2 = $service->getJobs()->get($job->getName(), $job->getNamespace());
        $this->assertEquals($job->getName(), $job2->getName(),
            'Fetching a job by its own name returned a different job.');
    }
    
    public function testCreateInCustomNamespace()
    {
        $namespace = Splunk_Namespace::createUser('USER', 'APP');
        
        $postResponse = new Splunk_HttpResponse(array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => trim("
<?xml version='1.0' encoding='UTF-8'?>
<response><sid>1345584253.35</sid></response>
")));
        $postArgs = array(
            // (The URL should correspond to the namespace)
            'https://localhost:8089/servicesNS/USER/APP/search/jobs/',
            array(
                'search' => 'A_SEARCH',
            ),
            array(
                'Authorization' => 'Splunk ' . SplunkTest::MOCK_SESSION_TOKEN,
            ),
        );
        
        list($service, $http) = $this->loginToMockService(
            $postResponse,
            $postArgs);
        
        $job = $service->getJobs()->create('A_SEARCH', array(
            'namespace' => $namespace,
        ));
        // (The created object should be in the correct namespace)
        $this->assertEquals($namespace, $job->getNamespace());
    }
    
    public function testCreateInServiceNamespace()
    {
        $namespace = Splunk_Namespace::createUser('USER', 'APP');
        
        $postResponse = new Splunk_HttpResponse(array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => trim("
<?xml version='1.0' encoding='UTF-8'?>
<response><sid>1345584253.35</sid></response>
")));
        $postArgs = array(
            // (The URL should correspond to the namespace)
            'https://localhost:8089/servicesNS/USER/APP/search/jobs/',
            array(
                'search' => 'A_SEARCH',
            ),
            array(
                'Authorization' => 'Splunk ' . SplunkTest::MOCK_SESSION_TOKEN,
            ),
        );
        $extraConnectArgs = array(
            'namespace' => $namespace,
        );
        
        list($service, $http) = $this->loginToMockService(
            $postResponse,
            $postArgs,
            $extraConnectArgs);
        
        $job = $service->getJobs()->create('A_SEARCH');
        // (The created object should be in the correct namespace)
        $this->assertEquals($namespace, $job->getNamespace());
    }
    
    public function testControlInCustomNamespace()
    {
        $service = $this->loginToRealService();
        
        // Setup
        $job = $service->getJobs()->create('search index=_internal | head 1', array(
            'namespace' => Splunk_Namespace::createUser('admin', 'launcher'),
        ));
        
        // Test & Teardown
        // (Ensure this doesn't throw HTTP 404)
        $job->cancel();
    }
    
    public function testSearchOnService()
    {
        $service = $this->loginToRealService();
        
        $job = $service->search('search index=_internal | head 1', array(
            'exec_mode' => 'blocking',
        ));
        $this->makeDone($job);
        
        // Ensure we got some results
        $results = $job->getResults();
        $numResults = 0;
        foreach ($results as $result)
        {
            if (is_array($result))
            {
                $numResults++;
            }
        }
        $this->assertEquals(1, $numResults);
    }
    
    public function testOneshotSearchOnService()
    {
        $service = $this->loginToRealService();
        
        $resultsStream = $service->oneshotSearch('search index=_internal | head 1');
        
        // Ensure we got some results
        $results = new Splunk_ResultsReader($resultsStream);
        $numResults = 0;
        foreach ($results as $result)
        {
            if (is_array($result))
            {
                $numResults++;
            }
        }
        $this->assertEquals(1, $numResults);
    }
    
    public function testResultsDocstringSample()
    {
        $service = $this->loginToRealService();
        
        $job = $service->getJobs()->create('search index=_internal | head 1');
        while (!$job->refresh()->isDone()) { usleep(0.5 * 1000000); }
        
        foreach ($job->getResults() as $result)
        {
            // (See documentation for Splunk_ResultsReader to see how to
            //  interpret $result.)
            //...
        }
    }
    
    public function testResultsPageDocstringSample()
    {
        $service = $this->loginToRealService();
        
        $job = $service->getJobs()->create('search index=_internal | head 1');
        while (!$job->refresh()->isDone()) { usleep(0.5 * 1000000); }
        
        $results = new Splunk_ResultsReader($job->getResultsPage());
        foreach ($results as $result)
        {
            // (See documentation for Splunk_ResultsReader to see how to
            //  interpret $result.)
            //...
        }
    }

    public function testWaitingForParsing()
    {
        $service = $this->loginToRealService();

        //This query has only one job: to stay in the parsing state for several seconds
        $job = $service->getJobs()->create('search index=_internal | join host [search index=_internal] | join host [search index=_internal]');

        //this should not raise an exeption
        while(!$job->isDone())
        {
            usleep(0.5 * 1000000);
            $job->refresh();
        }

    }
    
    // === Utility ===
    
    private function pageHasResults($resultsPage)
    {
        $pageHasResults = FALSE;
        foreach (new Splunk_ResultsReader($resultsPage) as $result)
            $pageHasResults = TRUE;
        return $pageHasResults;
    }
    
    private function makeDone(Splunk_Job $job)
    {
        while (!$job->isDone())
        {
            //printf("%03.1f%%\r\n", $job->getProgress() * 100);
            usleep(0.1 * 1000000);
            $job->refresh();
        }
    }
}
