#!/bin/bash

# PHP 5.2.11 / PEAR 1.8.0

cd /tmp
wget http://museum.php.net/php5/php-5.2.11.tar.gz
tar xzf php-5.2.11.tar.gz
cd php-5.2.11/
./configure --with-openssl --with-curl=/usr
make
sudo make install
sudo cp php.ini-recommended /usr/local/lib/php.ini
