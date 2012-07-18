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

class ResultsMessageTest extends SplunkTest
{
    // (Exists solely to give the getters code coverage, preventing
    //  Splunk_ResultsMessage from sticking out on code coverage reports.)
    public function testGetters() {
        $message = new Splunk_ResultsMessage('DEBUG', 'Hello World');
        $this->assertEquals('DEBUG', $message->getType());
        $this->assertEquals('Hello World', $message->getText());
    }
}