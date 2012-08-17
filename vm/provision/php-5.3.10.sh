#!/bin/bash

# PHP 5.3.10 / PEAR 1.9.4

cd /tmp
wget -O php-5.3.10.tar.gz http://us.php.net/get/php-5.3.10.tar.gz/from/this/mirror
tar xzf php-5.3.10.tar.gz
cd php-5.3.10/
./configure --with-openssl
make
sudo make install
sudo cp php.ini-development /usr/local/lib/php.ini
