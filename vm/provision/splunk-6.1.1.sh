#!/bin/bash

cd /tmp
wget -O splunk-6.1.1-207789-linux-2.6-intel.deb 'http://www.splunk.com/page/download_track?file=6.1.1/splunk/linux/splunk-6.1.1-207789-linux-2.6-intel.deb&ac=&wget=true&name=wget&typed=release'
sudo dpkg -i splunk-6.1.1-207789-linux-2.6-intel.deb

# Start Splunk
sudo /opt/splunk/bin/splunk --accept-license start

# Change Splunk admin password to "weak"
# (NOTE: Splunk must already be running for this command to work.)
sudo /opt/splunk/bin/splunk edit user admin -password weak -role admin -auth admin:changeme