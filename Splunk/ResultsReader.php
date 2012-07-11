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
 * a Splunk_Message. If the result is an associative array, it maps each field
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
 *                 $valuesString = implode(',', $valueOrValues);
 *                 print "  {$key} => [{$valuesString}]\r\n";
 *             }
 *             else
 *             {
 *                 print "  {$key} => {$valueOrValues}\r\n";
 *             }
 *         }
 *         print "}\r\n";
 *     }
 *     else if ($result instanceof Splunk_Message)
 *     {
 *         // Process a message
 *         print "[{$result->type}] {$result->text}\r\n";
 *     }
 * }
 * 
 * @package Splunk
 */
class Splunk_ResultsReader implements IteratorAggregate
{
    private $results;
    
    public function __construct($xmlString)
    {
        $xml = new SimpleXMLElement($xmlString);
        
        $this->results = array();
        if (Splunk_XmlUtil::elementExists($xml->messages))
        {
            foreach ($xml->messages->msg as $msgXml)
            {
                $type = Splunk_XmlUtil::getAttributeValue($msgXml, 'type');
                $text = Splunk_XmlUtil::getTextContent($msgXml);
                
                $this->results[] = new Splunk_Message($type, $text);
            }
        }
        foreach ($xml->result as $resultXml)
        {
            $result = array();
            foreach ($resultXml->field as $fieldXml)
            {
                $k = Splunk_XmlUtil::getAttributeValue($fieldXml, 'k');
                $vs = array();
                foreach ($fieldXml->value as $valueXml)
                {
                    $vs[] = Splunk_XmlUtil::getTextContent($valueXml->text);
                }
                
                $result[$k] = (count($vs) === 1 ? $vs[0] : $vs);
            }
            $this->results[] = $result;
        }
    }
    
    // === IteratorAggregate Methods ===
    
    /**
     * Returns an iterator over the results from this reader.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->results);
    }
}

