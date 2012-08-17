#!/bin/bash

# PHP 5.3.7 / PEAR x.y.z

cd /tmp
wget -O php-5.3.7.tar.gz http://us2.php.net/get/php-5.3.7.tar.gz/from/us.php.net/mirror
tar xzf php-5.3.7.tar.gz
cd php-5.3.7/
./configure --with-openssl
make
sudo make install
sudo cp php.ini-development /usr/local/lib/php.ini
