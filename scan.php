<?php

/**
 * Main process for reading and validating cards, and opening the door
 */

/**
 * Constants for communicating with the scanner
 */
define('SCARD_GET_IDENTIFIER', 'FFCA000004');
define('SCARD_BEEP_DEFAULT_DISABLE', 'FF00520000');
define('SCARD_LED_SET_OK', 'FF0040CF0400000000');

/**
 * Connect to the scanner and wait for a card to be present
 * @return string the unique ID of the card
 */
function scan()
{
    // Connect to the card reader
    $scard = scard_establish_context();
    if (! scard_is_valid_context($scard)) {
        throw new Exception('Context is invalid');
    }
    $readers = scard_list_readers($scard);
    if(!$readers || count($readers) > 1) {
        throw new Exception('Incorrect amount of readers (!= 1)');
    }
    $reader = $readers[0];

    // Wait for a card to be presented
    $card = false;
    while(!$card)
    {
        sleep(1);
        // Connect to a card
        $card = scard_connect($scard, $reader);
    }

    // Send the command to disable the default beep when presenting a card
    scard_transmit($card, SCARD_BEEP_DEFAULT_DISABLE);

    // Return the correct card ID
    return get_id($card);

    // // Notify the user that we're checking the card
    // scard_transmit($card, SCARD_LED_SET_OK);

    // // Disconnect from the card
    // scard_disconnect($card);
    // sleep(15);  // Delay is needed for a successful disconnect
}

/**
 * Read the standarised ID from the card in the correct format
 * @param array $card card information data
 */
function get_id($card)
{
    // Read manufacturer ID
    $manufacturer = "";
    $info = scard_status($card);
    if(isset($info["ATR"])) {
        $manufacturer = $info["ATR"];
    }
    else {
        throw new Exception('Cannot read manufacturer ID from card');
    }

    // Read ID from the card
    $response = scard_transmit($card, SCARD_GET_IDENTIFIER);

    $status = substr($response, -4);
    $identifier = substr($response, 0, strlen($response) - 4);

    if($status != '9000') {
        throw new Exception('Cannot read ID from card');
    }

    // Return composite ID
    return "$manufacturer-$identifier";
}
