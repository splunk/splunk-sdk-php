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
}