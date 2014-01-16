<?php
/**
 * Copyright 2013 Splunk, Inc.
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
 * Represents a running or completed search job.
 * 
 * @package Splunk
 */
class Splunk_Job extends Splunk_Entity
{
    // NOTE: These constants are somewhat arbitrary and could use some tuning
    const DEFAULT_FETCH_MAX_TRIES = 10;
    const DEFAULT_FETCH_DELAY_PER_RETRY = 0.1;  // secs
    
    // === Load ===
    
    /*
     * Job requests sometimes yield an HTTP 204 code when they are in the
     * process of being created. To hide this from the caller, transparently
     * retry requests when an HTTP 204 is received.
     */
    protected function fetch($fetchArgs)
    {
        $fetchArgs = array_merge(array(
            'maxTries' => Splunk_Job::DEFAULT_FETCH_MAX_TRIES,
            'delayPerRetry' => Splunk_Job::DEFAULT_FETCH_DELAY_PER_RETRY,
        ), $fetchArgs);
        
        for ($numTries = 0; $numTries < $fetchArgs['maxTries']; $numTries++)
        {
            $response = parent::fetch($fetchArgs);
            if ($this->isFullResponse($response))
                return $response;
            usleep($fetchArgs['delayPerRetry'] * 1000000);
        }
        
        // Give up
        throw new Splunk_HttpException($response);
    }
    
    protected function extractEntryFromRootXmlElement($xml)
    {
        // <entry> element is at the root of a job's Atom feed
        return $xml;
    }
    
    // === Ready ===
    
    /** 
     * Returns a value that indicates whether this job has been loaded.
     *
     * @return bool                Whether this job has been loaded. 
     */
    public function isReady()
    {
        return $this->isLoaded();
    }
    
    /**
     * Loads this job, retrying the specified number of times as necessary.
     * 
     * @param int $maxTries         The maximum number of times to try loading
     *                              this job.
     * @param float $delayPerRetry  The number of seconds to wait between
     *                              attempts to retry loading this job.
     * @return Splunk_Entity        This entity.
     * @throws Splunk_IOException
     */
    public function makeReady(
        $maxTries=Splunk_Job::DEFAULT_FETCH_MAX_TRIES,
        $delayPerRetry=Splunk_Job::DEFAULT_FETCH_DELAY_PER_RETRY)
    {
        return $this->validate(/*fetchArgs=*/array(
            'maxTries' => $maxTries,
            'delayPerRetry' => $delayPerRetry,
        ));
    }
    
    // === Accessors ===
    
    // Overrides superclass to return the correct ID of this job,
    // which can be used to lookup this job from the Jobs collection.
    /**
     * @see Splunk_Entity::getName()
     */
    public function getName()
    {
        return $this['sid'];
    }
    
    /**
     * Returns the search string executed by this job.
     *
     * @return string               The search string executed by this job.
     */
    public function getSearch()
    {
        return $this->getTitle();
    }
    
    // === Results ===
    
    /**
     * Returns a value that indicates the percentage of this job's results 
     *      that were computed at the time this job was last loaded or 
     *      refreshed.
     *
     * @return float                Percentage of this job's results that were
     *                              computed (0.0-1.0) at the time this job was
     *                              last loaded or refreshed.
     * @see Splunk_Entity::refresh()
     */ 
    public function getProgress()
    {
        return floatval($this['doneProgress']);
    }
    
    /**
     * Returns a value that indicates whether this job's results were available 
     *      at the time this job was last loaded or refreshed.
     *
     * @return boolean              Whether this job's results were available
     *                              at the time this job was last loaded or
     *                              refreshed.
     * @see Splunk_Entity::refresh()
     */
    public function isDone()
    {
        return ($this['isDone'] === '1');
    }
    
