all: scanner usbreset teensy_loader_cli

scanner: scanner.c
	gcc scanner.c -o scanner -Wall -lpcsclite -I/usr/include/PCSC

usbreset: usbreset.c
	gcc usbreset.c -o usbreset -Wall

teensy_loader_cli: teensy_loader_cli.c
	gcc -O2 -Wall -s -DUSE_LIBUSB -o teensy_loader_cli teensy_loader_cli.c -lusb

install: all
	cp ./deursoos.service /etc/systemd/system
	sudo systemctl daemon-reload

clean:
	rm -f teensy_loader_cli
	rm -f scanner
	rm -f usbreset
	rm -f /etc/systemd/system/deursoos.service
	sudo systemctl daemon-reload
