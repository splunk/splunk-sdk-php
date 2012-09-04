#!/bin/bash

cd /tmp
wget -O splunk-4.3.2-123586-linux-2.6-intel.deb 'http://www.splunk.com/page/download_track?file=/4.3.2/splunk/linux/splunk-4.3.2-123586-linux-2.6-intel.deb&ac=&wget=true&name=wget&typed=releases'
sudo dpkg -i splunk-4.3.2-123586-linux-2.6-intel.deb

# Start Splunk
sudo /opt/splunk/bin/splunk --accept-license start

# Change Splunk admin password to "weak"
# (NOTE: Splunk must already be running for this command to work.)
sudo /opt/splunk/bin/splunk edit user admin -password weak -role admin -auth admin:changeme
