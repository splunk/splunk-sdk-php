<?php

// Credentials to connect to your Splunk instance.
$SplunkExamples_connectArguments = array(
    'username' => 'admin',
    'password' => 'changeme',
    
    'host' => 'localhost',
    'port' => 8089,
);

// Enable for better error reporting
if (FALSE)
{
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
}
