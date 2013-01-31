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
 * An index contains events that have been logged to Splunk.
 * 
 * @package Splunk
 */
class Splunk_Index extends Splunk_Entity
{
    /**
     * Logs one or more events to this index.
     * 
     * It is highly recommended to specify a sourcetype explicitly.
     * 
     * It is slightly faster to use {@link Splunk_Receiver::submit()}
     * to accomplish the same task. One fewer network request is needed.
     * 
     * @param string $data  Raw event text.
     *                      This may contain data for multiple events.
     *                      Under the default configuration, line breaks
     *                      ("\n") can be inserted to separate multiple events.
     * @param array $args   (optional) {<br/>
     *      **host**: (optional) The value to populate in the host field
     *                for events from this data input.<br/>
     *      **host_regex**: (optional) A regular expression used to
     *                      extract the host value from each event.<br/>
     *      **source**: (optional) The source value to fill in the
     *                  metadata for this input's events.<br/>
     *      **sourcetype**: (optional) The sourcetype to apply to
     *                      events from this input.<br/>
     * }
     * @throws Splunk_IOException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTinput#receivers.2Fsimple
     */
    public function submit($data, $args=array())
    {
        $this->service->getReceiver()->submit($data, array_merge($args, array(
            'index' => $this->getName(),
        )));
    }
    
    /**
     * Creates a stream for logging events to the specified index.
     * 
     * It is highly recommended to specify a sourcetype explicitly.
     * 
     * It is slightly faster to use {@link Splunk_Receiver::attach()}
     * to accomplish the same task. One fewer network request is needed.
     * 
     * The returned stream should eventually be closed via fclose().
     * 
     * @param array $args   (optional) {<br/>
     *      **host**: (optional) The value to populate in the host field
     *                for events from this data input.<br/>
     *      **host_regex**: (optional) A regular expression used to
     *                      extract the host value from each event.<br/>
     *      **source**: (optional) The source value to fill in the
     *                  metadata for this input's events.<br/>
     *      **sourcetype**: (optional) The sourcetype to apply to
     *                      events from this input.<br/>
     * }
     * @return resource     A stream that you can write event text to.
     * @throws Splunk_IOException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTinput#receivers.2Fstream
     */
    public function attach($args=array())
    {
        return $this->service->getReceiver()->attach(array_merge($args, array(
            'index' => $this->getName(),
        )));
    }
}
