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
 * Represents a statement of which fields will be returned in a results stream,
 * and what their relative ordering is.
 * 
 * @package Splunk
 * @see Splunk_ResultsReader
 */
class Splunk_ResultsFieldOrder
{
    private $fieldNames;
    
    /** @internal */
    public function __construct($fieldNames)
    {
        $this->fieldNames = $fieldNames;
    }
    
    /**
     * Gets an ordered list of field names that will be returned in the results stream.
     *
     * @return array    A ordered list of the field names that will be returned
     *                  in the results stream.
     */
    public function getFieldNames()
    {
        return $this->fieldNames;
    }
}
