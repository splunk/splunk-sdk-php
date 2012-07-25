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
 * Base class of all unit tests for the Splunk PHP SDK.
 */
abstract class SplunkTest extends PHPUnit_Framework_TestCase
{
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
    public function loginToRealContext()
    {
        global $Splunk_testSettings;
        $context = new Splunk_Context($Splunk_testSettings['connectArgs']);
        $context->login();
        return $context;
    }
    
    /**
     * Returns a Splunk_Service connected to a real Splunk server.
     */
    public function loginToRealService()
    {
        global $Splunk_testSettings;
        $service = new Splunk_Service($Splunk_testSettings['connectArgs']);
        $service->login();
        return $service;
    }
    
    /**
     * Returns a Splunk_Service connected to a mock Http object.
     */
    public function loginToMockService($secondPostReturnValue=NULL)
    {
        $http = $this->getMock('Splunk_Http');
        $service = new Splunk_Service(array(
            'http' => $http,
        ));
        
        $httpResponse = (object) array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => '
<response>
<sessionKey>068b3021210eb4b67819b1a292302948</sessionKey>
</response>');
        if ($secondPostReturnValue === NULL)
        {
            $http->expects($this->once())
                 ->method('post')
                 ->will($this->returnValue($httpResponse));
        }
        else
        {
            $http->expects($this->exactly(2))
                 ->method('post')
                 ->will($this->onConsecutiveCalls(
                    $this->returnValue($httpResponse),
                    $this->returnValue($secondPostReturnValue)
                 ));
        }
        $service->login();
        
        return array($service, $http);
    }
    
    /**
     * Forcefully loads the specified entity from the server,
     * if it hasn't been already.
     */
    public function touch($entity)
    {
        $entity->getName();    // force load from server
    }
}