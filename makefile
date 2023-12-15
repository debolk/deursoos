all:
	gcc scanner.c -o scanner.new -Wall -lpcsclite -I/usr/include/PCSC
	gcc usbreset.c -o usbreset.new -Wall
	gcc -O2 -Wall -s -DUSE_LIBUSB -o teensy_loader_cli.new teensy_loader_cli.c -lusb

install:
	mv scanner.new scanner
	mv usbreset.new usbreset
	mv teensy_loader_cli.new teensy_loader_cli
	chmod +x scanner usbreset teensy_loader_cli
	cp ./deursoos.service /etc/systemd/system
	systemctl daemon-reload

clean:
	rm -f teensy_loader_cli.new
	rm -f teensy_loader_cli
	rm -f scanner.new
	rm -f scanner
	rm -f usbreset.new
	rm -f usbreset
	rm -f /etc/systemd/system/deursoos.service
	systemctl daemon-reload
