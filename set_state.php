<?php

define('SCARD_GET_IDENTIFIER', 'FFCA000004');
define('SCARD_BEEP_DEFAULT_DISABLE', 'FF00520000');
define('SCARD_LED_SET_REJECTED', 'FF0040560401010801');
define('SCARD_LED_SET_ACCEPTED', 'FF0040A60401010201');

$succes = $argv[1] == "accepted";

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

if($succes)
	scard_transmit($card, SCARD_LED_SET_ACCEPTED);
else
	scard_transmit($card, SCARD_LED_SET_REJECTED);

disconnect($card);

//Delay is neccesary for succesful disconnect
function disconnect($card)
{
	scard_disconnect($card);
	sleep(1);
}