    /**
     * Returns an iterator over the results from this job.
     * 
     * Large result sets will be paginated automatically.
     * 
     * Example:
     * 
     * <pre>
     *  $job = ...;
     *  while (!$job->refresh()->isDone()) { usleep(0.5 * 1000000); }
     *  
     *  foreach ($job->getResults() as $result)
     *  {
     *      // (See documentation for Splunk_ResultsReader to see how to
     *      //  interpret $result.)
     *      ...
     *  }
     * </pre>
     * 
     * This method cannot be used to access results from realtime jobs,
     * which are never done. Use {@link getResultsPreviewPage()} instead.
     * 
     * @param array $args (optional) {<br/>
     *     **count**: (optional) The maximum number of results to return,
     *                or -1 to return as many as possible.
     *                Defaults to returning as many as possible.<br/>
     *     **offset**: (optional) The offset of the first result to return.
     *                 Defaults to 0.<br/>
     *     **pagesize**: (optional) The number of results to fetch from the
     *                   server on each request when paginating internally,
     *                   or -1 to return as many results as possible.
     *                   Defaults to returning as many results as possible.<br/>
     *     
     *     **field_list**: (optional) Comma-separated list of fields to return
     *                     in the result set. Defaults to all fields.<br/>
     *     **output_mode**: (optional) The output format of the result. Valid 
     *                      values:<br/>
     *                      - "csv"<br/>
     *                      - "raw"<br/>
     *                      - "xml": The format parsed by Splunk_ResultsReader.
     *                      <br/>
     *                      - "json"<br/>
     *                      Defaults to "xml".<br/>
     *                      You should not change this unless you are parsing
     *                      results yourself.<br/>
     *     **search**: (optional) The post processing search to apply to
     *                 results. Can be any valid search language string.
     *                 For example "search sourcetype=splunkd" will match any
     *                 result whose "sourcetype" field is "splunkd".<br/>
     * }
     * @return Iterator             The results (i.e. transformed events)
     *                              of this job, via an iterator.
     * @throws Splunk_JobNotDoneException
     *                              If the results are not ready yet.
     *                              Check isDone() to ensure the results are
     *                              ready prior to calling this method.
     * @throws Splunk_IOException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTsearch#search.2Fjobs.2F.7Bsearch_id.7D.2Fresults
     */
    public function getResults($args=array())
    {
        return new Splunk_PaginatedResultsReader($this, $args);
    }
    
    /**
     * Returns a single page of results from this job.
     * 
     * Most potential callers should use {@link getResults()} instead.
     * Only use this method if you wish to parse job results yourself
     * or want to control pagination manually.
     * 
     * By default, all results are returned. For large
     * result sets, it is advisable to fetch items using multiple calls with
     * the paging options (i.e. 'offset' and 'count').
     * 
     * The format of the results depends on the 'output_mode' argument
     * (which defaults to "xml"). XML-formatted results can be parsed
     * using {@link Splunk_ResultsReader}. For example:
     * 
     * <pre>
     *  $job = ...;
     *  while (!$job->refresh()->isDone()) { usleep(0.5 * 1000000); }
     *  
     *  $results = new Splunk_ResultsReader($job->getResultsPage());
     *  foreach ($results as $result)
     *  {
     *      // (See documentation for Splunk_ResultsReader to see how to
     *      //  interpret $result.)
     *      ...
     *  }
     * </pre>
     * 
     * This method cannot be used to access results from realtime jobs,
     * which are never done. Use {@link getResultsPreviewPage()} instead.
     * 
     * @param array $args (optional) {<br/>
     *     **count**: (optional) The maximum number of results to return,
     *                or -1 to return as many as possible.
     *                Defaults to returning as many as possible.<br/>
     *     **offset**: (optional) The offset of the first result to return.
     *                 Defaults to 0.<br/>
     *     
     *     **field_list**: (optional) Comma-separated list of fields to return
     *                     in the result set. Defaults to all fields.<br/>
     *     **output_mode**: (optional) The output format of the result. Valid 
     *                      values:<br/>
     *                      - "csv"<br/>
     *                      - "raw"<br/>
     *                      - "xml": The format parsed by Splunk_ResultsReader.
     *                      <br/>
     *                      - "json"<br/>
     *                      Defaults to "xml".<br/>
     *                      You should not change this unless you are parsing
     *                      results yourself.<br/>
     *     **search**: (optional) The post processing search to apply to
     *                 results. Can be any valid search language string.
     *                 For example "search sourcetype=splunkd" will match any
     *                 result whose "sourcetype" field is "splunkd".<br/>
     * }
     * @return resource             The results (i.e. transformed events)
     *                              of this job, as a stream.
     * @throws Splunk_JobNotDoneException
     *                              If the results are not ready yet.
     *                              Check isDone() to ensure the results are
     *                              ready prior to calling this method.
     * @throws Splunk_IOException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTsearch#search.2Fjobs.2F.7Bsearch_id.7D.2Fresults
     */
    public function getResultsPage($args=array())
    {
        $response = $this->fetchPage('results', $args);
        if ($response->status == 204)
            throw new Splunk_JobNotDoneException($response);
        return $response->bodyStream;
    }
    
