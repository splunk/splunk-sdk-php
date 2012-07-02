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
    public function testEnumerateAll()
    {
        $service = $this->loginToRealService();
        
        $entities = $service->getSavedSearches()->enumerate();
        $this->assertGreaterThanOrEqual(2, count($entities),
            'Expected at least two saved searches.');
        
        return $entities;
    }
    
    /** @depends testEnumerateAll */
    public function testEnumerateSlices($entities)
    {
        $service = $this->loginToRealService();
        
        $entityPage1 = $service->getSavedSearches()->enumerate(array(
            'offset' => 0,
            'count' => 1,
        ));
        $this->assertCount(1, $entityPage1);
        
        $entityPage2 = $service->getSavedSearches()->enumerate(array(
            'offset' => 1,
            'count' => 1,
        ));
        $this->assertCount(1, $entityPage2);
        
        $this->assertEquals($entities[0]->getName(), $entityPage1[0]->getName());
        $this->assertEquals($entities[1]->getName(), $entityPage2[0]->getName());
    }
    
    /** @depends testEnumerateAll */
    public function testEnumerateWithSort($entities)
    {
        $service = $this->loginToRealService();
        
        $lastEntityPage = $service->getSavedSearches()->enumerate(array(
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
    
    public function testEnumerateInNamespace()
    {
        $service = $this->loginToRealService();
        
        $entities = $service->getSavedSearches()->enumerate(array(
            'namespace' => Splunk_Namespace::system()
        ));
        $this->assertCount(0, $entities,
            'Expected no saved searches in the system namespace.');
        
        return $entities;
    }
}