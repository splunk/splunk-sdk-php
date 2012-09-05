#!/bin/bash

# PHP 5.2.17 / PEAR 1.9.1

cd /tmp
wget http://museum.php.net/php5/php-5.2.17.tar.gz
tar xzf php-5.2.17.tar.gz
cd php-5.2.17/
./configure --with-openssl --with-curl=/usr
make
sudo make install
sudo cp php.ini-recommended /usr/local/lib/php.ini