    /**
     * Returns a single page of results from this job,
     * which may or may not be done running.
     * 
     * @param array $args (optional) {<br/>
     *     **count**: (optional) The maximum number of results to return,
     *                or -1 to return as many as possible.
     *                Defaults to returning as many as possible.<br/>
     *     **offset**: (optional) The offset of the first result to return.
     *                 Defaults to 0.<br/>
     *     
     *     **field_list**: (optional) Comma-separated list of fields to return
     *                     in the result set. Defaults to all fields.<br/>
     *     **output_mode**: (optional) The output format of the result. Valid 
     *                      values:<br/>
     *                      - "csv"<br/>
     *                      - "raw"<br/>
     *                      - "xml": The format parsed by Splunk_ResultsReader.
     *                      <br/>
     *                      - "json"<br/>
     *                      Defaults to "xml".<br/>
     *                      You should not change this unless you are parsing
     *                      results yourself.<br/>
     *     **search**: (optional) The post processing search to apply to
     *                 results. Can be any valid search language string.
     *                 For example "search sourcetype=splunkd" will match any
     *                 result whose "sourcetype" field is "splunkd".<br/>
     * }
     * @return resource             The results (i.e. transformed events)
     *                              of this job, as a stream.
     * @throws Splunk_IOException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTsearch#search.2Fjobs.2F.7Bsearch_id.7D.2Fresults_preview
     */
    public function getResultsPreviewPage($args=array())
    {
        $response = $this->fetchPage('results_preview', $args);
        if ($response->status == 204)
        {
            // The REST API throws a 204 when a preview is being generated
            // and no results are available. This isn't a friendly behavior
            // for clients.
            return Splunk_StringStream::create('');
        }
        return $response->bodyStream;
    }
    
    /** Fetches a page of the specified type. */
    private function fetchPage($pageType, $args)
    {
        $args = array_merge(array(
            'count' => -1,
        ), $args);
        
        if ($args['count'] <= 0 && $args['count'] != -1)
            throw new InvalidArgumentException(
                'Count must be positive or -1 (infinity).');
        
        if ($args['count'] == -1)
            $args['count'] = 0;     // infinity value for the REST API
        
        $response = $this->sendGet("/{$pageType}", $args);
        return $response;
    }

    /** Determines whether a response contains full or partial results */
    private function isFullResponse($response)
    {
        if ($response->status == 204)
            $result = FALSE;
        else
        {        
            $responseBody = new SimpleXMLElement($response->body);
            $dispatchState = implode($responseBody->content->xpath('s:dict/s:key[@name="dispatchState"]/text()'));
            $result = !($dispatchState === 'QUEUED' || $dispatchState === 'PARSING');
        }
        return $result;
    }
    
    // === Control ===
    
    /**
     * Pauses this search job.
     * 
     * @throws Splunk_IOException
     */
    public function pause()
    {
        $this->sendControlAction('pause');
    }
    
    /**
     * Unpauses this search job.
     * 
     * @throws Splunk_IOException
     */
    public function unpause()
    {
        $this->sendControlAction('unpause');
    }
    
    /**
     * Stops this search job but keeps the partial results.
     * 
     * @throws Splunk_IOException
     */
    public function finalize()
    {
        $this->sendControlAction('finalize');
    }
    
    /**
     * Stops this search job and deletes the results.
     * 
     * @throws Splunk_IOException
     */
    public function cancel()
    {
        $this->sendControlAction('cancel');
    }
    
    /**
     * Posts the specified control action.
     *
     * @throws Splunk_IOException
     */
    private function sendControlAction($actionName)
    {
        $response = $this->sendPost('/control', array(
            'action' => $actionName,
        ));
    }
}
