all:
	gcc scanner.c -o scanner.new -Wall -lpcsclite -I/usr/include/PCSC

install:
	mv scanner.new scanner
