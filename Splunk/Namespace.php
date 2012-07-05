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
class Splunk_Namespace
{
    private $user;
    private $app;
    private $sharing;
    
    // === Init ===
    
    private function __construct($user, $app, $sharing)
    {
        $this->user = $user;
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
    public static function default_()
    {
        static $default_ = NULL;
        if ($default_ === NULL)
            $default_ = new Splunk_Namespace(NULL, NULL, 'default');
        return $default_;
    }
    
    /**
     * Creates the namespace containing objects associated with the specified
     * user and application.
     * 
     * @param string|NULL $user     name of a Splunk user (ex: "admin"),
     *                              or NULL to specify all users.
     * @param string|NULL $app      name of a Splunk app (ex: "search"),
     *                              or NULL to specify all apps.
     * @return Splunk_Namespace
     */
    public static function user($user, $app)
    {
        if ($user === '' || $user === 'nobody' || $user === '-')
            throw new InvalidArgumentException('Invalid user.');
        if ($app === '' || $app === 'system' || $app === '-')
            throw new InvalidArgumentException('Invalid app.');
        if ($user === NULL)
            $user = '-';
        if ($app === NULL)
            $app = '-';
        return new Splunk_Namespace($user, $app, 'user');
    }
    
    /**
     * Creates the non-global namespace containing objects associated with the
     * specified application.
     * 
     * @param string|NULL $app      name of a Splunk app (ex: "search"),
     *                              or NULL to specify all apps.
     * @return Splunk_Namespace
     */
    public static function app($app)
    {
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
    public static function global_($app)
    {
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
    public static function system()
    {
        static $system = NULL;
        if ($system === NULL)
            $system = new Splunk_Namespace('nobody', 'system', 'system');
        return $system;
    }
    
    /**
     * Creates a non-wildcarded namespace with the specified properties.
     * 
     * @param string $user          name of a Splunk user (ex: "admin").
     * @param string $app           name of a Splunk app (ex: "search").
     * @param string $sharing       one of {'user', 'app', 'global', 'system'}.
     * @see user()
     */
    public static function exact($user, $app, $sharing)
    {
        if (!in_array($sharing, array('user', 'app', 'global', 'system')))
            throw new InvalidArgumentException('Invalid sharing.');
        if ($user === '' || $user === '-')
            throw new InvalidArgumentException('Invalid user.');
        if ($app === '' || $app === '-')
            throw new InvalidArgumentException('Invalid app.');
        
        return new Splunk_Namespace($user, $app, $sharing);
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
                return '/servicesNS/' . urlencode($this->user) . '/' . urlencode($this->app) . '/';
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
        return ($this->user !== '-') && ($this->app !== '-');
    }
}