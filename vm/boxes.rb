def _extract_version(filename)
  filename.scan(/-(.*)\.sh$/)[0][0]
end

def _set_next_box_id(box_id)
  @@_current_box_id = box_id
end

def _create_ip_for_box(boxname)
  BOX_NAME_2_IP[boxname] = '192.168.50.' + @@_current_box_id.to_s
  @@_current_box_id += 1
end


# Locate supported version combinations
PHP_VERSIONS = Dir['provision/php-*.sh'].map {|x| _extract_version(x) }
SPLUNK_VERSIONS = Dir['provision/splunk-*.sh'].map {|x| _extract_version(x) }

# Compute IPs for all VMs
BOX_NAME_2_IP = {}

_set_next_box_id(100)
PHP_VERSIONS.each do |php_version|
  _create_ip_for_box('php-' + php_version)
end

_set_next_box_id(200)
SPLUNK_VERSIONS.each do |splunk_version|
  _create_ip_for_box('splunk-' + splunk_version)
end
