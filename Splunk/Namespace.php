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
 * Represents a namespace. Every Splunk object belongs to a namespace.
 * 
 * @package Splunk
 */
// NOTE: Ideally the static constructors for this class wouldn't have any
//       prefix (like 'create') or suffix. Unfortunately both 'default' and
//       'global' are considered keywords in PHP, preventing their use
//       as static constructor names.
class Splunk_Namespace
{
    private $owner;
    private $app;
    private $sharing;
    
    // === Init ===
    
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
        Splunk_Namespace::assertArgumentCountEquals(0, func_num_args());
        
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
        Splunk_Namespace::assertArgumentCountEquals(2, func_num_args());
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
        Splunk_Namespace::assertArgumentCountEquals(1, func_num_args());
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
        Splunk_Namespace::assertArgumentCountEquals(1, func_num_args());
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
        Splunk_Namespace::assertArgumentCountEquals(0, func_num_args());
        
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
        Splunk_Namespace::assertArgumentCountEquals(3, func_num_args());
        if (!in_array($sharing, array('user', 'app', 'global', 'system')))
            throw new InvalidArgumentException('Invalid sharing.');
        if ($owner === NULL || $owner === '' || $owner === '-')
            throw new InvalidArgumentException('Invalid owner.');
        if ($app === NULL || $app === '' || $app === '-')
            throw new InvalidArgumentException('Invalid app.');
        
        return new Splunk_Namespace($owner, $app, $sharing);
    }
    
    // === Accessors ===
    
    /**
     * Returns the path prefix to use when referencing objects in this namespace.
     */
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
    
    // === Utility ===
    
    // (Explicitly check the argument count because many creation function 
    //  names do not make the required number of arguments clear and PHP
    //  does not check under certain circumstances.)
    private static function assertArgumentCountEquals($expected, $actual)
    {
        if ($actual !== $expected)
            throw new InvalidArgumentException(
                "Expected exactly ${expected} arguments.");
    }
}