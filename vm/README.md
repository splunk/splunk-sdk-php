# VM Testing Automation

This is a system for spinning up virtual machines (VMs) with particular
versions of PHP and Splunk, wiring them together, and running the unit tests on
such VM-pairs.

Most VM management operations are delegated to [Vagrant], which in turn depends
on [VirtualBox] for actually running VMs.

## Requirements

* [VirtualBox]
* [Vagrant]

[VirtualBox]: https://www.virtualbox.org/
[Vagrant]: http://vagrantup.com/

## Commands

#### List Splunk Versions Supported by VM Testing

```
ls provision | grep splunk-
```

#### List PHP Versions Supported by VM Testing

```
ls provision | grep php-
```

#### Run Tests on a Provisioned VM-Pair

<span style="color: red">
**WARNING:** Make sure you save your work before running `vmtest` or any
`vagrant` commands. The underlying VirtualBox tool is known to cause kernel
panics on occasion.
</span>

For PHP 5.3.10 running against Splunk 4.3.2, you would use the command:

```
./vmtest 5.3.10 4.3.2
```

For more advanced options, including running against a Splunk version on the
host OS, see the documentation comment in the `vmtest` script.

#### Manually Provision a PHP VM

```
vagrant up php-5.3.10
```

#### Manually Provision a Splunk VM

```
vagrant up splunk-4.3.2
```

## Extending the Supported Splunk & PHP Versions

### Adding a new Splunk version

* Create a new provisioning script:
    * Select an existing provisioning script that has a nearby version number.
    * Test to make sure it still works: `vagrant up splunk-4.3.2.sh`
    * Duplicate it: `cp provision/splunk-4.3.2.sh provision/splunk-5.0.1.sh`
    * Go to the main Splunk website.
      Go to the download page for the 32-bit .deb package.
    * On the download page, look for a way to get a wget-compatible URL.
      Update the duplicated script with this new URL.

* Test the new provisioning script.
    * `vagrant up splunk-5.0.1`
    * If there is a problem, fix the provisioning script,
      run `vagrant destroy splunk-5.0.1` and try the previous step again.


### Adding a new PHP version

The process is similar to that for adding a new Splunk version.

Download links for PHP releases can be found at:

* [Current Releases](http://us.php.net/downloads.php)
* [Historical Releases](http://us.php.net/releases/index.php)

Since PHP is compiled from source, it is a lot more likely that things will
go wrong or outright break. In this case you may need to:

* debug the `Vagrantfile` which controls how PHP's dependencies are built, and/or
* debug the partially provisioned machine by connecting to it via SSH:
  `vagrant ssh php-5.3.10`.
    * The `splunk-sdk-php/vm` directory will be mounted in the VM at `/vagrant`.
    * The `splunk-sdk-php` directory will be mounted in the VM at `/vagrant_data`.

## Design

VirtualBox was chosen as the virtualization solution because it is free, which
makes it widely available to contributors everywhere.

Vagrant is a nice abstraction for manipulating VirtualBox VMs.
