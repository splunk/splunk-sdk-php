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

/**
 * Tests functionality common to all Entity instances.
 * 
 * Since it is not (normally) possible to manipulate an concrete Entity
 * directly (as opposed to a subclass), a choice has to be made regarding
 * which subclass to use. For now, a "saved search" entity will be the
 * concrete subclass used for most tests.
 */
class EntityTest extends SplunkTest
{
    // (This search is installed by default on Splunk 4.x.)
    const SAVED_SEARCH_NAME = 'Errors in the last 24 hours';
    const SAVED_SEARCH_QUERY = 'error OR failed OR severe OR ( sourcetype=access_* ( 404 OR 500 OR 503 ) )';
    
    public function testGetCollectionUsingContext()
    {
        $context = $this->loginToRealContext();
        $response = $context->get('/servicesNS/nobody/search/saved/searches/');
        $this->assertContains(
            '<title>' . self::SAVED_SEARCH_NAME . '</title>',
            $response->body);
    }
    
    public function testGetEntityFromCollection()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(self::SAVED_SEARCH_NAME);
        return $savedSearch;
    }
    
    /** @depends testGetEntityFromCollection */
    public function testGetPropertyOfEntityFromCollection($savedSearch)
    {
        $this->assertEquals(self::SAVED_SEARCH_QUERY, $savedSearch['search']);
    }
    
    public function testGetEntity()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->getReference(
            self::SAVED_SEARCH_NAME);
        return $savedSearch;
    }
    
    /** @depends testGetEntity */
    public function testGetPropertyOfEntity($savedSearch)
    {
        $this->assertEquals(self::SAVED_SEARCH_QUERY, $savedSearch['search']);
    }
    
    public function testGetMissingEntity()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->getReference(
            'NO_SUCH_SEARCH');
        try
        {
            $this->touch($savedSearch);
            $this->assertTrue(FALSE, 'Expected Splunk_HttpException to be thrown.');
        }
        catch (Splunk_HttpException $e)
        {
            $this->assertEquals(404, $e->getResponse()->status);
        }
    }
    
    /**
     * @expectedException Splunk_NoSuchKeyException
     */
    public function testGetMissingEntityFromCollectionInNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::system());
    }
    
    public function testGetMissingEntityInNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->getReference(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::system());
        try
        {
            $this->touch($savedSearch);
            $this->assertTrue(FALSE, 'Expected Splunk_HttpException to be thrown.');
        }
        catch (Splunk_HttpException $e)
        {
            $this->assertEquals(404, $e->getResponse()->status);
        }
    }
    
    public function testGetEntityFromCollectionInNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::user('admin', 'search'));
    }
    
    public function testGetEntityFromCollectionInWildcardNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::user('admin', NULL));
    }
    
    public function testCreateEntity()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->create(
            $this->createTempName(),
            array(
                'search' => 'index=_internal',
            ));
        
        // Clean up
        $savedSearch->delete();
    }
    
    public function testDeleteEntity()
    {
        $entityName = $this->createTempName();
        
        $service = $this->loginToRealService();
        $service->getSavedSearches()->create($entityName, array(
            'search' => 'index=_internal',
        ));
        
        $service->getSavedSearches()->delete($entityName);
    }
    
    // === Utility ===
    
    /**
     * @return      A name for a temporary object that is both easily
     *              identifiable (to facilitate manual cleanup if needed)
     *              and unlikely to collide with other objects in the system.
     */
    private function createTempName()
    {
        return "DELETEME-{$this->createGuid()}";
    }
    
    /** @return     A version 4 (random) UUID. */
    private function createGuid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535));
    }
}
