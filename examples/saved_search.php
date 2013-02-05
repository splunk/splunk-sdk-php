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
  $savedSearch = $service->getSavedSearches()->get(
    $id,
    Splunk_Namespace::createUser(NULL, NULL)     // all owners, all apps
  );
}

if ($method === 'POST')
{
  $search = $_POST['search'];
  
  if ($action === 'create')
  {
    $name = $_POST['name'];
    
    $service->getSavedSearches()->create($name, array(
      'search' => $search,
    ));
    
    header('Location: list_saved_searches.php');
    exit;
  }
  else if ($action === 'edit')
  {
    $savedSearch->update(array(
      'search' => $search,
    ));
    
    header('Location: list_saved_searches.php');
    exit;
  }
  else
  {
    die('Unrecognized action.');
  }
}
else if ($method === 'GET')
{
  if ($action === 'help')
  {
    header('Location: list_saved_searches.php');
    exit;
  }
  else if ($action === 'run')
  {
    $search = $savedSearch['search'];
    
    /*
     * Here, a search job is being created manually, based on the
     * search expression stored in the saved search.
     * 
     * In a real application it would be easier (and more accurate) to
     * call $savedSearch->dispatch() to automatically create an asynchronous
     * search job with the correct settings.
     */
    header('Location: search.php?search=' . urlencode('search ' . $search));
    exit;
  }
  else if ($action === 'create')
  {
    $search = '';
    
    // (continue)
  }
  else if ($action === 'edit')
  {
    $search = $savedSearch['search'];
    
    // (continue)
  }
  else if ($action === 'delete')
  {
    $savedSearch->delete();
    
    header('Location: list_saved_searches.php');
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
  <title>Saved Search | Splunk SDK for PHP Examples</title>
  <link rel="stylesheet" type="text/css" href="shared/style.css" />
  <style>
    input { margin-bottom: .5em; }
  </style>
</head>
<body>
<?php require 'shared/navbar.php'; ?>

<?php if ($action === 'edit'): ?>
  <h2>Edit Saved Search: <?php echo htmlspecialchars($savedSearch->getName()); ?></h2>
<?php elseif ($action === 'create'): ?>
  <h2>Create Saved Search</h2>
<?php endif; ?>

<form action="" method="post">
  <input type="hidden" name="action" value="<?php echo htmlspecialchars($action) ?>"/>
  <input type="hidden" name="id" value="<?php echo htmlspecialchars($id) ?>"/>
  <?php if ($action === 'create'): ?>
    Name: <input type="text" name="name" value="" placeholder="Untitled"/><br/>
  <?php endif; ?>
  Search: <input type="text" name="search" class="search-field"
    value="<?php echo htmlspecialchars($search) ?>"
    placeholder="index=_internal | top sourcetype"/><br/>
  <input type="submit" value="Save" class="btn btn-primary"/>
  <a href="list_saved_searches.php" class="btn">Cancel</a>
</form>

</body>
</html>
