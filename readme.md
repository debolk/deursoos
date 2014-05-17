# Deursoos

Software for the opening of the door.

## Installation
* Install the raspberry pi and configure as needed
* `apt-get update && apt-get upgrade`
* Install card reader libraries: `apt-get install build-essential libusb-dev libusb++-dev libpcsclite-dev libccid`
* Install PHP5 `apt-get install php5-dev php5-cli php-pear`
* Configure the server to start `/home/deursysteem/scan` on boot
* Configure the server to run `reprogram_door` every day to restore the configuration of the teensy door opener
