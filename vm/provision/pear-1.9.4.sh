#!/bin/bash

cd /tmp
wget http://download.pear.php.net/package/PEAR-1.9.4.tgz
tar xzf PEAR-1.9.4.tgz

cd PEAR-1.9.4/
wget http://pear.php.net/go-pear.phar
# HACK: Temporarily move /dev/tty aside so that go-pear.phar reads from stdin
sudo mv /dev/tty /dev/tty-
# (press return to accept the default file layout)
# (type "y" and press return to alter php.ini)
# (press return to confirm php.ini changes)
echo -n "
y

" | sudo php go-pear.phar
sudo mv /dev/tty- /dev/tty

# Add PEAR's bin directory to the PATH
echo '' >> ~/.bashrc
echo '# PEAR' >> ~/.bashrc
echo 'export PATH=/home/vagrant/pear/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
