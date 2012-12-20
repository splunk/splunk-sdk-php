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

/* Common imports for all unit tests */
require_once 'Splunk.php';
require_once 'settings.php';

/**
 * Base class of all unit tests for the Splunk SDK for PHP.
 */
abstract class SplunkTest extends PHPUnit_Framework_TestCase
{
    const MOCK_SESSION_TOKEN = 'deadbeefdeadbeefdeadbeefdeadbeef';
    
    private $initialOpenStreamCount = -1;
    
    public function setUp()
    {
        $this->initialOpenStreamCount = Splunk_StreamStream::getOpenStreamCount();
    }
    
    public function tearDown()
    {
        // Make sure all streams are being closed appropriately
        // (especially in error scenarios).
        $finalOpenStreamCount = Splunk_StreamStream::getOpenStreamCount();
        $this->assertEquals($this->initialOpenStreamCount, $finalOpenStreamCount,
            "Number of open streams after test ({$finalOpenStreamCount}) " .
            "does not match number before test ({$this->initialOpenStreamCount}).");
    }
    
    /**
     * Returns a Splunk_Context connected to a real Splunk server.
     */
    protected function loginToRealContext()
    {
        global $SplunkTests_connectArguments;
        $context = new Splunk_Context($SplunkTests_connectArguments);
        $context->login();
        return $context;
    }
    
    /**
     * Returns a Splunk_Service connected to a real Splunk server.
     */
    protected function loginToRealService()
    {
        global $SplunkTests_connectArguments;
        $service = new Splunk_Service($SplunkTests_connectArguments);
        $service->login();
        return $service;
    }
    
    /**
     * Returns a Splunk_Service connected to a mock Http object.
     */
    protected function loginToMockService(
        $secondPostReturnValue=NULL,
        $secondPostExpectedArgs=NULL,
        $extraConnectArgs=array())
    {
        $http = $this->getMock('Splunk_Http');
        $service = new Splunk_Service(array_merge(array(
            'http' => $http,
        ), $extraConnectArgs));
        
        $httpResponse = (object) array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => '
<response>
<sessionKey>' . SplunkTest::MOCK_SESSION_TOKEN . '</sessionKey>
</response>');
        if ($secondPostReturnValue === NULL)
        {
            $http->expects($this->once())
                 ->method('post')
                 ->will($this->returnValue($httpResponse));
        }
        else
        {
            $http->expects($this->at(0))
                 ->method('post')
                 ->will($this->returnValue($httpResponse))
                 ->with($this->anything());
            
            $m = $http->expects($this->at(1))
                 ->method('post')
                 ->will($this->returnValue($secondPostReturnValue));
            if ($secondPostExpectedArgs !== NULL)
                call_user_func_array(array($m, 'with'), $secondPostExpectedArgs);
        }
        $service->login();
        
        return array($service, $http);
    }
    
    /**
     * Forcefully loads the specified entity from the server,
     * if it hasn't been already.
     */
    protected function touch($entity)
    {
        $entity->getName();    // force load from server
    }
    
    /**
     * @return      A name for a temporary object that is both easily
     *              identifiable (to facilitate manual cleanup if needed)
     *              and unlikely to collide with other objects in the system.
     */
    protected function createTempName()
    {
        return "DELETEME-{$this->createGuid()}";
    }
    
    /** @return     A version 4 (random) UUID. */
    private function createGuid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535));
    }
}