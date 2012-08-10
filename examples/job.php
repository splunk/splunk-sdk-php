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
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Job | Splunk PHP SDK Examples</title>
  <style>
    table { border-collapse: collapse; }
    table, th, td { border: 1px solid black; }
    th, td { padding: 5px; }
    th { text-align: left; }
  </style>
</head>
<body>

<h2>View Job</h2>

<table>
  <tr>
    <th>SID</th>
    <td><?php echo htmlspecialchars($job->getName()); ?></td>
  </tr>
  <tr>
    <th>Search</th>
    <td><?php echo htmlspecialchars($job->getSearch()); ?></td>
  </tr>
</table>
<br/>
<table>
  <?php foreach ($job->getContent() as $k => $v): ?>
    <tr>
      <th><?php echo htmlspecialchars($k); ?></th>
      <td><?php echo htmlspecialchars(json_encode($v)); ?></td>
    </tr>
  <?php endforeach; ?>
</table>
<br/>
<a href="list_jobs.php">OK</a>

</body>
</html>
