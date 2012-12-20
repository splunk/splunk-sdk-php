<?php

require_once '../Splunk.php';
require_once 'settings.php';

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Saved Searches | Splunk SDK for PHP Examples</title>
  <link rel="stylesheet" type="text/css" href="shared/style.css" />
</head>
<body>
<?php require 'shared/navbar.php'; ?>

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

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>Actions</th>
    </tr>
  </thead>
  <?php
  foreach ($savedSearches as $savedSearch)
  {
    echo '<tr><td>';
    echo htmlspecialchars($savedSearch->getName());
    echo '</td><td>';
    echo '<a href="saved_search.php?action=run&id=' . urlencode($savedSearch->getName()) . '">Run</a>';
    echo '<span class="pipe"> | </span>';
    echo '<a href="saved_search.php?action=edit&id=' . urlencode($savedSearch->getName()) . '">Edit</a>';
    echo '<span class="pipe"> | </span>';
    echo '<a href="saved_search.php?action=delete&id=' . urlencode($savedSearch->getName()) . '">Delete</a>';
    echo '</td></tr>';
  }
  ?>
</table>
<a href="saved_search.php?action=create" class="btn">Create New</a>

</body>
</html>