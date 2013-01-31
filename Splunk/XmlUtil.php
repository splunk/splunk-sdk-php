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
 * Utilities for manipulating SimpleXMLElement objects.
 * 
 * @package Splunk
 * @internal
 */
class Splunk_XmlUtil
{
    /**
     * Returns whether the specified XML element exists.
     * 
     * @param SimpleXMLElement  $xml
     * @return boolean
     */
    public static function elementExists($xml)
    {
        return $xml->getName() != '';
    }
    
    /**
     * @param SimpleXMLElement  $xml
     * @param string            $attributeName
     * @return string|NULL
     */
    public static function getAttributeValue($xml, $attributeName)
    {
        return (isset($xml->attributes()->$attributeName))
            ? (string) $xml->attributes()->$attributeName
            : NULL;
    }
    
    /**
     * @param SimpleXMLElement  $xml
     * @return string
     */
    public static function getTextContent($xml)
    {
        // HACK: Some versions of PHP 5 can't access the [0] element
        //       of a SimpleXMLElement object properly.
        return (string) $xml;
    }
    
    /**
     * @param SimpleXMLElement  $xml
     * @param string            $xpathExpr
     * @return string|NULL
     */
    public static function getTextContentAtXpath($xml, $xpathExpr)
    {
        $matchingElements = $xml->xpath($xpathExpr);
        return (count($matchingElements) == 0)
            ? NULL
            : Splunk_XmlUtil::getTextContent($matchingElements[0]);
    }
    
    /**
     * Returns true if the specified SimpleXMLElement represents a unique
     * element or false if it represents a collection of elements.
     * 
     * @param SimpleXMLElement  $xml
     * @return bool
     */
    public static function isSingleElement($xml)
    {
        $count = 0;
        foreach ($xml as $item)
        {
            $count++;
            if ($count >= 2)
                return false;
        }
        return ($count == 1);
    }
}
