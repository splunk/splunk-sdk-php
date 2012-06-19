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

require_once 'Utils.php';

class Splunk_Context
{
    private $username;
    private $password;
    private $host;
    private $port;
    private $scheme;
    private $http;
    
    private $token;
    
    /**
     * @param array $args (optional) {
     *      'username' => (optional) The username to login with. Defaults to "admin".
     *      'password' => (optional) The password to login with. Defaults to "changeme".
     *      'host' => (optional) The hostname of the Splunk server. Defaults to "localhost".
     *      'port' => (optional) The port of the Splunk server. Defaults to 8089.
     *      'scheme' => (optional) The scheme to use: either "http" or "https". Defaults to "https".
     *      'http' => (optional) An Http object that will be used for performing HTTP requests.
     * }
     */
    public function __construct($args)
    {
        $args = array_merge(array(
            'username' => 'admin',
            'password' => 'changeme',
            'host' => 'localhost',
            'port' => 8089,
            'scheme' => 'https',
            'http' => new Splunk_Http(),
        ), $args);
        
        $this->username = $args['username'];
        $this->password = $args['password'];
        $this->host = $args['host'];
        $this->port = $args['port'];
        $this->scheme = $args['scheme'];
        $this->http = $args['http'];
    }
    
    // === Operations ===
    
    /**
     * Authenticates to the Splunk server.
     */
    public function login()
    {
        $response = $this->http->post($this->url('/services/auth/login'), array(
            'username' => $this->username,
            'password' => $this->password,
        ));
        
        $sessionKey = Splunk_Utils::getTextContentAtXpath(
            new SimpleXMLElement($response['body']),
            '/response/sessionKey');
        
        $this->token = 'Splunk ' . $sessionKey;
    }
    
    // === Accessors ===
    
    /**
     * Returns the token used to authenticate HTTP requests
     * after logging in.
     */
    public function getToken()
    {
        return $this->token;
    }
    
    // === Utility ===
    
    private function url($path)
    {
        return "{$this->scheme}://{$this->host}:{$this->port}{$path}";
    }
}
