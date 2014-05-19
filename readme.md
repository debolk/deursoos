# Deursoos

Software for the opening of the door.

## Installation
* Install the raspberry pi and configure as needed
* `apt-get update && apt-get upgrade`
* Install card reader libraries: `apt-get install build-essential libusb-dev libusb++-dev libpcsclite-dev libccid`
* Install PHP5 `apt-get install php5-dev php5-cli php-pear`
* Install git `apt-get install git`
* Create a directory for the code `mkdir /home/deursysteem`
* Git clone the repository into that directory
* Compile the system by running `make` and `make install`.
* Make the necessary executable by `chmod +x open_door reprogram_door scan scanner teensy_loader_cli`
* Configure the server to start `/home/deursysteem/scan` on boot
* Configure the server to run `reprogram_door` every day to restore the configuration of the teensy door opener
