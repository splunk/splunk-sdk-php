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
 * Provides methods for logging events to a Splunk index.
 * 
 * @package Splunk
 */
class Splunk_Receiver
{
    private $service;
    
    public function __construct($service)
    {
        $this->service = $service;
    }
    
    // === Operations ===
    
    /**
     * Logs one or more events to the specified index.
     * 
     * @param string $data  Raw event text.
     *                      This may contain data for multiple events.
     *                      Under the default configuration, line breaks
     *                      ("\n") can be inserted to separate multiple events.
     * @param array $args   (optional) {
     *      'host' => (optional) The value to populate in the host field
     *                for events from this data input.
     *      'host_regex' => (optional) A regular expression used to
     *                      extract the host value from each event.
     *      'index' => (optional) The index to send events from this
     *                 input to. Highly recommended. Defaults to "default".
     *      'source' => (optional) The source value to fill in the
     *                  metadata for this input's events
     *      'sourcetype' => (optional) The sourcetype to apply to
     *                      events from this input.
     * }
     * @throws Splunk_HttpException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTinput#receivers.2Fsimple
     */
    public function submit($data, $args=array())
    {
        // (Avoid the normal post() method, since we aren't sending form data.)
        $this->service->request(
            'post', '/services/receivers/simple',
            array('Content-Type' => 'text/plain'),
            $data,
            $args);
    }
    
    /**
     * Creates a stream for logging events to the specified index.
     * 
     * @param array $args   (optional) {
     *      'host' => (optional) The value to populate in the host field
     *                for events from this data input.
     *      'host_regex' => (optional) A regular expression used to
     *                      extract the host value from each event.
     *      'index' => (optional) The index to send events from this
     *                 input to. Highly recommended. Defaults to "default".
     *      'source' => (optional) The source value to fill in the
     *                  metadata for this input's events
     *      'sourcetype' => (optional) The sourcetype to apply to
     *                      events from this input.
     * }
     * @return resource     A stream that you can write event text to.
     * @throws Splunk_ConnectException
     * @link http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI/RESTinput#receivers.2Fstream
     */
    public function attach($args=array())
    {
        $scheme = $this->service->getScheme();
        $host = $this->service->getHost();
        $port = $this->service->getPort();
        
        if ($scheme == 'http')
            $stream = fsockopen($host, $port);
        else if ($scheme == 'https')
            $stream = fsockopen('ssl://' . $host, $port);
        else
            throw new Splunk_UnsupportedOperationException(
                'Unsupported URL scheme.');
        if ($stream === FALSE)
            throw new Splunk_ConnectException();
        
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