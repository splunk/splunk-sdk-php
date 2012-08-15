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

class ReceiverTest extends SplunkTest
{
    /**
     * @group slow
     */
    public function testSubmitOneEvent()
    {
        $this->submitEvents(1, 2.0);
    }
    
    /**
     * @group slow
     */
    public function testSubmitMultipleEvents()
    {
        $this->submitEvents(3, 2.0);
    }
    
    /**
     * @group slow
     */
    public function testAttachAndSendOneEvent()
    {
        $this->attachAndSendEvents(1, 2.0);
    }
    
    /**
     * @group slow
     */
    public function testAttachAndSendMultipleEvents()
    {
        $this->attachAndSendEvents(3, 2.0);
    }
    
    // === Utility ===
    
    private function submitEvents($numEvents, $indexDelay)
    {
        $service = $this->loginToRealService();
        
        $expectedEvents = array();
        $uniqueString = uniqid();
        for ($i = 0; $i < $numEvents; $i++)
             $expectedEvents[] = sprintf(
                '[%s] DELETEME-%s-%d', date('d/M/Y:H:i:s O'), $uniqueString, $i);
        
        $data = implode("\n", $expectedEvents);
        
        // Submit events
        $service->getReceiver()->submit($data, array(
            'index' => '_internal',
            'sourcetype' => 'php_unit_test',
        ));
        
        // Delay so that Splunk actually indexes the events
        usleep($indexDelay * 1000000);
        
        // Ensure the events are there
        $job = $service->getJobs()->create(
            'search index=_internal sourcetype=php_unit_test | head ' . $numEvents, array(
                'exec_mode' => 'blocking'
            ));
        $actualEvents = array();
        foreach ($job->getResults() as $result)
            if (is_array($result))
                $actualEvents[] = $result['_raw'];
        $actualEvents = array_reverse($actualEvents);
        
        $this->assertEquals($expectedEvents, $actualEvents);
    }
    
    private function attachAndSendEvents($numEvents, $indexDelay)
    {
        $service = $this->loginToRealService();
        
        $expectedEvents = array();
        $uniqueString = uniqid();
        for ($i = 0; $i < $numEvents; $i++)
             $expectedEvents[] = sprintf(
                '[%s] DELETEME-%s-%d', date('d/M/Y:H:i:s O'), $uniqueString, $i);
        
        $data = implode("\n", $expectedEvents);
        
        // Submit events
        $eventOutputStream = $service->getReceiver()->attach(array(
            'index' => '_internal',
            'sourcetype' => 'php_unit_test',
        ));
        Splunk_Util::fwriteall($eventOutputStream, $data);
        fclose($eventOutputStream);
        
        // Delay so that Splunk actually indexes the events
        usleep($indexDelay * 1000000);
        
        // Ensure the events are there
        $job = $service->getJobs()->create(
            'search index=_internal sourcetype=php_unit_test | head ' . $numEvents, array(
                'exec_mode' => 'blocking'
            ));
        $actualEvents = array();
        foreach ($job->getResults() as $result)
            if (is_array($result))
                $actualEvents[] = $result['_raw'];
        $actualEvents = array_reverse($actualEvents);
        
        $this->assertEquals($expectedEvents, $actualEvents);
    }
}