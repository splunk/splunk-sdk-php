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

class PaginatedResultsReaderTest extends SplunkTest
{
    public function testResultsPagination()
    {
        list($job, $results1) = $this->createJobWithTwelveResults();
        
        $resultsIter2 = $job->getResults();
        $results2 = $this->createListFromRowsInIterator($resultsIter2);
        $this->assertEquals($results1, $results2);
    }
    
    public function testResultsPaginationWithCustomBounds()
    {
        list($job, $results1) = $this->createJobWithTwelveResults();
        
        $resultsIter2 = $job->getResults(array(
            'pagesize' => 1,
        ));
        $results2 = $this->createListFromRowsInIterator($resultsIter2);
        $this->assertEquals($results1, $results2);
        
        // Request sublist
        $this->assertEquals(
            array_slice($results1, 4, 6),
            $this->createListFromRowsInIterator(
                $job->getResults(array(
                    'offset' => 4,
                    'count' => 6,
                    'pagesize' => 4,
                ))
            ));
        
        // Request sublist that extends past the end
        $this->assertEquals(
            array_slice($results1, 8, 6),
            $this->createListFromRowsInIterator(
                $job->getResults(array(
                    'offset' => 8,
                    'count' => 6,
                    'pagesize' => 4,
                ))
            ));
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testResultsPaginationWithZeroCount()
    {
        list($job, $results1) = $this->createJobWithTwelveResults();
        
        $job->getResults(array(
            'count' => 0,
        ));
    }
    
    public function testResultsPaginationWithInfinityCount()
    {
        list($job, $results1) = $this->createJobWithTwelveResults();
        
        $results2Iter = $job->getResults(array(
            'count' => -1,
        ));
        $results2 = $this->createListFromRowsInIterator($results2Iter);
        $this->assertEquals($results1, $results2);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testResultsPaginationWithZeroPagesize()
    {
        list($job, $results1) = $this->createJobWithTwelveResults();
        
        $job->getResults(array(
            'pagesize' => 0,
        ));
    }
    
    public function testResultsPaginationWithInfinityPagesize()
    {
        list($job, $results1) = $this->createJobWithTwelveResults();
        
        $results2Iter = $job->getResults(array(
            'pagesize' => -1,
        ));
        $results2 = $this->createListFromRowsInIterator($results2Iter);
        $this->assertEquals($results1, $results2);
    }
    
    // === Utility ===
    
    private function createJobWithTwelveResults()
    {
        $service = $this->loginToRealService();
        
        $job = $service->getJobs()->create(
            'search index=_internal | head 12',
            array(
                'exec_mode' => 'blocking',
            )
        );
        
        $resultsIter1 = new Splunk_ResultsReader($job->getResultsPage());
        $results1 = $this->createListFromRowsInIterator($resultsIter1);
        $this->assertEquals(12, count($results1),
            'Update the search expression to return the expected number of results.');
        
        return array($job, $results1);
    }
    
    private function createListFromRowsInIterator($iter)
    {
        $list = array();
        foreach ($iter as $element)
            if (is_array($element))
                $list[] = $element;
        return $list;
    }
}