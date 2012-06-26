## Requirements

* PHPUnit 3.6
* Xdebug 2.2.0 (for code coverage)

## Running the Tests

Navigate to the root directory containing `Splunk.php`, then run:

    phpunit tests

To generate a code coverage report, run:

    phpunit --coverage-html coverage tests
    open coverage/Splunk.html
