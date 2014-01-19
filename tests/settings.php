<?php

require_once 'settings.default.php';
// (warn if file not found)
include_once 'settings.local.php';
// (ignore if file not found)
if (getenv('HOME'))
    @include_once getenv('HOME') . '/.splunk-phpsdk-test-settings.php';
print "Testing on PHP ".phpversion()."\n";