# Deursoos

Software for the opening of the door.

## Card Ids
The system uses standardised card IDs in the format "[A-Z0-9]+\-[A-Z0-9]+"

## Installation
Installation is separated into three parts: general raspberry pi configuration, installation of the system and configuration of the CCTV

### Installing the raspberry pi
* Install the raspberry pi with either [raspbian](http://www.raspbian.org/) or [moebius](http://moebiuslinux.sourceforge.net/) (preferred) and configure as needed
* Update the system to the latest libraries by running `apt-get update && apt-get upgrade`
* (moebius only) Install and configure ntp to keep the correct time and date

### Installing the software
* Install the card reader libraries: `apt-get install build-essential libusb-dev libusb++-dev libpcsclite-dev libccid pcscd`
* Install the API by downloading it from [acsccid project](http://acsccid.sourceforge.net/). You'll need to compile this from source as there are no pre-built packages available for the armhf architecture
* Install PHP5 `apt-get install php5-dev php5-cli php-pear php5-ldap`
* Install git `apt-get install git`
* Create a directory for the code `mkdir /opt/deursysteem`
* Git clone the repository into that directory
* Compile the system by running `make` and `make install`.
* Disable the pn533 and nfc modules by copying the included config file `cp blacklist-nfc.conf /etc/modprobe.d/blacklist-nfc.conf`
* Configure the system to start `/opt/deursysteem/scan &` on boot by adding it to `/etc/rc.local`
* Configure the system to run `reprogram_door` every day to restore the configuration of the teensy door opener
* Configure te system to run `/opt/deursysteem/reset_system` every day to prevent losing the connection to the scanner

### Installing the CCTV
* Install motion and the ssh filesystem `apt-get install motion sshfs`
* Enable the motion daemon by editing `/etc/default/motion`
* Copy the motion configuration file (`motion.conf`) to `/etc/motion/motion.conf`
* Create a ssh-key pair and push this to deursysteem@camerastore.i.bolkhuis.nl to grant your device access to the camerastore
* Configure a sshfs network mount to store the files in  `sshfs#deursysteem@camerastore.i.bolkhuis.nl:  /home/deursysteem/camerastore/   fuse    auto,_netdev,port=22,user,uid=X,gid=Y,umask=0022,nonempty`. Use the uid of the motion user (`id -u motion`) and gid of the motion group (`id -g motion`) in place of X and Y.
