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

class HttpTest extends SplunkTest
{
    // NOTE: Ideally there would be tests that would create a local HTTP
    //       server and send canned responses to the Http class.
    //       Unfortunately there is no threading in PHP and forking
    //       requires the PCNTL extension, which is not installed by default.
    //       
    //       Therefore most of the testing of Http is indirect, via all the
    //       other unit tests that depend on it indirectly to work correctly.
    
    public function testGet()
    {
        $http = new Splunk_Http();
        $response = $http->get('http://www.splunk.com/');
        
        $this->assertEquals(200, $response->status);
        $this->assertContains('<head>', $response->body);
    }
    
    public function testConnectFailure()
    {
        $http = new Splunk_Http();
        try
        {
            $response = $http->get('http://127.0.0.1:9999/');
            $this->fail('Expected Splunk_ConnectException.');
        }
        catch (Splunk_ConnectException $e)
        {
            $this->assertNotEquals('', $e->getMessage(),
                'Expected Splunk_ConnectException with a message.');
        }
    }
}
