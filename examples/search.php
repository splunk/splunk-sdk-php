<?php

require_once '../Splunk.php';
require_once 'settings.php';

$search = array_key_exists('search', $_GET) ? $_GET['search'] : '';

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Search | Splunk SDK for PHP Examples</title>
  <link rel="stylesheet" type="text/css" href="shared/style.css" />
</head>
<body>
<?php require 'shared/navbar.php'; ?>

<h2>Search</h2>
<form class="form-search" method="get" action="">
  <input type="text" name="search" class="input-medium search-query search-field"
    placeholder="search index=_internal | top sourcetype"
    value="<?php echo htmlspecialchars($search); ?>"/>
  <input type="submit" value="Search" class="btn"/>
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
      $job->refresh();
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
  <table class="table table-condensed table-striped">
    <?php
    $anyRows = FALSE;
    $columnNames = NULL;
    foreach ($results as $result)
    {
      if ($result instanceof Splunk_ResultsFieldOrder)
      {
        $columnNames = $result->getFieldNames();
        
        echo '<thead><tr>';
        foreach ($columnNames as $columnName)
          echo '<th>' . htmlspecialchars($columnName) . '</th>';
        echo '</tr></thead>';
        echo "\n";
      }
      else if ($result instanceof Splunk_ResultsMessage)
      {
        $messages[] = $result;
      }
      else if (is_array($result))
      {
        $anyRows = TRUE;
        
        // (We should have received information about the field ordering prior
        //  to receiving any rows.)
        assert ($columnNames !== NULL);
        
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
  <?php if (!$anyRows && (count($messages) === 0)): ?>
    <p>No results.</p>
  <?php endif; ?>
<?php endif; ?>

</body>
</html>