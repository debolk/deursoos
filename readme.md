# Deursoos

Software for the opening of the door.

## Installation
Installation is separated into two parts: general raspberry pi configuration and installation of the system

### Installing the raspberry pi
* Install the raspberry pi with either [raspbian](http://www.raspbian.org/) or [moebius](http://moebiuslinux.sourceforge.net/) (preferred) and configure as needed
* Update the system to the latest libraries by running `apt-get update && apt-get upgrade`
* (moebius only) Install and configure ntp to keep the correct time and date

### Installing the software
* Install the card reader libraries: `apt-get install build-essential libusb-dev libusb++-dev libpcsclite-dev libccid`
* Install PHP5 `apt-get install php5-dev php5-cli php-pear`
* Install git `apt-get install git`
* Create a directory for the code `mkdir /opt/deursysteem`
* Git clone the repository into that directory
* Compile the system by running `make` and `make install`.
* Configure the server to start `/opt/deursysteem/scan` on boot
* Configure the server to run `reprogram_door` every day to restore the configuration of the teensy door opener
