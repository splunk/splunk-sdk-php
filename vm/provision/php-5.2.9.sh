#!/bin/bash

# PHP 5.2.9 / PEAR 1.7.2

cd /tmp
wget http://museum.php.net/php5/php-5.2.9.tar.gz
tar xzf php-5.2.9.tar.gz
cd php-5.2.9/
./configure --with-openssl --with-curl=/usr
make
sudo make install
sudo cp php.ini-recommended /usr/local/lib/php.ini
