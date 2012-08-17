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

class NamespaceTest extends SplunkTest
{
    public function testCreateDefault()
    {
        $namespace = Splunk_Namespace::createDefault();
        $this->assertEquals('/services/', $namespace->getPathPrefix());
        $this->assertTrue($namespace->isExact());
    }
    
    public function testCreateUser()
    {
        $namespace = Splunk_Namespace::createUser('theowner', 'theapp');
        $this->assertEquals('/servicesNS/theowner/theapp/', $namespace->getPathPrefix());
        $this->assertTrue($namespace->isExact());
        
        $namespace = Splunk_Namespace::createUser('theowner', NULL);
        $this->assertEquals('/servicesNS/theowner/-/', $namespace->getPathPrefix());
        $this->assertFalse($namespace->isExact());
        
        $namespace = Splunk_Namespace::createUser(NULL, 'theapp');
        $this->assertEquals('/servicesNS/-/theapp/', $namespace->getPathPrefix());
        $this->assertFalse($namespace->isExact());
        
        $namespace = Splunk_Namespace::createUser(NULL, NULL);
        $this->assertEquals('/servicesNS/-/-/', $namespace->getPathPrefix());
        $this->assertFalse($namespace->isExact());
    }
    
    public function testCreateApp()
    {
        $namespace = Splunk_Namespace::createApp('theapp');
        $this->assertEquals('/servicesNS/nobody/theapp/', $namespace->getPathPrefix());
        $this->assertTrue($namespace->isExact());
        
        $namespace = Splunk_Namespace::createApp(NULL);
        $this->assertEquals('/servicesNS/nobody/-/', $namespace->getPathPrefix());
        $this->assertFalse($namespace->isExact());
    }
    
    public function testCreateGlobal()
    {
        $namespace = Splunk_Namespace::createGlobal('theapp');
        $this->assertEquals('/servicesNS/nobody/theapp/', $namespace->getPathPrefix());
        $this->assertTrue($namespace->isExact());
        
        $namespace = Splunk_Namespace::createGlobal(NULL);
        $this->assertEquals('/servicesNS/nobody/-/', $namespace->getPathPrefix());
        $this->assertFalse($namespace->isExact());
    }
    
    public function testCreateSystem()
    {
        $namespace = Splunk_Namespace::createSystem();
        $this->assertEquals('/servicesNS/nobody/system/', $namespace->getPathPrefix());
        $this->assertTrue($namespace->isExact());
    }
    
    public function testCreateExact()
    {
        $namespace = Splunk_Namespace::createExact('theowner', 'theapp', 'user');
        $this->assertEquals('/servicesNS/theowner/theapp/', $namespace->getPathPrefix());
        $this->assertTrue($namespace->isExact());
        $this->assertEquals('theowner', $namespace->getOwner());
        $this->assertEquals('theapp', $namespace->getApp());
        $this->assertEquals('user', $namespace->getSharing());
    }
    
    // === Argument Count Checks ===
    
    /*
     * All of the following tests use a plausible number of arguments, based
     * on the create*() function's name, but which is actually incorrect.
     */
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateDefaultWithTooManyArguments()
    {
        Splunk_Namespace::createDefault('theowner');
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateUserWithTooFewArguments()
    {
        Splunk_Namespace::createUser('theowner');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateAppWithTooManyArguments()
    {
        Splunk_Namespace::createApp('theapp', 'theowner');
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateGlobalWithTooFewArguments()
    {
        Splunk_Namespace::createGlobal();
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateGlobalWithTooManyArguments()
    {
        Splunk_Namespace::createGlobal('theapp', 'theowner');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateSystemWithTooManyArguments()
    {
        Splunk_Namespace::createSystem('theapp');
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateExactWithTooFewArguments()
    {
        Splunk_Namespace::createExact('theapp', 'theowner');
    }
}
