# Deursoos

Software for the opening of the door.

## Card Ids
The system uses standardised card IDs in the format "[A-Z0-9]+\-[A-Z0-9]+"

## Installation
Installation is separated into three parts: general raspberry pi configuration, installation of the system and configuration of the CCTV

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

### Installing the CCTV
* Install motion and the ssh filesystem `apt-get install motion sshfs`
* Enable the motion daemon by editing `/etc/default/motion`
* Copy the motion configuration file (`motion.conf`) to `/etc/motion/motion.conf`
* Create a ssh-key pair and push this to deursysteem@camerastore.i.bolkhuis.nl to grant your device access to the camerastore
* Configure a sshfs network mount to store the files in  `sshfs#deursysteem@camerastore.i.bolkhuis.nl:  /home/deursysteem/camerastore/   fuse    auto,_netdev,port=22,user,uid=X,gid=Y,umask=0022,nonempty,allow_other`. Use the uid of the motion user (`id -u motion`) and gid of the motion group (`id -g motion`) in place of X and Y.
* The VPS *must* be configured to destroy all files after 28 days for legal reasons. Usually this is done through a cron job which executes `find /home/deursysteem/ -name '*.jpg' -mtime +28 -exec rm -f {} \;`
