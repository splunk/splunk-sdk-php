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

class AtomFeedTest extends SplunkTest
{
    public function testParseScalar()
    {
        $xmlString = '<container>1</container>';
        $expectedValue = 1;
        
        $this->checkParseResult($xmlString, $expectedValue);
    }
    
    public function testParseDict()
    {
        $xmlString = '
    <container xmlns:s="http://dev.splunk.com/ns/rest">
        <s:dict>
            <s:key name="k1">v1</s:key>
            <s:key name="k2">v2</s:key>
        </s:dict>
    </container>';
        $expectedValue = array(
            'k1' => 'v1',
            'k2' => 'v2',
        );
        
        $this->checkParseResult($xmlString, $expectedValue);
    }
    
    public function testParseList()
    {
        $xmlString = '
    <container xmlns:s="http://dev.splunk.com/ns/rest">
        <s:list>
            <s:item>e1</s:item>
            <s:item>e2</s:item>
        </s:list>
    </container>';
        $expectedValue = array(
            'e1',
            'e2',
        );
        
        $this->checkParseResult($xmlString, $expectedValue);
    }
    
    public function testParseEmpty()
    {
        $xmlString = '<container></container>';
        $expectedValue = '';
        
        $this->checkParseResult($xmlString, $expectedValue);
    }
    
    public function testParseComplex()
    {
        $xmlString = '
    <content type="text/xml" xmlns:s="http://dev.splunk.com/ns/rest">
      <s:dict>
        <s:key name="action.email">0</s:key>
        <s:key name="action.email.sendresults"></s:key>
        <s:key name="eai:acl">
          <s:dict>
            <s:key name="can_write">1</s:key>
            <s:key name="perms">
              <s:dict>
                <s:key name="read">
                  <s:list>
                    <s:item>*</s:item>
                  </s:list>
                </s:key>
                <s:key name="write">
                  <s:list>
                    <s:item>admin</s:item>
                  </s:list>
                </s:key>
              </s:dict>
            </s:key>
            <s:key name="removable">0</s:key>
            <s:key name="sharing">app</s:key>
          </s:dict>
        </s:key>
      </s:dict>
    </content>';
        $expectedValue = array(
            'action.email' => '0',
            'action.email.sendresults' => '',
            'eai:acl' => array(
                'can_write' => '1',
                'perms' => array(
                    'read' => array('*'),
                    'write' => array('admin'),
                ),
                'removable' => '0',
                'sharing' => 'app',
            ),
        );
        
        $this->checkParseResult($xmlString, $expectedValue);
    }
    
    private function checkParseResult($xmlString, $expectedValue)
    {
        $this->assertEquals(
            $expectedValue,
            Splunk_AtomFeed::parseValueInside(
                new SimpleXMLElement($xmlString)));
    }
}