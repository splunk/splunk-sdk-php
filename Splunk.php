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
 * Intercepts failed attempts to load classes of the form 'Splunk_*'
 * and automatically includes the appropriate PHP file for the class.
 */
function Splunk_autoload($className)
{
    if (substr_compare($className, 'Splunk_', 0, strlen('Splunk_')) != 0)
        return false;
    $file = str_replace('_', '/', $className);
    return include dirname(__FILE__) . "/$file.php";
}

spl_autoload_register('Splunk_autoload');

if (version_compare(PHP_VERSION, '5.2.11') < 0)
    die('The Splunk SDK for PHP requires PHP 5.2.11 or later.');
