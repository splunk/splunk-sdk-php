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
    /**
     * Returns a Splunk_Context connected to a real Splunk server.
     * 
     * Written as a test method (with a 'test' prefix) so that other
     * unit tests can depend on it with the '@depends' annotation.
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
     * 
     * Written as a test method (with a 'test' prefix) so that other
     * unit tests can depend on it with the '@depends' annotation.
     */
    public function loginToRealService()
    {
        global $Splunk_testSettings;
        $service = new Splunk_Service($Splunk_testSettings['connectArgs']);
        $service->login();
        return $service;
    }
}