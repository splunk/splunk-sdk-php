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
 * Represents a message received in a results stream.
 * 
 * @package Splunk
 * @see Splunk_ResultsReader
 */
class Splunk_ResultsMessage
{
    private $type;
    private $text;
    
    /** @internal */
    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }
    
    /**
     * Gets the type of this message.
     *
     * @return string           The type of this message (ex: 'DEBUG').
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Gets the text of this message.
     *
     * @return string           The text of this message.
     */
    public function getText()
    {
        return $this->text;
    }
}