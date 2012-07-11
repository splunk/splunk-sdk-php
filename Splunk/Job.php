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
 * Represents a job entity in the Splunk REST API.
 * 
 * @package Splunk
 */
class Splunk_Job extends Splunk_Entity
{
    // NOTE: These constants are somewhat arbitrary and could use some tuning
    const DEFAULT_FETCH_MAX_TRIES = 10;
    const DEFAULT_FETCH_DELAY_PER_RETRY = 0.5;  // secs
    
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
            sleep($fetchArgs['delayPerRetry']);
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
     * Returns the results from this job.
     * 
     * The format of the results depends on the 'output_mode' argument
     * (which defaults to "xml"). XML-formatted results can be parsed
     * using Splunk_ResultsReader. For example:
     * 
     *  $job = ...;
     *  while (!$job->isDone())
     *  {
     *      sleep(.5);
     *      $job->reload();
     *  }
     *  $results = new Splunk_ResultsReader($job->getResults());
     *  foreach ($results as $result)
     *  {
     *      // (See documentation for Splunk_ResultsReader to see how to
     *      //  interpret $result.)
     *      ...
     *  }
     * 
     * @param array $args           (optional) Additional arguments.
     *                              For details, see the
     *                              "GET search/jobs/{search_id}/results"
     *                              endpoint in the REST API Documentation.
     * @return string               The results (i.e. transformed events)
     *                              of this job.
     * @throws Splunk_JobNotDoneException
     *                              If the results are not ready yet.
     *                              Check isDone() to ensure the results are
     *                              ready prior to calling this method.
     * @throws Splunk_HttpException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTsearch#search.2Fjobs.2F.7Bsearch_id.7D.2Fresults
     */
    public function getResults($args=array())
    {
        $response = $this->service->get($this->path . '/results', $args);
        if ($response->status == 204)
            throw new Splunk_JobNotDoneException($response);
        return $response->body;
    }
}
