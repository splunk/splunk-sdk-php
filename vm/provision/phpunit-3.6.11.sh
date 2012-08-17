#!/bin/bash

sudo pear config-set auto_discover 1
sudo pear channel-discover pear.phpunit.de
echo "Downloading PHPUnit... (this can take 30 seconds or more to start)"
sudo pear install phpunit/PHPUnit-3.6.11

