#!/bin/bash

# PHP 5.4.9 / PEAR x.y.z

cd /tmp
wget -O php-5.4.9.tar.gz http://us.php.net/get/php-5.4.9.tar.gz/from/us1.php.net/mirror
tar xzf php-5.4.9.tar.gz
cd php-5.4.9/
./configure --with-openssl
make
sudo make install
sudo cp php.ini-development /usr/local/lib/php.ini
