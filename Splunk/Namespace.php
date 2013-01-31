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

// NOTE: Ideally the static constructors for this class wouldn't have any
//       prefix (like 'create') or suffix. Unfortunately both 'default' and
//       'global' are considered keywords in PHP, preventing their use
//       as static constructor names.
/**
 * Represents a namespace. Every Splunk object belongs to a namespace.
 * 
 * @package Splunk
 */
class Splunk_Namespace
{
    private $owner;
    private $app;
    private $sharing;
    
    // === Init ===
    
    /** Constructs a new namespace with the specified parameters. */
    private function __construct($owner, $app, $sharing)
    {
        $this->owner = $owner;
        $this->app = $app;
        $this->sharing = $sharing;
    }
    
    /**
     * Creates the default namespace.
     * 
     * Objects in the default namespace correspond to the authenticated user
     * and their default Splunk application.
     * 
     * @return Splunk_Namespace
     */
    public static function createDefault()
    {
        $numArgs = func_num_args(); // must be own line for PHP < 5.3.0
        Splunk_Namespace::ensureArgumentCountEquals(0, $numArgs);
        
        static $defaultNamespace = NULL;
        if ($defaultNamespace === NULL)
            $defaultNamespace = new Splunk_Namespace(NULL, NULL, 'default');
        return $defaultNamespace;
    }
    
    /**
     * Creates the namespace containing objects associated with the specified
     * user and application.
     * 
     * @param string|NULL $owner    name of a Splunk user (ex: "admin"),
     *                              or NULL to specify all users.
     * @param string|NULL $app      name of a Splunk app (ex: "search"),
     *                              or NULL to specify all apps.
     * @return Splunk_Namespace
     */
    public static function createUser($owner, $app)
    {
        $numArgs = func_num_args(); // must be own line for PHP < 5.3.0
        Splunk_Namespace::ensureArgumentCountEquals(2, $numArgs);
        
        if ($owner === '' || $owner === 'nobody' || $owner === '-')
            throw new InvalidArgumentException('Invalid owner.');
        if ($app === '' || $app === 'system' || $app === '-')
            throw new InvalidArgumentException('Invalid app.');
        if ($owner === NULL)
            $owner = '-';
        if ($app === NULL)
            $app = '-';
        return new Splunk_Namespace($owner, $app, 'user');
    }
    
    /**
     * Creates the non-global namespace containing objects associated with the
     * specified application.
     * 
     * @param string|NULL $app      name of a Splunk app (ex: "search"),
     *                              or NULL to specify all apps.
     * @return Splunk_Namespace
     */
    public static function createApp($app)
    {
        $numArgs = func_num_args(); // must be own line for PHP < 5.3.0
        Splunk_Namespace::ensureArgumentCountEquals(1, $numArgs);
        
        if ($app === '' || $app === 'system' || $app === '-')
            throw new InvalidArgumentException('Invalid app.');
        if ($app === NULL)
            $app = '-';
        return new Splunk_Namespace('nobody', $app, 'app');
    }
    
    /**
     * Creates the global namespace containing objects associated with the
     * specified application.
     * 
     * @param string|NULL $app      name of a Splunk app (ex: "search"),
     *                              or NULL to specify all apps.
     * @return Splunk_Namespace
     */
    public static function createGlobal($app)
    {
        $numArgs = func_num_args(); // must be own line for PHP < 5.3.0
        Splunk_Namespace::ensureArgumentCountEquals(1, $numArgs);
        
        if ($app === '' || $app === 'system' || $app === '-')
            throw new InvalidArgumentException('Invalid app.');
        if ($app === NULL)
            $app = '-';
        return new Splunk_Namespace('nobody', $app, 'global');
    }
    
    /**
     * Creates the system namespace.
     * 
     * Objects in the system namespace ship with Splunk.
     * 
     * @return Splunk_Namespace
     */
    public static function createSystem()
    {
        $numArgs = func_num_args(); // must be own line for PHP < 5.3.0
        Splunk_Namespace::ensureArgumentCountEquals(0, $numArgs);
        
        static $system = NULL;
        if ($system === NULL)
            $system = new Splunk_Namespace('nobody', 'system', 'system');
        return $system;
    }
    
    /**
     * Creates a non-wildcarded namespace with the specified properties.
     * 
     * @param string $owner         name of a Splunk user (ex: "admin").
     * @param string $app           name of a Splunk app (ex: "search").
     * @param string $sharing       one of {'user', 'app', 'global', 'system'}.
     * @see user()
     */
    public static function createExact($owner, $app, $sharing)
    {
        $numArgs = func_num_args(); // must be own line for PHP < 5.3.0
        Splunk_Namespace::ensureArgumentCountEquals(3, $numArgs);
        
        if (!in_array($sharing, array('user', 'app', 'global', 'system')))
            throw new InvalidArgumentException('Invalid sharing.');
        if ($owner === NULL || $owner === '' || $owner === '-')
            throw new InvalidArgumentException('Invalid owner.');
        if ($app === NULL || $app === '' || $app === '-')
            throw new InvalidArgumentException('Invalid app.');
        
        return new Splunk_Namespace($owner, $app, $sharing);
    }
    
    // === Accessors ===
    
    /** Returns the path prefix to use when referencing objects in this 
            namespace. */
    public function getPathPrefix()
    {
        switch ($this->sharing)
        {
            case 'default':
                return '/services/';
            case 'user':
            case 'app':
            case 'global':
            case 'system':
                return '/servicesNS/' . urlencode($this->owner) . '/' . urlencode($this->app) . '/';
            default:
                throw new Exception("Invalid sharing mode '{$this->sharing}'.");
        }
    }
    
    /**
     * Returns whether this is an exact (non-wildcarded) namespace.
     * 
     * Within an exact namespace, no two objects can have the same name.
     */
    public function isExact()
    {
        return ($this->owner !== '-') && ($this->app !== '-');
    }
    
    /**
     * Returns the user who owns objects in this namespace.
     * 
     * This operation is only defined for exact namespaces.
     */
    public function getOwner()
    {
        $this->ensureExact();
        return $this->owner;
    }
    
    /**
     * Returns the app associated with objects in this namespace.
     * 
     * This operation is only defined for exact namespaces.
     */
    public function getApp()
    {
        $this->ensureExact();
        return $this->app;
    }
    
    /**
     * Returns the sharing mode of this namespace.
     * 
     * This operation is only defined for exact namespaces.
     */
    public function getSharing()
    {
        $this->ensureExact();
        return $this->sharing;
    }
    
    // === Utility ===
    
    // (Explicitly check the argument count because many creation function 
    //  names do not make the required number of arguments clear and PHP
    //  does not check under certain circumstances.)
    /** Throws an exception if the number of arguments is not what was 
            expected. */
    private static function ensureArgumentCountEquals($expected, $actual)
    {
        if ($actual !== $expected)
            throw new InvalidArgumentException(
                "Expected exactly ${expected} arguments.");
    }
    
    /** Throws an exception if this namespace is not an exact (non-wildcarded) 
	        namespace. */
    private function ensureExact()
    {
        if (!$this->isExact())
            throw new Splunk_UnsupportedOperationException(
                'This operation is supported only for exact namespaces.');
    }
}