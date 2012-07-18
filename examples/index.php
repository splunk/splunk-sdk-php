<?php

require_once '../Splunk.php';
require_once 'settings.php';

$username = $SplunkExamples_connectArguments['username'];
$password = $SplunkExamples_connectArguments['password'];
$usingDefaultCredentials = ($username === 'admin' && $password === 'changeme');

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Search | Splunk PHP SDK Examples</title>
  <style>
    table { border-collapse: collapse; }
    table, th, td { border: 1px solid black; }
    th, td { padding: 5px; }
  </style>
</head>
<body>

<?php if ($usingDefaultCredentials): ?>
  <table border="1" style="background: yellow;">
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

<h2>Actions</h2>
<ul>
  <li><a href="list_saved_searches.php">List Saved Searches</a></li>
  <li><a href="search.php">Search</a></li>
</ul>

</body>
</html>