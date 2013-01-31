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
 * Internal utility functions for the PHP SDK.
 * 
 * @package Splunk
 * @internal
 */
class Splunk_Util
{
    /**
     * Extracts the value for the specified $key from the specified $dict.
     * 
     * @param array $dict
     * @param mixed $key
     * @param mixed $defaultValue
     * @return array {
     *     [0] => $dict without $key
     *     [1] => $dict[$key] if it exists, or $defaultValue if it does not
     * }
     */
    public static function extractArgument($dict, $key, $defaultValue)
    {
        $value = array_key_exists($key, $dict) ? $dict[$key] : $defaultValue;
        unset($dict[$key]);
        return array($dict, $value);
    }
    
    /**
     * Gets the value for the specified $key from the specified $dict,
     * returning the $defaultValue in the key is not found.
     * 
     * @param array $dict
     * @param mixed $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getArgument($dict, $key, $defaultValue)
    {
        return array_key_exists($key, $dict) ? $dict[$key] : $defaultValue;
    }
    
    /**
     * Writes $data to $stream.
     * 
     * @throws Splunk_IOException   If an I/O error occurs.
     */
    public static function fwriteall($stream, $data)
    {
        while (TRUE)
        {
            $numBytesWritten = fwrite($stream, $data);
            if ($numBytesWritten === FALSE)
            {
                $errorInfo = error_get_last();
                $errmsg = $errorInfo['message'];
                $errno = $errorInfo['type'];
                throw new Splunk_IOException($errmsg, $errno);
            }
            if ($numBytesWritten == strlen($data))
                return;
            $data = substr($data, $numBytesWritten);
        }
    }
    
    /**
     * Reads the entire contents of the specified stream.
     * Throws a Splunk_IOException upon error.
     * 
     * @param resource $stream      A stream.
     * @return string               The contents of the specified stream.
     * @throws Splunk_IOException   If an I/O error occurs.
     */
    public static function stream_get_contents($stream)
    {
        // HACK: Clear the last error
        @trigger_error('');
            
        $oldError = error_get_last();
        $body = @stream_get_contents($stream);
        $newError = error_get_last();
        
        // HACK: Detecting whether stream_get_contents() has failed is not
        //       strightforward because it can either return FALSE or ''.
        //       However '' is also a legal return value in non-error scenarios.
        if ($newError != $oldError)
        {
            $errorInfo = error_get_last();
            $errmsg = $errorInfo['message'];
            $errno = $errorInfo['type'];
            throw new Splunk_IOException($errmsg, $errno);
        }
        
        return $body;
    }
}