<?php

// Convert command-line input to boolean
$succes = $argv[1] == "accepted";

// Connect to the card reader
$scard = scard_establish_context();
if (! scard_is_valid_context($scard)) {
    exit (1);
}
$readers = scard_list_readers($scard);
if(!$readers || count($readers) > 1) {
    exit (2);
}
$reader = $readers[0];
$card = scard_connect($scard, $reader);

// Send a correct signals to the card (sound and light)

if($succes) {
	scard_transmit($card, "FF0040A60401010201");
}
else {
	scard_transmit($card, "FF0040560401010801");
}

// Disconnect from the card
scard_disconnect($card);

//Delay is neccesary for succesful disconnect
sleep(1);
