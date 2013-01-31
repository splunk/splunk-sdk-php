<?php

require_once '../Splunk.php';
require_once 'settings.php';

$username = array_key_exists('username', $SplunkExamples_connectArguments)
    ? $SplunkExamples_connectArguments['username'] : 'admin';
$password = array_key_exists('username', $SplunkExamples_connectArguments)
    ? $SplunkExamples_connectArguments['password'] : 'changeme';
$usingDefaultCredentials = ($username === 'admin' && $password === 'changeme');

$loginFailed = FALSE;
$loginFailReason = NULL;
try
{
    $service = new Splunk_Service($SplunkExamples_connectArguments);
    // (NOTE: Can throw HTTP 401 if bad credentials)
    $service->login();
}
catch (Exception $e)
{
    $loginFailed = TRUE;
    $loginFailReason = $e->getMessage();
}

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Splunk SDK for PHP Examples</title>
  <link rel="stylesheet" type="text/css" href="shared/style.css" />
</head>
<body>
<?php require 'shared/navbar.php'; ?>

<?php if ($usingDefaultCredentials): ?>
  <div class="alert">
    <h4 class="alert-heading">Examples Unconfigured.</h4>
    You are using the default name and password. Please copy the
    <strong>settings.default.php</strong> file in this directory to
    <strong>settings.local.php</strong> and edit it appropriately.
  </div>
<?php endif; ?>

<?php if ($loginFailed): ?>
  <div class="alert alert-error">
    <h4 class="alert-heading">Login Failed.</h4>
    Reason: <code><?php echo htmlspecialchars($loginFailReason); ?></code>
  </div>
<?php endif; ?>

<h2>Examples</h2>
<ul>
  <!-- (arranged in order from most useful to least useful) -->
  <li><a href="search.php">Search</a></li>
  <li><a href="list_saved_searches.php">List Saved Searches</a></li>
  <li><a href="list_jobs.php">List Jobs</a></li>
</ul>

</body>
</html>