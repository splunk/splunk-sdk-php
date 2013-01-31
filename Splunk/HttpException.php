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
 * Thrown when an HTTP request fails due to a non 2xx status code.
 * 
 * @package Splunk
 */
class Splunk_HttpException extends Splunk_IOException
{
    private $response;
    
    // === Init ===
    
    /** @internal */
    public function __construct($response)
    {
        $detail = Splunk_HttpException::parseFirstMessageFrom($response);
        
        $message = "HTTP {$response->status} {$response->reason}";
        if ($detail != NULL)
            $message .= ' -- ' . $detail;
        
        $this->response = $response;
        parent::__construct($message);
    }
    
    /** Parses an HTTP response. */
    private static function parseFirstMessageFrom($response)
    {
        if ($response->body == '')
            return NULL;
        
        return Splunk_XmlUtil::getTextContentAtXpath(
            new SimpleXMLElement($response->body),
            '/response/messages/msg');
    }
    
    // === Accessors ===
    
    /**
     * Gets an HTTP response.
     *
     * @return Splunk_HttpResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}
