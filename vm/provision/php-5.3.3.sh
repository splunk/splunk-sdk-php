#!/bin/bash

# PHP 5.3.3 / PEAR x.y.z

cd /tmp
wget http://museum.php.net/php5/php-5.3.3.tar.gz
tar xzf php-5.3.3.tar.gz
cd php-5.3.3/
./configure --with-openssl --with-curl=/usr
make
sudo make install
sudo cp php.ini-development /usr/local/lib/php.ini
