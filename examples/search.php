<?php

require_once '../Splunk.php';
require_once 'settings.php';

$search = array_key_exists('search', $_GET) ? $_GET['search'] : '';

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

<h2>Search</h2>
<form method="get" action="">
  <input type="text" name="search" placeholder="search index=_internal | top sourcetype" size="100"
    value="<?php echo htmlspecialchars($search); ?>"/>
  <input type="submit" value="Search"/>
</form>

<?php if ($search !== ''): ?>
  <h2>Progress</h2>
  <?php
  try
  {
    // Login and start search job
    $service = new Splunk_Service($SplunkExamples_connectArguments);
    // (NOTE: Can throw HTTP 401 if bad credentials)
    $service->login();
    // (NOTE: Can throw HTTP 400 if search command not recognized)
    $job = $service->getJobs()->create($search);
    
    // Print progress of the job as it is running
    echo '<ul>';
    while (!$job->isDone())
    {
      echo '<li>';
      printf("%03.1f%%", $job->getProgress() * 100);
      echo '</li>';
      flush();
      
      usleep(0.5 * 1000000);
      $job->reload();
    }
    echo '<li>Done</li>';
    echo '</ul>';
    
    // (NOTE: Can throw HTTP 400 if search command arguments not recognized)
    $results = $job->getResults();
    $messages = array();
  }
  catch (Exception $e)
  {
    // Generate fake result that contains the exception message
    $results = array();
    $messages = array();
    $messages[] = new Splunk_ResultsMessage('EXCEPTION', $e->getMessage());
  }
  ?>
  <h2>Results</h2>
  <table>
    <?php
    $isFirstRow = TRUE;
    foreach ($results as $result)
    {
      // Skip messages and other non-standard results
      if (!is_array($result))
      {
        if ($result instanceof Splunk_ResultsMessage)
        {
          $messages[] = $result;
        }
        continue;
      }
      
      if ($isFirstRow)
      {
        $columnNames = array_keys($result);
        echo '<tr>';
        foreach ($columnNames as $columnName)
          echo '<th>' . htmlspecialchars($columnName) . '</th>';
        echo '</tr>';
        echo "\n";
        
        $isFirstRow = FALSE;
      }
      
      echo '<tr>';
      foreach ($columnNames as $columnName)
      {
        $cellValue = array_key_exists($columnName, $result) ? $result[$columnName] : NULL;
        echo '<td>';
        if ($cellValue !== NULL)
        {
          if (is_array($cellValue))
          {
            echo '<ul>';
            foreach ($cellValue as $value)
              echo '<li>' . htmlspecialchars($value) . '</li>';
            echo '</ul>';
          }
          else
          {
            echo htmlspecialchars($cellValue);
          }
        }
        echo '</td>';
      }
      echo '</tr>';
      echo "\n";
    }
    ?>
  </table>
  <?php if (count($messages) > 0): ?>
    <ul>
      <?php
      foreach ($messages as $message)
      {
        echo '<li>[' . htmlspecialchars($message->getType()) . '] ';
        echo htmlspecialchars($message->getText()) . '</li>';
      }
      ?>
    </ul>
  <?php endif; ?>
  <?php if ($isFirstRow && (count($messages) === 0)): ?>
    <p>No results.</p>
  <?php endif; ?>
<?php endif; ?>

</body>
</html>