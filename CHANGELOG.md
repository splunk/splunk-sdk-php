# Splunk SDK for PHP Changelog

<<<<<<< HEAD
## 1.0.0

* No bugs found during beta period.
=======
## 1.0.1 (release)

* The `Job` class has been updated for use with Splunk 6.
>>>>>>> release/1.0.1

## 0.8.0 (beta)

* The `Entity::reload` method has been renamed to `refresh`.
* Added convenience methods to `Service`:
    * `search`
    * `oneshotSearch`

## 0.1.1 (preview)

* Improve HTTPS reliability in PHP 5.2.11 - 5.3.6.
    * Streaming support for large result sets is no longer available for this
      range of PHP versions. Please upgrade to PHP 5.3.7+ if you require this.

## 0.1.0 (preview)

* Initial PHP SDK release
    * Run search jobs and extract data
    * Manage search jobs
    * Log events to indexes
