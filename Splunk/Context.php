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

/**
 * Allows clients to issue HTTP requests to a Splunk server.
 * 
 * @package Splunk
 */
class Splunk_Context
{
    private $username;
    private $password;
    private $token;
    private $host;
    private $port;
    private $scheme;
    private $namespace;
    private $http;
    
    /**
     * @param array $args {
     *      'username' => (optional) The username to login with. Defaults to "admin".
     *      'password' => (optional) The password to login with. Defaults to "changeme".
     *      'token' => (optional) The authentication token to use. If provided,
     *                 the username and password are ignored and there is no
     *                 need to call login(). In the format "Splunk SESSION_KEY".
     *      'host' => (optional) The hostname of the Splunk server. Defaults to "localhost".
     *      'port' => (optional) The port of the Splunk server. Defaults to 8089.
     *      'scheme' => (optional) The scheme to use: either "http" or "https". Defaults to "https".
     *      'namespace' => (optional) Namespace that all object lookups will occur in by default.
     *                     Defaults to `Splunk_Namespace::createDefault()`.
     *      'http' => (optional) An Http object that will be used for performing HTTP requests.
     *                This is intended for testing only.
     * }
     */
    public function __construct($args=array())
    {
        $args = array_merge(array(
            'username' => 'admin',
            'password' => 'changeme',
            'token' => NULL,
            'host' => 'localhost',
            'port' => 8089,
            'scheme' => 'https',
            'namespace' => Splunk_Namespace::createDefault(),
            'http' => new Splunk_Http(),
        ), $args);
        
        $this->username = $args['username'];
        $this->password = $args['password'];
        $this->token = $args['token'];
        $this->host = $args['host'];
        $this->port = $args['port'];
        $this->scheme = $args['scheme'];
        $this->namespace = $args['namespace'];
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
        
        $sessionKey = Splunk_XmlUtil::getTextContentAtXpath(
            new SimpleXMLElement($response->body),
            '/response/sessionKey');
        
        $this->token = "Splunk {$sessionKey}";
    }
    
    /**
     * Performs an HTTP GET request to the endpoint at the specified path.
     * 
     * @param string $path      relative or absolute URL path.
     * @param array $args       (optional) query parameters, merged with {
     *     'namespace' => (optional) namespace to use, or NULL to use
     *                    this context's default namespace.
     * }
     * @return Splunk_HttpResponse
     * @throws Splunk_HttpException
     * @see Splunk_Http::get()
     */
    public function get($path, $args=array())
    {
        return $this->simpleRequest('get', $path, $args);
    }
    
    /**
     * Performs an HTTP POST request to the endpoint at the specified path.
     * 
     * @param string $path      relative or absolute URL path.
     * @param array $args       (optional) form parameters to send in the request body,
     *                          merged with {
     *     'namespace' => (optional) namespace to use, or NULL to use
     *                    this context's default namespace.
     * }
     * @return Splunk_HttpResponse
     * @throws Splunk_HttpException
     * @see Splunk_Http::post()
     */
    public function post($path, $args=array())
    {
        return $this->simpleRequest('post', $path, $args);
    }
    
    /**
     * Performs an HTTP DELETE request to the endpoint at the specified path.
     * 
     * @param string $path      relative or absolute URL path.
     * @param array $args       (optional) query parameters, merged with {
     *     'namespace' => (optional) namespace to use, or NULL to use
     *                    this context's default namespace.
     * }
     * @return Splunk_HttpResponse
     * @throws Splunk_HttpException
     * @see Splunk_Http::delete()
     */
    public function delete($path, $args=array())
    {
        return $this->simpleRequest('delete', $path, $args);
    }
    
    /**
     * Performs an HTTP request to the endpoint at the specified path.
     * 
     * @param string $method        the HTTP method (ex: 'GET' or 'POST').
     * @param string $path          relative or absolute URL path.
     * @param array $requestHeaders (optional) dictionary of header names and values.
     * @param string $requestBody   (optional) content to send in the request.
     * @param array $args           (optional) query parameters, merged with {
     *     'namespace' => (optional) namespace to use, or NULL to use
     *                    this context's default namespace.
     * }
     * @return Splunk_HttpResponse
     * @throws Splunk_HttpException
     * @see Splunk_Http::request()
     */
    public function request(
        $method, $path, $requestHeaders=array(), $requestBody='', $args=array())
    {
        list($params, $namespace) = 
            Splunk_Util::extractArgument($args, 'namespace', NULL);
        
        $url = $this->url($path, $namespace);
        $fullUrl = (count($params) == 0)
            ? $url
            : $url . '?' . http_build_query($params);
        
        $requestHeaders = array_merge(
            $this->getRequestHeaders(),
            $requestHeaders);
        
        return $this->http->request(
            $method,
            $fullUrl,
            $requestHeaders,
            $requestBody);
    }
    
    private function simpleRequest($method, $path, $args)
    {
        list($params, $namespace) = 
            Splunk_Util::extractArgument($args, 'namespace', NULL);
        
        return $this->http->$method(
            $this->url($path, $namespace),
            $params,
            $this->getRequestHeaders());
    }
    
    /** Returns the standard headers to send on each HTTP request. */
    private function getRequestHeaders()
    {
        return array(
            'Authorization' => $this->token,
        );
    }
    
    // === Accessors ===
    
    /**
     * @return Splunk_Namespace     The default namespace that will be used
     *                              to perform collection and entity operations
     *                              when none is explicitly specified.
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    
    /**
     * @return string   The token used to authenticate HTTP requests
     *                  after logging in.
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @return string   The hostname of the Splunk server.
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * @return string   The port of the Splunk server.
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * @return string   The scheme to use: either "http" or "https".
     */
    public function getScheme()
    {
        return $this->scheme;
    }
    
    // === Utility ===
    
    /**
     * @param string $path                  relative or absolute URL path.
     * @param Splunk_Namespace|NULL $namespace
     * @return string                       absolute URL.
     */
    private function url($path, $namespace=NULL)
    {
        return "{$this->scheme}://{$this->host}:{$this->port}{$this->abspath($path, $namespace)}";
    }
    
    /**
     * @param string $path                  relative or absolute URL path.
     * @param Splunk_Namespace|NULL $namespace
     * @return string                       absolute URL path.
     */
    private function abspath($path, $namespace=NULL)
    {
        if ((strlen($path) >= 1) && ($path[0] == '/'))
            return $path;
        if ($namespace === NULL)
            $namespace = Splunk_Namespace::createDefault();
        
        return $namespace->getPathPrefix() . $path;
    }
}
