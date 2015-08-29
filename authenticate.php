<?php
/*
 * Read the card ID (passed as a command line parameter)
 * authenticate the card in the API
 * open the door if access is granted
 */

// Setup
require 'vendor/autoload.php';
require 'config.php';

// Define constants for return values of the script
define('RETURN_ACCEPTED', 0);
define('RETURN_ERROR',    1);
define('RETURN_DENIED',   2);

// Read card id argument
if (! isset($argv[1])) {
    syslog(LOG_EMERG, 'authenticate.php called without a card ID');
    exit(RETURN_ERROR);
}
$card_id = preg_replace('/[()*\\\\]/','\\\\$0',$argv[1]);

// Open the door if access is granted
if (accessIsGrantedTo($card_id, $config)) {
    syslog(LOG_NOTICE, "Granted entry to $card_id");
    openDoor();
    exit(RETURN_ACCEPTED);
}
else {
    syslog(LOG_NOTICE, "Denied entry to $card_id");
    exit(RETURN_DENIED);
}

/*
 * Utility functions for usage in this script
 */

/**
 * Determine whether a card is allowed entry
 * by calling the PassManagement API
 * http://passmanagement.i.bolkhuis.nl/docs/
 *
 * @param  string  $card_id full card ID as scanned
 * @param  array   $config  configuration details for communicating with the server
 * @return boolean          true if access is granted, false otherwise
 */
function accessIsGrantedTo($card_id, $config)
{
    $client = new GuzzleHttp\Client;

    // Get an access token for the API (client credentials flow)
    try {
        $response = $client->post($config['oauth_endpoint'], [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $config['client_id'],
                'client_secret' => $config['client_secret']
            ]
        ]);
    }
    catch (Exception $e) {
        // Any exception results in termination
        syslog(LOG_ERR, "Cannot get a OAuth 2 token from the authorisation server: {$e->getMessage()}");
        return false;
    }

    // Now we have a valid token
    $body = (string)$response->getBody();
    $token = json_decode($body);

    // Send card to API for validation
    try {
        $client->get($config['passmanagement'] . $card_id . '?access_token=' . $token->access_token);
    }
    // A client error means access was denied
    catch (GuzzleHttp\Exception\ClientException $e) {
        return false;
    }
    // Any other error is unexpected
    catch (Exception $e) {
        syslog(LOG_ERR, "Unexpected error when communicating with the PassManagement API: {$e->getMessage()}");
        return false;
    }

    // An absence of errors means access is granted
    return true;
}

/**
 * Open the door
 * @return void
 */
function openDoor()
{
    for($i = 0; $i < 5; $i++) {
        exec('/opt/deursysteem/open_door');
        sleep(1);
    }
}
