#!/bin/bash

# PHP 5.3.5 / PEAR x.y.z

cd /tmp
wget http://museum.php.net/php5/php-5.3.5.tar.gz
tar xzf php-5.3.5.tar.gz
cd php-5.3.5/
./configure --with-openssl --with-curl=/usr
make
sudo make install
sudo cp php.ini-development /usr/local/lib/php.ini
