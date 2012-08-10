#!/bin/bash

# PHP 5.3.8 / PEAR x.y.z

cd /tmp
wget -O php-5.3.8.tar.gz http://us2.php.net/get/php-5.3.8.tar.gz/from/us.php.net/mirror
tar xzf php-5.3.8.tar.gz
cd php-5.3.8/
./configure --with-openssl
make
sudo make install
sudo cp php.ini-development /usr/local/lib/php.ini
