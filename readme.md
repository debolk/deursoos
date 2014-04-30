# Deursoos

Software for the opening of the door.

## Technology
The main system consists of a single file `scan.php`. It establishes a connection to the scanner and then waits for a card. If a card is presented, it is authenticated against LDAP. If the user can enter the door, the script calls `open_door`, which opens the door. If the card is not found in LDAP, the script saves the card ID to the system logging.

## Installation
* Install the raspberry pi and configure as needed
* `apt-get update && apt-get upgrade`
* Install card reader libraries: `apt-get install build-essential libusb-dev libusb++-dev libpcsclite-dev libccid`
* Install PHP5 `apt-get install php5-dev php5-cli php-pear`
* Install PHP extension for smartcard
** `pecl install pcsc-alpha`
** Configure the extension `echo 'extension=pcsc.so' > /etc/php5/mods-available/pcsc.ini`
** Enable the extension `php5enmod pcsc`
* Install PHP extension for Direct IO (used to talk to the door)
** `pecl install dio-0.0.7`
** Configure the extension `echo 'extension=dio.so' > /etc/php5/mods-available/dio.ini`
** Enable the extension `php5enmod dio`
* Disable pn533 kernel module (including hotloading) to not claim the port to the scanner
* Copy the files to the system
* Configure system to reboot every morning at 6am using a cronjob (or the scanner will lose connection to the server)
* Configure the server to start `/home/deursysteem/scan &` on boot
* Configure the server to run `doorlock/program` every day to store the configuration of the teensy door opener
