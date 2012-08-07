#!/bin/bash

# Install placeholders for DNS aliases
echo ""                          | sudo tee -a /etc/hosts > /dev/null
echo "# Splunk SDK Tests"        | sudo tee -a /etc/hosts > /dev/null
echo "127.0.0.1 splunk.vm.local" | sudo tee -a /etc/hosts > /dev/null

# Install test settings
ln -s /vagrant/.splunk-phpsdk-test-settings.php /home/vagrant/.splunk-phpsdk-test-settings.php
