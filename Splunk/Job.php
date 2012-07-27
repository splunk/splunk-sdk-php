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
 * Represents a running or completed search job.
 * 
 * @package Splunk
 */
class Splunk_Job extends Splunk_Entity
{
    // NOTE: These constants are somewhat arbitrary and could use some tuning
    const DEFAULT_FETCH_MAX_TRIES = 10;
    const DEFAULT_FETCH_DELAY_PER_RETRY = 0.0;  // secs
    
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
            if ($response->status != 204)
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
    
    /** @return bool                Whether this job has been loaded. */
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
     * @throws Splunk_HttpException
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
    
    // === Results ===
    
    /**
     * @return float                Percentage of this job's results that were
     *                              computed (0.0-1.0) at the time this job was
     *                              last loaded or reloaded.
     * @see Splunk_Entity::reload()
     */ 
    public function getProgress()
    {
        return floatval($this['doneProgress']);
    }
    
    /**
     * @return boolean              Whether this job's results were available
     *                              at the time this job was last loaded or
     *                              reloaded.
     * @see Splunk_Entity::reload()
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
     *  $job = ...;
     *  foreach ($job->getResults() as $result)
     *  {
     *      // (See documentation for Splunk_ResultsReader to see how to
     *      //  interpret $result.)
     *      ...
     *  }
     * 
     * @param array $args (optional) {
     *     'count' => (optional) The maximum number of results to return,
     *                or -1 to return as many results as possible.
     *                Defaults to returning as many results as possible.
     *     'offset' => (optional) The offset of the first result to return.
     *                 Defaults to 0.
     *     'pagesize' => (optional) The number of results to fetch from the
     *                   server on each request when paginating internally,
     *                   or -1 to return as many results as possible.
     *                   Defaults to returning as many results as possible.
     *     
     *     'field_list' => (optional) Comma-separated list of fields to return
     *                     in the result set. Defaults to all fields.
     *     'output_mode' => (optional) The output format of the result. Valid values:
     *                      - "csv"
     *                      - "raw"
     *                      - "xml": The format parsed by Splunk_ResultsReader.
     *                      - "json"
     *                      Defaults to "xml".
     *     'search' => (optional) The search expression to filter results
     *                 with. For example, "foo" matches any object that has
     *                 "foo" in a substring of a field. Similarly the
     *                 expression "field_name=field_value" matches only objects
     *                 that have a "field_name" field with the value
     *                 "field_value".
     * }
     * @return Iterator             The results (i.e. transformed events)
     *                              of this job, via an iterator.
     * @throws Splunk_JobNotDoneException
     *                              If the results are not ready yet.
     *                              Check isDone() to ensure the results are
     *                              ready prior to calling this method.
     * @throws Splunk_HttpException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTsearch#search.2Fjobs.2F.7Bsearch_id.7D.2Fresults
     */
    public function getResults($args=array())
    {
        return new Splunk_PaginatedResultsReader($this, $args);
    }
    
    /**
     * Returns a single page of results from this job.
     * 
     * Most potential callers should use getResults() instead.
     * Only use this method if you wish to parse job results yourself
     * or want to control pagination manually.
     * 
     * By default, all results are returned. For large
     * result sets, it is advisable to fetch items using multiple calls with
     * the paging options (i.e. 'offset' and 'count').
     * 
     * The format of the results depends on the 'output_mode' argument
     * (which defaults to "xml"). XML-formatted results can be parsed
     * using Splunk_ResultsReader. For example:
     * 
     *  $job = ...;
     *  $results = new Splunk_ResultsReader($job->getResultsPage());
     *  foreach ($results as $result)
     *  {
     *      // (See documentation for Splunk_ResultsReader to see how to
     *      //  interpret $result.)
     *      ...
     *  }
     * 
     * @param array $args (optional) {
     *     'count' => (optional) The maximum number of results to return,
     *                or -1 to return as many results as possible.
     *                Defaults to returning as many results as possible.
     *     'offset' => (optional) The offset of the first result to return.
     *                 Defaults to 0.
     *     
     *     'field_list' => (optional) Comma-separated list of fields to return
     *                     in the result set. Defaults to all fields.
     *     'output_mode' => (optional) The output format of the result. Valid values:
     *                      - "csv"
     *                      - "raw"
     *                      - "xml": The format parsed by Splunk_ResultsReader.
     *                      - "json"
     *                      Defaults to "xml".
     *     'search' => (optional) The search expression to filter results
     *                 with. For example, "foo" matches any object that has
     *                 "foo" in a substring of a field. Similarly the
     *                 expression "field_name=field_value" matches only objects
     *                 that have a "field_name" field with the value
     *                 "field_value".
     * }
     * @return resource             The results (i.e. transformed events)
     *                              of this job, as a stream.
     * @throws Splunk_JobNotDoneException
     *                              If the results are not ready yet.
     *                              Check isDone() to ensure the results are
     *                              ready prior to calling this method.
     * @throws Splunk_HttpException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTsearch#search.2Fjobs.2F.7Bsearch_id.7D.2Fresults
     */
    public function getResultsPage($args=array())
    {
        $args = array_merge(array(
            'count' => -1,
        ), $args);
        
        if ($args['count'] <= 0 && $args['count'] != -1)
            throw new IllegalArgumentException(
                'Count must be positive or -1 (infinity).');
        
        if ($args['count'] == -1)
            $args['count'] = 0;     // infinity value for the REST API
        
        $response = $this->service->get($this->path . '/results', $args);
        if ($response->status == 204)
            throw new Splunk_JobNotDoneException($response);
        return $response->bodyStream;
    }
}
