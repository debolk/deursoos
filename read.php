<?php

define('SCARD_GET_IDENTIFIER', 'FFCA000004');
define('SCARD_BEEP_DEFAULT_DISABLE', 'FF00520000');
define('SCARD_LED_SET_OK', 'FF0040CF0400000000');

$scard = scard_establish_context();

if (! scard_is_valid_context($scard)) {
	echo "Context is invalid!\n";
	exit (1);
}

$readers = scard_list_readers($scard);
if(!$readers || count($readers) > 1) {
	echo "Incorrect amount of readers (!= 1)\n";
	exit (2);
}

$reader = $readers[0];

while(true) {

	$card = false;
	while(!$card)
	{
		sleep(1);
		$card = scard_connect($scard, $reader);
	}
	scard_transmit($card, SCARD_BEEP_DEFAULT_DISABLE);

	echo get_id($card) . "\n";
	scard_transmit($card, SCARD_LED_SET_OK);

	disconnect($card);
	sleep(14);
}

//Delay is neccesary for succesful disconnect
function disconnect($card)
{
	scard_disconnect($card);
	sleep(1);
}

function get_id($card)
{
	$manufacturer = "FAILURE!!!";
	$info = scard_status($card);
	if(isset($info["ATR"]))
		$manufacturer = $info["ATR"];


	$response = scard_transmit($card, SCARD_GET_IDENTIFIER);

	$status = substr($response, -4);
	$identifier = substr($response, 0, strlen($response) - 4);

	if($status != "9000")
		$identifier = "FAILURE!!!";

	return "$manufacturer-$identifier";
}
