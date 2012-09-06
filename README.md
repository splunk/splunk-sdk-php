# The Splunk Software Development Kit for PHP (Preview)

This SDK makes it easy for PHP applications to communicate with and control a Splunk server. Such applications may start searches, display results, and perform administration tasks.

### About Splunk

Splunk is a search engine and analytic environment that uses a distributed
map-reduce architecture to efficiently index, search and process large 
time-varying data sets.

The Splunk product is popular with system administrators for aggregation and
monitoring of IT machine data, security, compliance and a wide variety of other
scenarios that share a requirement to efficiently index, search, analyze and
generate real-time notifications from large volumes of time series data.

## License

The Splunk Software Development Kit for PHP is licensed under the Apache
License 2.0. Details can be found in the file [LICENSE].

[LICENSE]: https://github.com/splunk/splunk-sdk-php/blob/master/LICENSE

## Requirements

The SDK requires PHP 5.2.11+ with the SimpleXML extension.
PHP 5.3.7+ is highly recommended.

OpenSSL support for PHP is required to access Splunk over `https://` URLs.

Tested PHP versions:

* PHP 5.4.x
  * **PHP 5.4.5 - OK.** Latest PHP as of 2012-08-01.
* PHP 5.3.x
  * **PHP 5.3.10 - OK.** Default PHP for Mac OS X 10.7 (Lion).
  * **PHP 5.3.7 - OK.**
  * **PHP 5.3.6 - Mostly OK&dagger;.**
  * **PHP 5.3.3 - Mostly OK&dagger;.**
* PHP 5.2.x
  * **PHP 5.2.17 - Mostly OK&dagger;.** Last version of PHP 5.2.x.
  * **PHP 5.2.11 - Mostly OK&dagger;.** Earliest PHP 5.2.x version known to work.
  * PHP 5.2.10 - Broken due to [bug 48182].
  * PHP 5.2.9 - Broken due to [bug 45092].
    Earliest recommended PHP for PHPUnit 3.6.
  * PHP 5.2.7 - Recalled due to security flaw.
    Earliest PHP supported by PHPUnit 3.6.

&dagger; Suffers from [bug 54137] which interferes with HTTPS communication, 
especially to a Splunk server on localhost. If you see the error message
`SSL: Connection reset by peer`, you are probably triggering this bug.
A possible workaround is to run your PHP script on a different server than
the Splunk indexer server, although this does not always resolve the issue.
The SDK team is developing a better workaround for the next release.

[bug 45092]: https://bugs.php.net/bug.php?id=45092
[bug 48182]: https://bugs.php.net/bug.php?id=48182
[bug 54137]: https://bugs.php.net/bug.php?id=54137

## Getting Started

