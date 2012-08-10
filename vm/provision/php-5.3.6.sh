#!/bin/bash

# PHP 5.3.6 / PEAR x.y.z

cd /tmp
wget http://museum.php.net/php5/php-5.3.6.tar.gz
tar xzf php-5.3.6.tar.gz
cd php-5.3.6/
./configure --with-openssl
make
sudo make install
sudo cp php.ini-development /usr/local/lib/php.ini
