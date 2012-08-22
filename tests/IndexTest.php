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

class IndexTest extends SplunkTest
{
    public function testSubmit()
    {
        $service = $this->loginToRealService();
        $index = $service->getIndexes()->get('_internal');
        
        // (Only test that this code path appears to work.
        //  The ReceiverTest checks whether submitted events actually show up.)
        $index->submit('DELETEME', array(
            'sourcetype' => 'php_unit_test',
        ));
    }
    
    public function testAttach()
    {
        $service = $this->loginToRealService();
        $index = $service->getIndexes()->get('_internal');
        
        // (Only test that this code path appears to work.
        //  The ReceiverTest checks whether submitted events actually show up.)
        $eventOutputStream = $index->attach(array(
            'sourcetype' => 'php_unit_test',
        ));
        Splunk_Util::fwriteall($eventOutputStream, 'DELETEME');
        fclose($eventOutputStream);
    }
}