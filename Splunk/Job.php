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
}
