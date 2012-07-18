<?php

require_once '../Splunk.php';
require_once 'settings.php';

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Saved Searches | Splunk PHP SDK Examples</title>
</head>
<body>

<h2>Saved Searches</h2>
<?php

$service = new Splunk_Service($SplunkExamples_connectArguments);
// (NOTE: Can throw HTTP 401 if bad credentials)
$service->login();

// Get all saved searches
$savedSearches = $service->getSavedSearches()->items(array(
  'namespace' => Splunk_Namespace::createUser(NULL, NULL),     // all owners, all apps
));

?>
<ul>
  <?php
  foreach ($savedSearches as $savedSearch)
  {
    echo '<li><a href="saved_search.php?id=' . urlencode($savedSearch->getName()) . '">';
    echo htmlspecialchars($savedSearch->getName()) . '</li>';
  }
  ?>
</ul>
</body>
</html>