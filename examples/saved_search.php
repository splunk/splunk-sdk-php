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
  // Get the specific saved search
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
    
    // Perform create
    $service->getSavedSearches()->create($name, array(
      'search' => $search,
    ));
    
    // Redirect to list of all saved searches
    header('Location: list_saved_searches.php');
    exit;
  }
  else if ($action === 'edit')
  {
    // Perform edit
    $savedSearch->update(array(
      'search' => $search,
    ));
    
    // Redirect to list of all saved searches
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
    // Redirect to list of all saved searches
    header('Location: list_saved_searches.php');
    exit;
  }
  else if ($action === 'run')
  {
    // Redirect to the manual search page
    $search = $savedSearch['search'];
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
    // NOTE: This really should be a POST action instead of a GET one,
    //       but this example is more straightforward with it as a GET.
    
    // Perform the delete
    $savedSearch->delete();
    
    // Redirect to list of all saved searches
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
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Saved Search | Splunk PHP SDK Examples</title>
  <style>
    input { margin-bottom: .5em; }
  </style>
</head>
<body>

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
  Search: <input type="text" name="search"
    value="<?php echo htmlspecialchars($search) ?>"
    placeholder="index=_internal | top sourcetype"/><br/>
  <br/>
  <input type="submit" value="Save"/> | <a href="list_saved_searches.php">Cancel</a>
</form>

</body>
</html>
