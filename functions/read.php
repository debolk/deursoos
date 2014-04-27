<?php

// Define valid card signals (sound and light)
define('SCARD_GET_IDENTIFIER', 'FFCA000004');
define('SCARD_BEEP_DEFAULT_DISABLE', 'FF00520000');
define('SCARD_LED_SET_OK', 'FF0040CF0400000000');

// Connect to the card reader
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

// Keep scanning for a card
while(true) {

	$card = false;
	while(!$card)
	{
		sleep(1);
        // Connect to a card
		$card = scard_connect($scard, $reader);
	}
    // Send the command to disable the default beep when presenting a card
	scard_transmit($card, SCARD_BEEP_DEFAULT_DISABLE);

    // Output the correct card ID
	echo get_id($card) . "\n";
    // Notify the user that we're checking the card
	scard_transmit($card, SCARD_LED_SET_OK);

    // Disconnect from the card
	scard_disconnect($card);
    sleep(15);  // Delay is needed for a successful disconnect
}

// Reads the standarised ID from the card in the correct format
function get_id($card)
{
    // Read manufacturer ID
	$manufacturer = "FAILURE!!!";
	$info = scard_status($card);
	if(isset($info["ATR"]))
		$manufacturer = $info["ATR"];

    // Read ID from the card
	$response = scard_transmit($card, SCARD_GET_IDENTIFIER);

	$status = substr($response, -4);
	$identifier = substr($response, 0, strlen($response) - 4);

	if($status != "9000") {
		$identifier = "FAILURE!!!";
    }

    // Return composite ID
	return "$manufacturer-$identifier";
}
