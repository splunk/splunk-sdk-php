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
 * Parses XML search results received from jobs.
 * 
 * Results are obtained by iterating over an instance of this class
 * using a foreach loop. Each result can either be an associative array or
 * a Splunk_ResultsMessage. If the result is an associative array, it maps each field
 * name to either a single value or an array of values.
 * 
 * $resultsReader = new Splunk_ResultsReader(...);
 * foreach ($resultsReader as $result)
 * {
 *     if (is_array($result))
 *     {
 *         // Process a normal result
 *         print "{\r\n";
 *         foreach ($result as $key => $valueOrValues)
 *         {
 *             if (is_array($valueOrValues))
 *             {
 *                 $values = $valueOrValues;
 *                 $valuesString = implode(',', $values);
 *                 print "  {$key} => [{$valuesString}]\r\n";
 *             }
 *             else
 *             {
 *                 $value = $valueOrValues;
 *                 print "  {$key} => {$value}\r\n";
 *             }
 *         }
 *         print "}\r\n";
 *     }
 *     else if ($result instanceof Splunk_ResultsMessage)
 *     {
 *         // Process a message
 *         print "[{$result->getType()}] {$result->getText()}\r\n";
 *     }
 * }
 * 
 * @package Splunk
 */
class Splunk_ResultsReader implements Iterator
{
    private $emptyXml;
    private $xmlReader;
    
    private $currentElement;
    private $atStart;
    
    public function __construct($streamOrXmlString)
    {
        if (is_string($streamOrXmlString))
        {
            $string = $streamOrXmlString;
            $stream = Splunk_ResultsReader::createStringStream($string);
        }
        else
        {
            $stream = $streamOrXmlString;
        }
        
        // Search jobs lacking results return a blank document (with HTTP 200)
        if (feof($stream))
        {
            $this->emptyXml = TRUE;
            
            $this->currentElement = NULL;
            $this->atStart = TRUE;
            return;
        }
        else
        {
            $this->emptyXml = FALSE;
        }
        
        $streamUri = Splunk_StreamStream::createUriForStream($stream);
        
        $this->xmlReader = new XMLReader();
        $this->xmlReader->open($streamUri);
        
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
        $xr = $this->xmlReader;
        
        if ($this->emptyXml)
            return NULL;
        
        // Prevent subsequent invocations of rewind()
        $this->atStart = FALSE;
        
        while ($xr->read())
        {
            // Read: /messages/msg
            if ($xr->nodeType == XMLReader::ELEMENT &&
                $xr->name === 'msg')
            {
                $type = $xr->getAttribute('type');
                
                // Read: /messages/msg/[TEXT]
                if (!$xr->read())
                    break;
                assert ($xr->nodeType == XMLReader::TEXT);
                $text = $xr->value;
                
                return new Splunk_ResultsMessage($type, $text);
            }
            
            // Read: /result
            if ($xr->nodeType == XMLReader::ELEMENT &&
                $xr->name === 'result')
            {
                return $this->readResult();
            }
        }
        return NULL;
    }
    
    private function readResult()
    {
        $xr = $this->xmlReader;
        
        $lastKey = NULL;
        $lastValues = array();
        $insideValue = FALSE;
        
        $result = array();
        while ($xr->read())
        {
            // Begin: /result/field
            if ($xr->nodeType == XMLReader::ELEMENT &&
                $xr->name === 'field')
            {
                $lastKey = $xr->getAttribute('k');
                $lastValues = array();
            }
            
            // Begin: /result/field/value
            // Begin: /result/field/v
            if ($xr->nodeType == XMLReader::ELEMENT &&
                ($xr->name === 'value' || $xr->name === 'v'))
            {
                $insideValue = TRUE;
            }
            
            // Read: /result/field/value/text/[TEXT]
            // Read: /result/field/v/[TEXT]
            if ($insideValue &&
                $xr->nodeType == XMLReader::TEXT)
            {
                $lastValues[] = $xr->value;
            }
            
            // End: /result/field/value
            // End: /result/field/v
            if ($xr->nodeType == XMLReader::END_ELEMENT &&
                ($xr->name === 'value' || $xr->name === 'v'))
            {
                $insideValue = FALSE;
            }
            
            // End: /result/field
            if ($xr->nodeType == XMLReader::END_ELEMENT &&
                $xr->name === 'field')
            {
                if (count($lastValues) === 1)
                {
                    $lastValues = $lastValues[0];
                }
                $result[$lastKey] = $lastValues;
            }
            
            // End: /result
            if ($xr->nodeType == XMLReader::END_ELEMENT &&
                $xr->name === 'result')
            {
                break;
            }
        }
        return $result;
    }
    
    // === Utility ===
    
    private static function createStringStream($string)
    {
        $stream = fopen('php://memory', 'rwb');
        fwrite($stream, $string);
        fseek($stream, 0);
        
        /*
         * fseek() causes the next call to feof() to always return FALSE,
         * which is undesirable if we seeked to the EOF. In this case,
         * attempt a read past EOF so that the next call to feof() returns
         * TRUE as expected.
         */
        if ($string === '')
            fread($stream, 1);  // trigger EOF explicitly
        
        return $stream;
    }
}
