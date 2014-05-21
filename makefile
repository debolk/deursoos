all:
	gcc scanner.c -o scanner.new -Wall -lpcsclite -I/usr/include/PCSC
	gcc usbreset.c -o usbreset.new -Wall

install:
	mv scanner.new scanner
	mv usbreset.c usbreset
	chmod +x scanner usbreset
