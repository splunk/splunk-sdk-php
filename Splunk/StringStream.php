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
 * Creates a stream that reads from a string.
 * 
 * @package Splunk
 * @internal
 */
class Splunk_StringStream
{
    /** (Prevent construction.) **/
    private function __construct()
    {
    }
    
    /**
     * @return resource     A stream that reads from the specified byte string.
     */
    public static function create($string)
    {
        $stream = fopen('php://memory', 'rwb');
        Splunk_Util::fwriteall($stream, $string);
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
