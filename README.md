# The Splunk Software Development Kit for PHP

#### Version 1.0

The Splunk Software Development Kit (SDK) for PHP makes it easy for PHP 
applications to communicate with and control a Splunk server. Using the APIs 
exposed by the SDK, applications can start searches, display results, and 
perform administrative tasks.

### About Splunk

Splunk is a search engine and analytic environment that uses a distributed
map-reduce architecture to efficiently index, search and process large 
time-varying data sets.

The Splunk product is popular with system administrators for aggregation and
monitoring of IT machine data, security, compliance, and a wide variety of 
other scenarios that share a requirement to efficiently index, search, analyze, 
and generate real-time notifications from large volumes of time series data.

## License

The Splunk Software Development Kit for PHP is licensed under the Apache
License 2.0. Details can be found in the file [LICENSE].

[LICENSE]: https://github.com/splunk/splunk-sdk-php/blob/master/LICENSE

## Requirements

The SDK requires PHP 5.2.11 or later with the SimpleXML extension.
PHP 5.3.7 or later is highly recommended.

OpenSSL support for PHP is required to access Splunk over `https://` URLs.

If you're using PHP 5.3.6 or earlier, the cURL extension is required as well.
Under this configuration, the SDK will not support streaming large results
when accessing Splunk over `https://` URLs.

## Getting Started

