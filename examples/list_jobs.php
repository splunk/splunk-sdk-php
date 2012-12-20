<?php

require_once '../Splunk.php';
require_once 'settings.php';

function getJobStatus($job)
{
  if ($job['isPaused'] === '1')
    return 'Paused';
  if ($job['isFailed'] === '1')
    return 'Failed';
  if ($job['isFinalized'] === '1')
    return 'Finalized';
  if ($job['isDone'] === '1')
    return 'Done';
  else
    return sprintf('Running (%d%%)', (int) ($job->getProgress() * 100));
}

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Jobs | Splunk SDK for PHP Examples</title>
  <link rel="stylesheet" type="text/css" href="shared/style.css" />
</head>
<body>
<?php require 'shared/navbar.php'; ?>

<h2>Jobs</h2>
<?php

$service = new Splunk_Service($SplunkExamples_connectArguments);
// (NOTE: Can throw HTTP 401 if bad credentials)
$service->login();

// Get all jobs
$jobs = $service->getJobs()->items(array(
  'namespace' => Splunk_Namespace::createUser(NULL, NULL),     // all owners, all apps
));

?>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Search Expression</th>
      <th>Owner</th>
      <th>App</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <?php
  foreach ($jobs as $job)
  {
    echo '<tr><td>';
    echo htmlspecialchars($job->getSearch());
    echo '</td><td>';
    echo htmlspecialchars($job->getNamespace()->getOwner());
    echo '</td><td>';
    echo htmlspecialchars($job->getNamespace()->getApp());
    echo '</td><td>';
    echo htmlspecialchars(getJobStatus($job));
    echo '</td><td>';
    echo '<a href="job.php?action=view&id=' . urlencode($job->getName()) . '">View</a>';
    if ($job['isDone'] !== '1' &&
        $job['isFinalized'] !== '1')
    {
      echo '<span class="pipe"> | </span>';
      if ($job['isPaused'] !== '1')
        echo '<a href="job.php?action=pause&id=' . urlencode($job->getName()) . '">Pause</a>';
      else
        echo '<a href="job.php?action=unpause&id=' . urlencode($job->getName()) . '">Unpause</a>';
      echo '<span class="pipe"> | </span>';
      echo '<a href="job.php?action=finalize&id=' . urlencode($job->getName()) . '">Finalize</a>';
    }
    echo '<span class="pipe"> | </span>';
    echo '<a href="job.php?action=delete&id=' . urlencode($job->getName()) . '">Delete</a>';
    echo '</td></tr>';
  }
  ?>
</table>
<a href="search.php" class="btn">Create New</a>

</body>
</html>