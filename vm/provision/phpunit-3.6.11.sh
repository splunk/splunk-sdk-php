#!/bin/bash

sudo pear config-set auto_discover 1
sudo pear channel-discover pear.phpunit.de

# Manually download (lowest supported) dependencies of PHPUnit to work around
# bugs in PEAR.
# 
# More information:
# * https://github.com/myplanetdigital/vagrant-ariadne/issues/77
# * https://pear.php.net/bugs/bug.php?id=19650
echo "Downloading PHPUnit dependencies... (this can take 30 seconds or more to start)"
sudo pear install phpunit/File_Iterator-1.3.2
echo "Downloading PHPUnit dependencies... (this can take 30 seconds or more to start)"
sudo pear install phpunit/Text_Template-1.1.2
echo "Downloading PHPUnit dependencies... (this can take 30 seconds or more to start)"
sudo pear install phpunit/PHP_Timer-1.0.1
echo "Downloading PHPUnit dependencies... (this can take 30 seconds or more to start)"
sudo pear install phpunit/PHPUnit_MockObject-1.1.0
echo "Downloading PHPUnit dependencies... (this can take 30 seconds or more to start)"
sudo pear install phpunit/PHP_TokenStream-1.1.0
echo "Downloading PHPUnit dependencies... (this can take 30 seconds or more to start)"
sudo pear install phpunit/PHP_CodeCoverage-1.1.0

echo "Downloading PHPUnit... (this can take 30 seconds or more to start)"
sudo pear install phpunit/PHPUnit-3.6.11

