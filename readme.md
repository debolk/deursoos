# Deursoos

Software for the opening of the door.

## Technology
The system consists of a few files working in tandem. `scan` is a bash-script that is started on boot. It starts `read.php` which connects to the scanner and waits for a valid card to be presented. 

When a valid card is presented, this script prints a standarized id to STDOUT. This output is read by `scan` and fed to `authenticate.php`. This second script connects to LDAP and determines whether the door should be opened. If the user can enter the door, the script calls `open`, which opens the door. If the card is not found in LDAP, the script calls `log_unknown` to save the card as an unknown detection for later reference.

Directly afterwards, `authenticate.php` calls `set_state.php` to notify the user of its decision.   

## Installation
* Install the raspberry pi and configure as needed
* `apt-get update`
* `apt-get upgrade`
* Install card reader libraries: `apt-get install build-essential libusb-dev libusb++-dev libpcsclite-dev libccid`
* Install PHP5 `apt-get install php5-dev php5-cli php-pear`
* Install PHP extension for smartcard `pecl install pcsc-alpha`
* Configure the extension `echo 'extension=pcsc.so' > /etc/php5/mods-available/pcsc.ini`
* Enable the extension `php5enmod pcsc`
* Disable pn533 kernel module (including hotloading) to not claim the port
* Copy the files to the system
* Configure system to reboot every morning at 6am using a cronjob (or the scanner will lose connection to the server)
* Configure the server to start `scan` on boot
* Configure the server to run `program` every day to store the configuration of the teensy door opener