If you haven't already installed Splunk, download it here: 
[http://www.splunk.com/download](http://www.splunk.com/download). 

For more about installing and running Splunk plus system requirements, see
[Installing & Running Splunk](http://dev.splunk.com/view/SP-CAAADRV). 

Get a copy of the Splunk SDK for PHP from [GitHub](https://github.com/) by 
cloning the repository with git:

    git clone https://github.com/splunk/splunk-sdk-php.git


## Layout of the SDK

<table>

<tr>
<th>Name</th>
<th>Description</th>
</tr>

<tr>
<td>examples</td>
<td>Examples demonstrating various SDK features</td>
<tr>

<tr>
<td>Splunk</td>
<td>Source for the SDK classes</td>
<tr>

<tr>
<td>Splunk.php</td>
<td>Source for the SDK class autoloader</td>
<tr>

<tr>
<td>tests</td>
<td>Source for unit tests</td>
<tr>

<tr>
<td>vm</td>
<td>Source for virtual machine testing automation</td>
<tr>

</table>

### Examples

To run the examples, you will need to install a web server locally that 
supports PHP.

* On Mac OS X, [MAMP] is recommended.
* On Windows, [XAMPP] is recommended. You will additionally need to configure 
    PHP to support OpenSSL:
    * Open `C:\xampp\php\php.ini`.
    * Find the line `;extension=php_openssl.dll` and remove the leading 
        semicolon (`;`).
	* Use the XAMPP Control Panel to restart Apache, if it was already
        running.
* On Linux, install Apache and PHP from your package manager.

[MAMP]: http://www.mamp.info/en/index.html
[XAMPP]: http://www.apachefriends.org/en/xampp.html

Then, move the entire `splunk-sdk-php` directory (containing `examples` and
`Splunk.php`) inside your web server's document root.

* For MAMP, the document root is located at: `/Applications/MAMP/htdocs/`
* For XAMPP, the document root is located at: `C:\xampp\htdocs\`

Finally, duplicate `settings.default.php` in the `examples` directory, rename 
the duplicate to `settings.local.php`, and then edit the file. Uncomment the 
`'port'`, `'username'`, and `'password'` fields and update the file with your 
Splunk server's credentials.

You should then be able to access the SDK examples via a URL similar to:

	http://localhost:8888/splunk-sdk-php/examples/index.php

(You may need to alter the port of the URL to another value such as `8080` or
 `80`, depending on your web server.)

If you see an error similar to:

```
Login Failed.
Reason: fopen(https://localhost:8089/services/auth/login): failed to open stream: Invalid argument
```

this means that you need to enable support for OpenSSL in your PHP
configuration (`php.ini`).

### Unit Tests

Requirements:

* [PHPUnit](http://www.phpunit.de/) 3.6 or later
* [Xdebug](http://xdebug.org/) 2.0.5 or later (for code coverage)
* Duplicate `settings.default.php` in the `tests` directory, rename the 
    duplicate to `settings.local.php`, and then edit the file. Uncomment the 
    `'port'`, `'username'`, and `'password'` fields and update the file with 
    the credentials of a Splunk server reserved for testing.

To execute all unit tests, run:

    phpunit tests

To execute only fast unit tests, run:

    phpunit --exclude-group slow tests

To generate a code coverage report, run:

    phpunit --coverage-html coverage tests
    open coverage/Splunk.html

### API Documentation

Requirements:

* [phpDocumentor 2](http://www.phpdoc.org/)
* [GraphViz](http://www.graphviz.org/) (optional)
    * the **dot** command-line tool is used to render the class hierarchy diagram 
        in the documentation

To generate the API documentation, run:

    phpdoc -d Splunk -t apidocs

### Changelog

You can look at the changelog for each version 
[here](https://github.com/splunk/splunk-sdk-php/blob/master/CHANGELOG.md).


## Quickstart

The PHP SDK provides an object-oriented interface for interacting with a 
Splunk server.

To use the SDK, first import `Splunk.php`. This will give you access to all 
`Splunk_*` classes.

```
require_once 'Splunk.php';
```

Then use an instance of `Splunk_Service` to connect to a Splunk server.

```
$service = new Splunk_Service(array(
    'host' => 'localhost',
    'port' => '8089',
    'username' => 'admin',
    'password' => 'changeme',
));
$service->login();
```

Once connected, you can manipulate various entities on the server,
such as saved searches and search jobs.

For example, the following code runs a quick search and prints out the results.

```
// NOTE: The expression must begin with 'search ' or '| '
$searchExpression = 'search index=_internal | head 100 | top sourcetype';

// Create oneshot search and get results
$resultsXmlString = $service->getJobs()->createOneshot($searchExpression);
$results = new Splunk_ResultsReader($resultsXmlString);

// Process results
foreach ($results as $result)
{
    if ($result instanceof Splunk_ResultsFieldOrder)
    {
        // Process the field order
        print "FIELDS: " . implode(',', $result->getFieldNames()) . "\r\n";
    }
    else if ($result instanceof Splunk_ResultsMessage)
    {
        // Process a message
        print "[{$result->getType()}] {$result->getText()}\r\n";
    }
    else if (is_array($result))
    {
        // Process a row
        print "{\r\n";
        foreach ($result as $key => $valueOrValues)
        {
            if (is_array($valueOrValues))
            {
                $values = $valueOrValues;
                $valuesString = implode(',', $values);
                print "  {$key} => [{$valuesString}]\r\n";
            }
            else
            {
                $value = $valueOrValues;
                print "  {$key} => {$value}\r\n";
            }
        }
        print "}\r\n";
    }
    else
    {
        // Ignore unknown result type
    }
}
```

## Core Concepts

### Entities and Collections

An *entity* is an object on a Splunk server. This includes saved searches,
search jobs, indexes, inputs, and many others.

Each type of entity lives inside a *collection*.
Each collection type can be accessed on the `Splunk_Service` object.

So, for example, to retrieve a list of saved searches or search jobs:

```
$savedSearches = $service->getSavedSearches()->items();  // in the default namespace
$jobs = $service->getJobs()->items();                    // in the default namespace
```

You can also retrieve a particular entity in a collection by name:

```
$topSourcetypesSearch = $service->getSavedSearches()->get('Top five sourcetypes');
```

### Namespaces

An entity has a *namespace*, which corresponds to the entity's access 
permissions.

All functions that retrieve an individual entity or a list of entities can be
provided a namespace argument. (If you omit this argument, the 
`Splunk_Service`'s default namespace will be used.)

So, for example, to retrieve the list of saved searches owned by user `admin` 
in the `search` app:

```
$savedSearches = $service->getSavedSearches()->items(array(
	'namespace' => Splunk_Namespace::createUser('admin', 'search'),
));
```

Or, to retrieve an individual entity in a namespace:

```
$topSourcetypesSearch = $service->getSavedSearches()->get(
	'Top five sourcetypes',
	Splunk_Namespace::createApp('search'));
```

If you typically access many objects in the same namespace, it is possible to
pass a default namespace to the `Splunk_Service` constructor. This allows you 
to avoid passing an explicit namespace on every call to `get()` or `items()`.

```
$service = new Splunk_Service(array(
    ...
    'namespace' => Splunk_Namespace::createUser('admin', 'search'),
));
$service->login();

$jobs = $service->getJobs()->items();			// in the admin/search namespace
$indexes = $service->getIndexes()->items(array(	// in the system namespace
	'namespace' => Splunk_Namespace::createSystem(),
));
```
The types of namespaces are described here:

| Type     | Name                                         | Description        |
| -------- | -------------------------------------------- | ------------------ |
| Default  | `Splunk_Namespace::createDefault()`          | Contains entities owned by the authenticated user in that user's default app. For example, if you logged in to `Splunk_Service` as the user `admin` and the default application for that user was `search`, the default namespace would include all entities belonging to `admin` in app `search`. This namespace is used if no explicit namespace is provided. |
| User     | `Splunk_Namespace::createUser($owner, $app)` | Contains entities owned by a particular user in a particular app. |
| App      | `Splunk_Namespace::createApp($app)`          | Contains entities in a particular app that have no owner. |
| Global   | `Splunk_Namespace::createGlobal($app)`       | Contains entities in a particular app that have no owner and that are accessible to all apps. |
| System   | `Splunk_Namespace::createSyste ()`           | Contains entities in the `system` app. |


## Common Tasks

### Running a Search with a Search Expression

#### Oneshot Searches

For searches that run quickly with a small number of results, it is easiest to
create a *oneshot* search:

```
// NOTE: The expression must begin with 'search ' or '| '
$searchExpression = 'search index=_internal | head 100 | top sourcetype';

// Create oneshot search and get results
$resultsXmlString = $service->getJobs()->createOneshot($searchExpression);
$results = new Splunk_ResultsReader($resultsXmlString);

// Process results
foreach ($results as $result)
{
    if ($result instanceof Splunk_ResultsFieldOrder)
    {
        // Process the field order
        // ...
    }
    else if ($result instanceof Splunk_ResultsMessage)
    {
        // Process a message
        print "[{$result->getType()}] {$result->getText()}\r\n";
    }
	else if (is_array($result))
    {
        // Process a row
        foreach ($result as $key => $valueOrValues)
        {
            // ...
        }
    }
    else
    {
        // Ignore unknown result type
    }
}
```

A oneshot search blocks until it completes and returns all results immediately.

#### Blocking Search Jobs

For searches that return a large number of results whose progress you don't 
need to monitor, it is easiest to create a *blocking* search job:

```
// NOTE: The expression must begin with 'search ' or '| '
$searchExpression = 'search index=_internal | head 1000';

// Create blocking search job and get results
$job = $service->getJobs()->create($searchExpression, array(
	'exec_mode' => 'blocking',
));
$results = $job->getResults();

// Process results
...
```

Blocking search jobs wait until all results are available.

#### Asynchronous (Normal) Search Jobs

For most searches that could potentially return a large number of results, you
should create an *asynchronous* (normal) search job.

Asynchronous jobs allow you to monitor their progress while they are running.

```
// NOTE: The expression must begin with 'search ' or '| '
$searchExpression = 'search index=_internal | head 10000';

// Create normal search job
$job = $service->getJobs()->create($searchExpression);

// Wait for job to complete and get results
while (!$job->isDone())
{
	printf("Progress: %03.1f%%\r\n", $job->getProgress() * 100);
	usleep(0.5 * 1000000);
	$job->refresh();
}
$results = $job->getResults();

// Process results
...
```

### Running a Saved Search

You can create a normal search job based on a saved search by calling the
`dispatch()` method on the `Splunk_SavedSearch` object.

```
$savedSearch = $service->getSavedSearches()->get('Top five sourcetypes');

// Create normal search job based on the saved search
$job = $savedSearch->dispatch();

// Wait for job to complete and get results
...

// Process results
...
```

### Running a Realtime Search

A realtime search must be run as an *asynchronous* search job.
The *blocking* and *oneshot* modes do not work, because a realtime search
never actually completes.

To get results from a realtime search, the `getResultsPreviewPage()` method
must be used instead of the `getResults()` method.

## Resources

You can find anything having to do with developing on Splunk at the Splunk
developer portal:

* For all things developer with Splunk, your main resource is the [Splunk
  Developer Portal](http://dev.splunk.com).

* For conceptual and how-to documentation, see the [Overview of the Splunk SDK
  for PHP](http://dev.splunk.com/view/php-sdk/SP-CAAAEJM).

* For API reference documentation, see the [Splunk SDK for PHP 
  Reference](http://docs.splunk.com/Documentation/PHPSDK)

* For more about the Splunk REST API, see the [REST API 
  Reference](http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI).

* For more about about Splunk in general, see [Splunk>Docs](http://docs.splunk.com/Documentation/Splunk).

* For more about this SDK's repository, see our 
  [GitHub Wiki](https://github.com/splunk/splunk-sdk-php/wiki/).

## Community

Stay connected with other developers building on Splunk.

<table>

<tr>
<td><em>Email</em></td>
<td><a href="mailto:devinfo@splunk.com">devinfo@splunk.com</a></td>
</tr>

<tr>
<td><em>Issues</em>
<td><a href="https://github.com/splunk/splunk-sdk-php/issues/">
https://github.com/splunk/splunk-sdk-php/issues</a></td>
</tr>

<tr>
<td><em>Answers</em>
<td><a href="http://splunk-base.splunk.com/tags/php/">
http://splunk-base.splunk.com/tags/php/</a></td>
</tr>

<tr>
<td><em>Blog</em>
<td><a href="http://blogs.splunk.com/dev/">http://blogs.splunk.com/dev/</a></td>
</tr>

<tr>
<td><em>Twitter</em>
<td><a href="http://twitter.com/splunkdev">@splunkdev</a></td>
</tr>

</table>

### How to contribute

If you would like to contribute to the SDK, please follow one of the links 
provided below.

* [Individual contributions](http://dev.splunk.com/goto/individualcontributions)
* [Company contributions](http://dev.splunk.com/view/companycontributions/SP-CAAAEDR)

### Support

1. You will be granted support if you or your company are already covered 
   under an existing maintenance/support agreement. Visit 
   <http://www.splunk.com/support> and click **Submit a Case** under **Contact
   a Support Engineer**.

2. If you are not covered under an existing maintenance/support agreement, you 
   can find help through the broader community at:
   * [Splunk Answers](http://splunk-base.splunk.com/answers/) (use the **sdk** and 
   **php** tags to identify your questions)
   * [Splunkdev Google Group](http://groups.google.com/group/splunkdev)

3. Splunk will NOT provide support for SDKs if the core library (the 
   code in the **splunk** directory) has been modified. If you modify an SDK
   and want support, you can find help through the broader community and Splunk 
   answers (see above). We would also like to know why you modified the core 
   library&mdash;please send feedback to _devinfo@splunk.com_.
4. File any issues on [GitHub](https://github.com/splunk/splunk-sdk-php/issues).

### Contact Us

You can reach the Dev Platform team at [devinfo@splunk.com](mailto:devinfo@splunk.com).
