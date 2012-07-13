<?php

require_once '../Splunk.php';
require_once 'settings.php';

$id = array_key_exists('id', $_GET) ? $_GET['id'] : '';

$service = new Splunk_Service($SplunkExamples_connectArguments);
// (NOTE: Can throw HTTP 401 if bad credentials)
$service->login();

// Get the specific saved search
Splunk_Namespace::createUser(NULL, NULL);
$savedSearch = $service->getSavedSearches()->get(
  $id,
  Splunk_Namespace::createUser(NULL, NULL)     // all owners, all apps
);

$search = $savedSearch['search'];

// Redirect to the manual search page
header('Location: search.php?search=' . urlencode('search ' . $search));
