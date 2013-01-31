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
 * Represents a saved search.
 * 
 * @package Splunk
 */
class Splunk_SavedSearch extends Splunk_Entity
{
    // === Operations ===
    
    /**
     * Runs this saved search and returns the resulting search job.
     * 
     * @param array $args   (optional) Additional arguments.
     *                      For details, see the
     *                      <a href="http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTsearch#saved.2Fsearches.2F.7Bname.7D.2Fdispatch">
     *                      "POST saved/searches/{name}/dispatch"</a>
     *                      endpoint in the REST API Documentation.
     * @return Splunk_Job
     *                      The created search job.
     */
    public function dispatch($args=array())
    {
        $response = $this->sendPost('/dispatch', $args);
        $xml = new SimpleXMLElement($response->body);
        $sid = Splunk_XmlUtil::getTextContentAtXpath($xml, '/response/sid');
        
        return $this->service->getJobs()->getReference(
            $sid, $this->getNamespace());
    }
}