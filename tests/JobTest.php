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

require_once 'SplunkTest.php';

class JobTest extends SplunkTest
{
    public function testGetTimeout()
    {
        $http = $this->getMock('Splunk_Http');
        $service = new Splunk_Service(array(
            'http' => $http,
        ));
        
        // Login
        $http_response = (object) array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => '
<response>
<sessionKey>068b3021210eb4b67819b1a292302948</sessionKey>
</response>');
        $http->expects($this->once())
             ->method('post')
             ->will($this->returnValue($http_response));
        $service->login();
        
        // Get job
        $http_response = (object) array(
            'status' => 204,
            'reason' => 'No Content',
            'headers' => array(),
            'body' => '');
        $http->expects($this->atLeastOnce())
             ->method('get')
             ->will($this->returnValue($http_response));
        $job = $service->getJobs()->getReference('A_JOB');
        
        // Try to touch job when server refuses to return it
        try
        {
            $this->touch($job);
            $this->assertTrue(FALSE, 'Expected Splunk_HttpException to be thrown.');
        }
        catch (Splunk_HttpException $e)
        {
            $this->assertEquals(204, $e->getResponse()->status);
        }
    }
}