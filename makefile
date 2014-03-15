all:
	gcc scanner.c -o scanner.new -lpcsclite -I/usr/include/PCSC

install:
	mv scanner.new scanner
