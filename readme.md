# Deursoos

Software for the opening of the door.

## Card Ids
The system uses standardised card IDs in the format "[A-Z0-9]+\-[A-Z0-9]+"

## Installation
Installation is separated into two parts: general raspberry pi configuration and installation of the system

### Installing the raspberry pi
* Install the raspberry pi with either [raspbian](http://www.raspbian.org/) (recommended) or [moebius](http://moebiuslinux.sourceforge.net/) (preferred) and configure as needed
* Update the system to the latest libraries by running `apt-get update && apt-get upgrade`
* (moebius only) Install and configure ntp to keep the correct time and date

### Installing the software
* Install the card reader libraries: `apt-get install build-essential libusb-dev libusb++-dev libpcsclite-dev libccid pcscd libacsccid1`
* Install PHP (last tested on PHP 7.4) `apt-get install php-dev php-cli php-pear php-ldap`
* Install git and composer `apt-get install git composer`
* Create a directory for the code `mkdir /opt/deursysteem`
* Git clone the repository into that directory
* Go to the cloned directory and install dependencies with composer `composer install`
* Compile the system by running `make` and `make install`.
* Disable the pn533 and nfc modules by copying the included config file `cp blacklist-libnfc.conf /etc/modprobe.d/blacklist-nfc.conf`
* Start and enable the service: `systemctl enable --now deursoos`
* (maybe not needed) Configure the system to run `reprogram_door` every day to restore the configuration of the teensy door opener
