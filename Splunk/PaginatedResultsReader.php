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

/**
 * @package Splunk
 */
class Splunk_PaginatedResultsReader implements Iterator
{
    private $job;
    private $args;
    
    private $curPageResultsIterator;
    private $curOffset;
    private $limOffset;
    private $pageSize;
    
    private $currentElement;
    private $atStart;
    
    // NOTE: This class should not be instantiated directly.
    //       Please call Splunk_Job::getPaginatedResults() instead.
    public function __construct($job, $args)
    {
        list($args, $pageSize) =
            Splunk_Util::extractArgument($args, 'pagesize', -1);
        list($args, $offset) =
            Splunk_Util::extractArgument($args, 'offset', 0);
        list($args, $count) =
            Splunk_Util::extractArgument($args, 'count', -1);
        
        if ($pageSize <= 0 && $pageSize != -1)
            throw new InvalidArgumentException(
                'Page size must be positive or -1.');
        
        $this->job = $job;
        $this->args = $args;
        
        $this->curPageResults = NULL;
        $this->curOffset = $offset;
        $this->limOffset = ($count == -1) ? PHP_INT_MAX : ($offset + $count);
        $this->pageSize = $pageSize;
        
        $this->currentElement = $this->readNextElement();
        $this->atStart = TRUE;
    }
    
    // === Iterator Methods ===
    
    public function rewind()
    {
        if ($this->atStart)
            return;
        
        throw new Splunk_UnsupportedOperationException(
            'Cannot rewind after reading past the first element.');
    }
    
    public function valid()
    {
        return ($this->currentElement !== NULL);
    }
    
    public function next()
    {
        $this->currentElement = $this->readNextElement();
        $this->atStart = FALSE;
    }
    
    public function current()
    {
        return $this->currentElement;
    }
    
    public function key()
    {
        return NULL;
    }
    
    // === Read Next Element ===
    
    private function readNextElement()
    {
        if ($this->curPageResultsIterator == NULL ||
            !$this->curPageResultsIterator->valid())
        {
            $numRemaining = $this->limOffset - $this->curOffset;
            if ($numRemaining === 0)
            {
                return NULL;    // at EOF
            }
            
            $this->curPageResultsIterator = new Splunk_ResultsReader(
                $this->job->getResults(
                    array_merge($this->args, array(
                        'offset' => $this->curOffset,
                        'count' => min($this->pageSize, $numRemaining),
                        'output_mode' => 'xml',
                    ))));
            if (!$this->curPageResultsIterator->valid())
            {
                $this->limOffset = $this->curOffset;    // remember EOF position
                return NULL;    // at EOF
            }
        }
        
        assert($this->curPageResultsIterator->valid());
        $element = $this->curPageResultsIterator->current();
        $this->curPageResultsIterator->next();
        
        $this->curOffset++;
        return $element;
    }
}