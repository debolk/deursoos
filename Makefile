all: scanner usbreset

scanner: scanner.c
	gcc scanner.c -o scanner -Wall -lpcsclite -I/usr/include/PCSC

usbreset: usbreset.c
	gcc usbreset.c -o usbreset -Wall

install: all
	cp ./deursoos.service /etc/systemd/system
	sudo systemctl daemon-reload

clean:
	rm -f scanner
	rm -f usbreset
	rm -f /etc/systemd/system/deursoos.service
	sudo systemctl daemon-reload
