<?php

require_once '../Splunk.php';
require_once 'settings.php';

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Saved Searches | Splunk PHP SDK Examples</title>
  <style>
    table { border-collapse: collapse; }
    table, th, td { border: 1px solid black; }
    th, td { padding: 5px; }
    th { text-align: left; }
  </style>
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

<table>
  <tr>
    <th>Name</th>
    <th>Actions</th>
  </tr>
  <?php
  foreach ($savedSearches as $savedSearch)
  {
    echo '<tr><td>';
    echo htmlspecialchars($savedSearch->getName());
    echo '</td><td>';
    echo '<a href="saved_search.php?action=run&id=' . urlencode($savedSearch->getName()) . '">Run</a>';
    echo ' | ';
    echo '<a href="saved_search.php?action=edit&id=' . urlencode($savedSearch->getName()) . '">Edit</a>';
    echo ' | ';
    echo '<a href="saved_search.php?action=delete&id=' . urlencode($savedSearch->getName()) . '">Delete</a>';
    echo '</td></tr>';
  }
  ?>
</table>
<br/>
<a href="saved_search.php?action=create">Create New</a>

</body>
</html>