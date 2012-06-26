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

require_once 'Splunk.php';
require_once 'settings.php';

class ContextTest extends PHPUnit_Framework_TestCase
{
    public function testLoginSuccess()
    {
        $http_response = (object) array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => '
<response>
<sessionKey>068b3021210eb4b67819b1a292302948</sessionKey>
</response>');
        
        $http = $this->getMock('Splunk_Http');
        $http->expects($this->once())
             ->method('post')
             ->will($this->returnValue($http_response));
        
        $context = new Splunk_Context(array(
            'http' => $http,
        ));
        
        $this->assertEquals(NULL, $context->getToken());
        $context->login();
        $this->assertEquals(
            'Splunk 068b3021210eb4b67819b1a292302948',
            $context->getToken());
    }
    
    /**
     * @expectedException           Splunk_HttpException
     * @expectedExceptionMessage    Login failed
     */
    public function testLoginFailDueToBadPassword()
    {
        $http_response = (object) array(
            'status' => 401,
            'reason' => 'Unauthorized',
            'headers' => array(),
            'body' => '
<response>
<messages>
<msg type="WARN">Login failed</msg>
</messages>
</response>');
        
        $http = $this->getMock('Splunk_Http');
        $http->expects($this->once())
             ->method('post')
             ->will($this->throwException(
                new Splunk_HttpException($http_response)));
        
        $context = new Splunk_Context(array(
            'http' => $http,
        ));
        $context->login();
    }
    
    public function testLoginSuccessOnRealServer()
    {
        global $Splunk_testSettings;
        
        $context = new Splunk_Context($Splunk_testSettings['connectArgs']);
        $context->login();
    }
    
    public function testLoginWithToken()
    {
        $context = new Splunk_Context(array(
            'token' => 'Splunk ACEACE'
        ));
        $this->assertEquals('Splunk ACEACE', $context->getToken());
    }
}