If you haven't already installed Splunk, download it here: 
[http://www.splunk.com/download](http://www.splunk.com/download). 

For more about installing and running Splunk and system requirements, see
[Installing & Running Splunk](http://dev.splunk.com/view/SP-CAAADRV). 

Get a copy of the Splunk PHP SDK from [GitHub](https://github.com/) by cloning
into the repository with git:

> git clone https://github.com/splunk/splunk-sdk-php.git


## Layout of the SDK

<table>

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

</table>

### Examples

To run the examples, you will need to install a web server locally that supports PHP.

* On Mac OS X, [MAMP] is recommended.
* On Windows, [XAMPP] is recommended.
* On Linux, install Apache and PHP from your package manager.

[MAMP]: http://www.mamp.info/en/index.html
[XAMPP]: http://www.apachefriends.org/en/xampp.html

Then, move the entire `splunk-sdk-php` directory (containing `examples` and
`Splunk.php`) inside your web server's document root.

* For MAMP, the document root is located at: `/Applications/MAMP/htdocs/`
* For XAMPP, the document root is located at: `C:\xampp\htdocs\`

Finally, copy `settings.default.php` in the `examples` directory to
`settings.local.php` and update it with your Splunk server's credentials.

Then you should be able to access the SDK examples via a URL similar to:

	http://localhost:8888/splunk-sdk-php/examples/index.php

(You may need to alter the port of the URL to another value such as `8080` or
 `80`, depending on your web server.)

### Unit Tests

Requirements:

* [PHPUnit](http://www.phpunit.de/) 3.6+
* [Xdebug](http://xdebug.org/) 2.0.5+ (for code coverage)
* Copy `settings.default.php` in the `tests` directory to
  `settings.local.php` and update it with the credentials of a Splunk server
  reserved for testing.

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

To generate the API documentation, run:

    phpdoc -d Splunk -t apidocs


## Quickstart

The PHP SDK provides an object-oriented interface for interacting with a Splunk server.

To use the SDK, first import `Splunk.php`. This will give you access to all `Splunk_*` classes.

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
// NOTE: The expression must begin with 'search '
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
        foreach ($result as $field => $valueOrValues)
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

So, for example, to fetch a list of saved searches or search jobs:

```
$savedSearches = $service->getSavedSearches()->items();  // in the default namespace
$jobs = $service->getJobs()->items();                    // in the default namespace
```

You can also fetch a particular entity in a collection by name:

```
$topSourcetypesSearch = $service->getSavedSearches()->get('Top five sourcetypes');
```

### Namespaces

An entity has a *namespace*, which corresponds to entity's access permissions.

All functions that fetch an individual entity or a list of entities can be
provided a namespace argument. (If you omit this argument, the `Splunk_Service`'s
default namespace will be used.)

So, for example, to get the list of saved searches owned by user `admin` in the
`search` app:

```
$savedSearches = $service->getSavedSearches()->items(array(
	'namespace' => Splunk_Namespace::createUser('admin', 'search'),
));
```

Or to get an individual entity in a namespace:

```
$topSourcetypesSearch = $service->getSavedSearches()->get(
	'Top five sourcetypes',
	Splunk_Namespace::createApp('search'));
```

If you typically access lots of objects in the same namespace, it is possible to
pass a default namespace to the `Splunk_Service` constructor. This allows you to
avoid passing an explicit namespace on every call to `get()` or `items()`.

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

For reference, the types of namespaces are:

* The default namespace - `Splunk_Namespace::createDefault()`
	* Contains entities owned by the authenticated user in that user's default app.
	* For example, if you logged in to `Splunk_Service` as the user `admin` and
	  the default application for that user was `search`, the default namespace
	  would include all entities belonging to `admin` in app `search`.
	* This namespace is used if no explicit namespace is provided.
* A user namespace - `Splunk_Namespace::createUser($owner, $app)`
	* Contains entities owned by a particular user in a particular app.
* An app namespace - `Splunk_Namespace::createApp($app)`
	* Contains entities in a particular app that have no owner .
* A global namespace - `Splunk_Namespace::createGlobal($app)`
	* Contains entities in a particular app that have no owner and that are
	  accessible to all apps.
* The system namespace - `Splunk_Namespace::createSystem()`
	* Contains entities in the `system` app.

## Common Tasks

### Running a Search with a Search Expression

#### Oneshot Searches

For searches that run quickly with a small number of results, it is easiest to
create a *oneshot* search:

```
// NOTE: The expression must begin with 'search '
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
        foreach ($result as $field => $valueOrValues)
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

For searches that return a large number of results whose progress you don't need
to monitor, it is easiest to create a *blocking* search job:

```
// NOTE: The expression must begin with 'search '
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
// NOTE: The expression must begin with 'search '
$searchExpression = 'search index=_internal | head 10000';

// Create normal search job
$job = $service->getJobs()->create($searchExpression);

// Wait for job to complete and get results
while (!$job->isDone())
{
	printf("Progress: %03.1f%%\r\n", $job->getProgress() * 100);
	usleep(0.5 * 1000000);
	$job->reload();
}
$results = $job->getResults();

// Process results
...
```

### Running a Saved Search

You can create a normal search job based on a saved search by calling
`dispatch()` on the `Splunk_SavedSearch` object.

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

* http://dev.splunk.com

Reference documentation for the Splunk REST API:

* http://docs.splunk.com/Documentation/Splunk/latest/RESTAPI

Overview of Splunk and links to additional product information:

* http://docs.splunk.com/Documentation/Splunk/latest/User/SplunkOverview

## Community

Stay connected with other developers building on Splunk.

<table>

<tr>
<td><em>Email</em></td>
<td>devinfo@splunk.com</td>
</tr>

<tr>
<td><em>Issues</em>
<td><span>https://github.com/splunk/splunk-sdk-php/issues/</span></td>
</tr>

<tr>
<td><em>Answers</em>
<td><span>http://splunk-base.splunk.com/tags/php/</span></td>
</tr>

<tr>
<td><em>Blog</em>
<td><span>http://blogs.splunk.com/dev/</span></td>
</tr>

<tr>
<td><em>Twitter</em>
<td>@splunkdev</td>
</tr>

</table>

### How to contribute

If you would like to contribute to the SDK, please follow one of the links 
provided below.

* [Individual contributions](http://dev.splunk.com/goto/individualcontributions)
* [Company contributions](http://dev.splunk.com/view/companycontributions/SP-CAAAEDR)

### Support

1. You will be granted support if you or your company are already covered under an existing maintenance/support agreement. Send an email to support@splunk.com and please include the SDK you are referring to in the subject. 
2. If you are not covered under an existing maintenance/support agreement you can find help through the broader community at:
<br>Splunk answers - http://splunk-base.splunk.com/answers/ Specific tags (SDK, java, python, javascript) are available to identify your questions
<br>Splunk dev google group - http://groups.google.com/group/splunkdev
3. Splunk will NOT provide support for SDKs if the core library (this is the code in the Splunk directory) has been modified. If you modify an SDK and want support, you can find help through the broader community and Splunk answers (see above). We also want to know about why you modified the core library. You can send feedback to: devinfo@splunk.com

### Contact Us

You can reach the Dev Platform team at devinfo@splunk.com
