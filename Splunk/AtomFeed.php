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
 * Contains utilities for parsing Atom feeds received from the Splunk REST API.
 * 
 * @package Splunk
 * @internal
 */
class Splunk_AtomFeed
{
    /** Name of the 's' namespace in Splunk Atom feeds. */
    const NS_S = 'http://dev.splunk.com/ns/rest';
    
    /**
     * Parses and returns the value inside the specified XML element.
     * 
     * @param SimpleXMLElement $containerXml
     * @return mixed
     */
    public static function parseValueInside($containerXml)
    {
        $dictValue = $containerXml->children(Splunk_AtomFeed::NS_S)->dict;
        $listValue = $containerXml->children(Splunk_AtomFeed::NS_S)->list;
        
        if (Splunk_XmlUtil::elementExists($dictValue))
        {
            return Splunk_AtomFeed::parseDict($dictValue);
        }
        else if (Splunk_XmlUtil::elementExists($listValue))
        {
            return Splunk_AtomFeed::parseList($listValue);
        }
        else // value is scalar
        {
            return Splunk_XmlUtil::getTextContent($containerXml);
        }
    }
    
    /*
     * Example of $dictXml:
     * 
     * <s:dict>
     *     <s:key name="k1">v1</s:key>
     *     <s:key name="k2">v2</s:key>
     * </s:dict>
     */
    private static function parseDict($dictXml)
    {
        $dict = array();
        foreach ($dictXml->children(Splunk_AtomFeed::NS_S)->key as $keyXml)
        {
            $key = Splunk_XmlUtil::getAttributeValue($keyXml, 'name');
            $value = Splunk_AtomFeed::parseValueInside($keyXml);
            
            $dict[$key] = $value;
        }
        return $dict;
    }
    
    /*
     * Example of $listXml:
     * 
     * <s:list>
     *     <s:item>e1</s:item>
     *     <s:item>e2</s:item>
     * </s:list>
     */
    private static function parseList($listXml)
    {
        $list = array();
        foreach ($listXml->children(Splunk_AtomFeed::NS_S)->item as $itemXml)
            $list[] = Splunk_AtomFeed::parseValueInside($itemXml);
        return $list;
    }
}