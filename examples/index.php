<?php

require_once '../Splunk.php';
require_once 'settings.php';

$username = $SplunkExamples_connectArguments['username'];
$password = $SplunkExamples_connectArguments['password'];
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
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Search | Splunk PHP SDK Examples</title>
  <style>
    table { border-collapse: collapse; border: 1px solid black; }
    table, th, td { border: 1px solid black; }
    th, td { padding: 5px; }
    
    .box {
      font-family: Helvetica, Arial, sans-serif;
      font-size: 90%;
      margin-bottom: 10px;
    }
    .warning-box {
      background: yellow;
    }
    .error-box {
      background: red; color: white;
    }
  </style>
</head>
<body>

<?php if ($usingDefaultCredentials): ?>
  <table class="warning-box box">
    <tr><td>
      It doesn't look like you have configured the PHP examples with
      your server's username and password.<br/>
      <br/>
      Please copy the
      <strong>settings.default.php</strong> file in this directory to
      <strong>settings.local.php</strong> and edit the settings appropriately.
    </td></tr>
  </table>
<?php endif; ?>

<?php if ($loginFailed): ?>
  <table class="error-box box">
    <tr><td>
      Unable to login to your Splunk server.<br/>
      <br/>
      Reason: <code><?php echo htmlspecialchars($loginFailReason); ?></code><br/>
      <br/>
      None of the examples will work until login is successful.
    </td></tr>
  </table>
<?php endif; ?>

<h2>Actions</h2>
<ul>
  <!-- (arranged in order from most useful to least useful) -->
  <li><a href="search.php">Search</a></li>
  <li><a href="list_saved_searches.php">List Saved Searches</a></li>
  <li><a href="list_jobs.php">List Jobs</a></li>
</ul>

</body>
</html>