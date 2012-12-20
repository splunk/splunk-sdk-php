<?php

require_once '../Splunk.php';
require_once 'settings.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = array_key_exists('action', $_REQUEST) ? $_REQUEST['action'] : 'help';
$id = array_key_exists('id', $_REQUEST) ? $_REQUEST['id'] : '';

$service = new Splunk_Service($SplunkExamples_connectArguments);
// (NOTE: Can throw HTTP 401 if bad credentials)
$service->login();

if ($id !== '')
{
  $job = $service->getJobs()->get(
    $id,
    Splunk_Namespace::createUser(NULL, NULL)     // all owners, all apps
  );
}

if ($method === 'GET')
{
  if ($action === 'help')
  {
    header('Location: list_jobs.php');
    exit;
  }
  else if ($action === 'view')
  {
    // (continue)
  }
  else if ($action === 'pause')
  {
    $job->pause();
    
    header('Location: list_jobs.php');
    exit;
  }
  else if ($action === 'unpause')
  {
    $job->unpause();
    
    header('Location: list_jobs.php');
    exit;
  }
  else if ($action === 'finalize')
  {
    $job->finalize();
    
    header('Location: list_jobs.php');
    exit;
  }
  else if ($action === 'delete')
  {
    $job->delete();
    
    header('Location: list_jobs.php');
    exit;
  }
  else
  {
    die('Unrecognized action.');
  }
}
else
{
  die('Unrecognized request method.');
}

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Job | Splunk SDK for PHP Examples</title>
  <link rel="stylesheet" type="text/css" href="shared/style.css" />
  <style>
    #job-meta {
      margin-bottom: 1em;
    }
  </style>
</head>
<body>
<?php require 'shared/navbar.php'; ?>

<h2>View Job</h2>

<table id="job-meta" class="table">
  <tr>
    <th>SID</th>
    <td><?php echo htmlspecialchars($job->getName()); ?></td>
  </tr>
  <tr>
    <th>Search</th>
    <td><?php echo htmlspecialchars($job->getSearch()); ?></td>
  </tr>
</table>
<table class="table table-condensed table-striped">
  <?php foreach ($job->getContent() as $k => $v): ?>
    <tr>
      <th><?php echo htmlspecialchars($k); ?></th>
      <td><?php echo htmlspecialchars(json_encode($v)); ?></td>
    </tr>
  <?php endforeach; ?>
</table>
<a href="list_jobs.php" class="btn">OK</a>

</body>
</html>
