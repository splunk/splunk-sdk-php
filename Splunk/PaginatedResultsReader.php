<?php
/**
 * Copyright 2013 Splunk, Inc.
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
 * @internal
 */
class Splunk_PaginatedResultsReader implements Iterator
{
    private $job;
    private $args;
    
    private $curPageResultsIterator;
    private $curOffset;
    private $limOffset;
    private $pageMaxSize;
    private $fieldOrderWasReturned;
    
    private $currentElement;
    private $atStart;
    
    /**
     * Do not instantiate this class directly.
     * Please call Splunk_Job::getResults() instead.
     */
    public function __construct($job, $args)
    {
        list($args, $pageMaxSize) =
            Splunk_Util::extractArgument($args, 'pagesize', -1);
        list($args, $offset) =
            Splunk_Util::extractArgument($args, 'offset', 0);
        list($args, $count) =
            Splunk_Util::extractArgument($args, 'count', -1);
        
        if ($pageMaxSize <= 0 && $pageMaxSize != -1)
            throw new InvalidArgumentException(
                'Page size must be positive or -1 (infinity).');
        if ($offset < 0)
            throw new InvalidArgumentException(
                'Offset must be >= 0.');
        if ($count <= 0 && $count != -1)
            throw new InvalidArgumentException(
                'Count must be positive or -1 (infinity).');
        
        // (Use PHP_INT_MAX for infinity internally because it works
        //  well with the min() function.)
        if ($pageMaxSize == -1)
            $pageMaxSize = PHP_INT_MAX;     // internal infinity value
        if ($count == -1)
            $count = PHP_INT_MAX;           // internal infinity value
        
        $this->job = $job;
        $this->args = $args;
        
        $this->curPageResults = NULL;
        $this->curOffset = $offset;
        $this->limOffset = ($count == PHP_INT_MAX) ? PHP_INT_MAX : ($offset + $count);
        $this->pageMaxSize = $pageMaxSize;
        $this->fieldOrderWasReturned = FALSE;
        
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
            if ($this->curOffset >= $this->limOffset)
            {
                return NULL;    // at EOF
            }
            
            $numRemaining = ($this->limOffset == PHP_INT_MAX)
                ? PHP_INT_MAX
                : ($this->limOffset - $this->curOffset);
            
            $curPageMaxSize = min($this->pageMaxSize, $numRemaining);
            if ($curPageMaxSize == PHP_INT_MAX)
                $curPageMaxSize = -1;    // infinity value for getResultsPage()
            
            $this->curPageResultsIterator = new Splunk_ResultsReader(
                $this->job->getResultsPage(
                    array_merge($this->args, array(
                        'offset' => $this->curOffset,
                        'count' => $curPageMaxSize,
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
        
        if ($element instanceof Splunk_ResultsFieldOrder)
        {
            // Only return the field order once.
            if ($this->fieldOrderWasReturned)
            {
                // Don't return the field order again.
                // Skip to the next element.
                return $this->readNextElement();
            }
            else
            {
                $this->fieldOrderWasReturned = TRUE;
            }
        }
        else if (is_array($element))
        {
            $this->curOffset++;
        }
        
        return $element;
    }
}