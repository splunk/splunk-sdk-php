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
 * Provides methods for logging events to a Splunk index.
 * 
 * @package Splunk
 */
class Splunk_Receiver
{
    private $service;
    
    /** @internal */
    public function __construct($service)
    {
        $this->service = $service;
    }
    
    // === Operations ===
    
    /**
     * Logs one or more events to the specified index.
     * 
     * In addition to the index name it is highly recommended to specify
     * a sourcetype explicitly.
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
     *      **index**: (optional) The index to send events from this
     *                 input to. Highly recommended. Defaults to "default".<br/>
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
        // (Avoid the normal post() method, since we aren't sending form data.)
        $this->service->sendRequest(
            'post', '/services/receivers/simple',
            array('Content-Type' => 'text/plain'),
            $data,
            $args);
    }
    
    /**
     * Creates a stream for logging events to the specified index.
     * 
     * In addition to the index name it is highly recommended to specify
     * a sourcetype explicitly.
     * 
     * The returned stream should eventually be closed via fclose().
     * 
     * @param array $args   (optional) {<br/>
     *      **host**: (optional) The value to populate in the host field
     *                for events from this data input.<br/>
     *      **host_regex**: (optional) A regular expression used to
     *                      extract the host value from each event.<br/>
     *      **index**: (optional) The index to send events from this
     *                 input to. Highly recommended. Defaults to "default".<br/>
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
        $scheme = $this->service->getScheme();
        $host = $this->service->getHost();
        $port = $this->service->getPort();
        
        $errno = 0;
        $errstr = '';
        if ($scheme == 'http')
            $stream = @fsockopen($host, $port, /*out*/ $errno, /*out*/ $errstr);
        else if ($scheme == 'https')
            $stream = @fsockopen('ssl://' . $host, $port, /*out*/ $errno, /*out*/ $errstr);
        else
            throw new Splunk_UnsupportedOperationException(
                'Unsupported URL scheme.');
        if ($stream === FALSE)
            throw new Splunk_ConnectException($errstr, $errno);
        
        $path = '/services/receivers/stream?' . http_build_query($args);
        $token = $this->service->getToken();
        
        $headers = array(
            "POST {$path} HTTP/1.1\r\n",
            "Host: {$host}:{$port}\r\n",
            "Accept-Encoding: identity\r\n",
            "Authorization: {$token}\r\n",
            "X-Splunk-Input-Mode: Streaming\r\n",
            "\r\n",
        );
        Splunk_Util::fwriteall($stream, implode('', $headers));
        
        return $stream;
    }
}