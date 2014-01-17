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
        $response = $context->sendGet('/servicesNS/nobody/search/saved/searches/');
        $this->assertContains(
            '<title>' . self::SAVED_SEARCH_NAME . '</title>',
            $response->body);
    }
    
    public function testGetEntity()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(self::SAVED_SEARCH_NAME);
        return $savedSearch;
    }
    
    /** @depends testGetEntity */
    public function testGetPropertyOfEntity($savedSearch)
    {
        $this->assertEquals(self::SAVED_SEARCH_QUERY, $savedSearch['search']);
    }
    
    public function testGetEntityReference()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->getReference(
            self::SAVED_SEARCH_NAME);
        return $savedSearch;
    }
    
    /** @depends testGetEntityReference */
    public function testGetPropertyOfEntityReference($savedSearch)
    {
        $this->assertEquals(self::SAVED_SEARCH_QUERY, $savedSearch['search']);
    }
    
    public function testGetMissingEntityReference()
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
     * @expectedException Splunk_NoSuchEntityException
     */
    public function testGetMissingEntityInNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::createSystem());
    }
    
    public function testGetMissingEntityReferenceInNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->getReference(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::createSystem());
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
    
    public function testGetEntityInNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::createUser('admin', 'search'));
    }
    
    public function testGetEntityInWildcardNamespace()
    {
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->get(
            self::SAVED_SEARCH_NAME,
            Splunk_Namespace::createUser('admin', NULL));
    }
    
    /**
     * @expectedException Splunk_AmbiguousEntityNameException
     */
    public function testGetAmbiguousEntity()
    {
        $this->runAmbiguousEntityTest('doGetAmbiguousEntity');
    }
    
    private function doGetAmbiguousEntity($service, $entityName)
    {
        $service->getSavedSearches()->get(
            $entityName,
            Splunk_Namespace::createUser(NULL, 'search'));
    }
    
    /**
     * @expectedException Splunk_AmbiguousEntityNameException
     */
    public function testGetAmbiguousEntityReference()
    {
        $this->runAmbiguousEntityTest('doGetAmbiguousEntityReference');
    }
    
    private function doGetAmbiguousEntityReference($service, $entityName)
    {
        $ambigSavedSearch = $service->getSavedSearches()->getReference(
            $entityName,
            Splunk_Namespace::createUser(NULL, 'search'));
        $this->touch($ambigSavedSearch);
    }
    
    private function runAmbiguousEntityTest($testBodyFunc)
    {
        $entityName = $this->createTempName();
        
        $service = $this->loginToRealService();
        $savedSearch1 = $service->getSavedSearches()->create(
            $entityName,
            array(
                'namespace' => Splunk_Namespace::createUser('admin', 'search'),
                'search' => 'index=_internal | head 1',
            ));
        $savedSearch2 = $service->getSavedSearches()->create(
            $entityName,
            array(
                'namespace' => Splunk_Namespace::createApp('search'),
                'search' => 'index=_internal | head 2',
            ));
        
        try
        {
            $this->$testBodyFunc($service, $entityName);
        }
        catch (Exception $e)
        {
            // Cleanup
            $savedSearch1->delete();
            $savedSearch2->delete();
            
            throw $e;
        }
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
    
    public function testDeleteEntityReferenceMakesNoGets()
    {
        list($service, $http) = $this->loginToMockService();
        
        $http->expects($this->never())
             ->method('get');
        $http->expects($this->once())
             ->method('delete')
             ->will($this->returnValue((object) array(
                'status' => 200,
                'reason' => 'OK',
                'headers' => array(),
                'body' => '')));
        $service->getSavedSearches()->getReference('IGNORED_NAME')->delete();
    }
    
    public function testUpdateEntity()
    {
        $entityName = $this->createTempName();
        
        $service = $this->loginToRealService();
        $savedSearch = $service->getSavedSearches()->create($entityName, array(
            'search' => 'index=_internal',
        ));
        $this->assertEquals('index=_internal', $savedSearch['search']);
        
        $savedSearch2 = $savedSearch->update(array(
            'search' => 'index=_internal | head 1',
        ));
        $this->assertSame($savedSearch, $savedSearch2);
        $this->assertEquals('index=_internal | head 1', $savedSearch['search']);
        
        $savedSearch->delete();
    }
    
    public function testUpdateEntityReferenceMakesNoGets()
    {
        $secondPostReturnValue = (object) array(
            'status' => 200,
            'reason' => 'OK',
            'headers' => array(),
            'body' => '');
        
        list($service, $http) = $this->loginToMockService($secondPostReturnValue);
        
        $http->expects($this->never())
             ->method('get');
        $service->getSavedSearches()->getReference('IGNORED_NAME')->update(array(
            'search' => 'index=_internal | head 1',
        ));
    }
    
    public function testRefreshEntityInCustomNamespace()
    {
        $service = $this->loginToRealService();
        
        $views = new Splunk_Collection($service, 'data/ui/views/');
        $entity = $views->get(
            'charting',
            Splunk_Namespace::createApp('search'));

        $entity->refresh();
    }
    
    public function testNamespaceIsNotAProperty()
    {
        $service = $this->loginToRealService();
        
        // Setup
        $savedSearch = $service->getSavedSearches()->create($this->createTempName(), array(
            'namespace' => Splunk_Namespace::createUser('admin', 'launcher'),
            'search' => 'search index=_internal | head 1',
        ));
        
        // Test
        $this->assertTrue(isset($savedSearch['search']));
        $this->assertFalse(isset($savedSearch['namespace']));
        $savedSearch->update(array(
            'search' => 'search index=_internal | head 2',
        ));
        $this->assertFalse(isset($savedSearch['namespace']));
        $savedSearch->refresh();
        $this->assertEquals('search index=_internal | head 2', $savedSearch['search']);
        
        // Teardown
        $savedSearch->delete();
    }
    
    public function testGetContent()
    {
        $service = $this->loginToRealService();
        
        $job = $service->search('search index=_internal | head 1', array(
            'exec_mode' => 'blocking',
        ));
        
        $this->assertTrue($job->isDone());
        $this->assertTrue('1' === $job['isDone']);
        $content = $job->getContent();
        $this->assertTrue('1' === $content['isDone']);
    }
    
    public function testGetNamespaceOfWildcardedReference()
    {
        $service = $this->loginToRealService();
        
        $requestNamespace = Splunk_Namespace::createUser(NULL, NULL);
        $this->assertFalse($requestNamespace->isExact());
        
        $index = $service->getIndexes()->getReference(
            "_internal",
            $requestNamespace);
        
        $actualNamespace = $index->getNamespace();
        $this->assertTrue($actualNamespace !== NULL);
        $this->assertTrue($actualNamespace !== $requestNamespace);
        $this->assertTrue($actualNamespace->isExact());
    }
}
