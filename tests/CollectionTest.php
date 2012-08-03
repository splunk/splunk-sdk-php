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

class CollectionTest extends SplunkTest
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testListZero()
    {
        $service = $this->loginToRealService();
        
        $entities = $service->getSavedSearches()->items(array(
            'count' => 0,
        ));
    }
    
    public function testListAllExplicitly()
    {
        $service = $this->loginToRealService();
        
        $entities = $service->getSavedSearches()->items(array(
            'count' => -1,
        ));
        $this->assertGreaterThanOrEqual(2, count($entities),
            'Expected at least two saved searches.');
    }
    
    public function testListAll()
    {
        $service = $this->loginToRealService();
        
        $entities = $service->getSavedSearches()->items();
        $this->assertGreaterThanOrEqual(2, count($entities),
            'Expected at least two saved searches.');
        
        return $entities;
    }
    
    /** @depends testListAll */
    public function testListSlices($entities)
    {
        $service = $this->loginToRealService();
        
        $entityPage1 = $service->getSavedSearches()->items(array(
            'offset' => 0,
            'count' => 1,
        ));
        $this->assertCount(1, $entityPage1);
        
        $entityPage2 = $service->getSavedSearches()->items(array(
            'offset' => 1,
            'count' => 1,
        ));
        $this->assertCount(1, $entityPage2);
        
        $this->assertEquals($entities[0]->getName(), $entityPage1[0]->getName());
        $this->assertEquals($entities[1]->getName(), $entityPage2[0]->getName());
    }
    
    /** @depends testListAll */
    public function testListWithSort($entities)
    {
        $service = $this->loginToRealService();
        
        $lastEntityPage = $service->getSavedSearches()->items(array(
            'count' => 1,
            'sort_dir' => 'desc',
            'sort_mode' => 'alpha_case',
        ));
        $lastEntity = $lastEntityPage[0];
        
        $entityNames = array();
        foreach ($entities as $entity)
            $entityNames[] = $entity->getName();
        sort($entityNames);
        
        $this->assertEquals(
            $entityNames[count($entityNames) - 1],
            $lastEntity->getName());
    }
    
    public function testListInNamespace()
    {
        $service = $this->loginToRealService();
        
        $entities = $service->getSavedSearches()->items(array(
            'namespace' => Splunk_Namespace::createSystem()
        ));
        $this->assertCount(0, $entities,
            'Expected no saved searches in the system namespace.');
        
        return $entities;
    }
}