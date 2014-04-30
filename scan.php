<?php

/*
 * Some housekeeping before starting the work-day
 */

// Disable the time-limit on execution;
set_time_limit(0);

// Set the working directory for this script
if (! chdir('/home/deursysteem')) {
    syslog(LOG_EMERG, 'Can\'t change working directory of PHP to the system');
    exit;
}

/*
 * Main process for reading and validating cards, and opening the door
 */

// Setup environment
$context = establish_context();
$scanner = connect_scanner($context);

// Wait for a card
while (true)
{
    $card = scan($context, $scanner);

    // Indicate that we're hard at work
    signal_busy($card);

    // Authenticate the card
    if (authenticate(get_id($card))) {
        signal_success($card);
        open_door();
    }
    else {
        signal_failure($card);
    }
}

// Disconnect from the scanner
disconnect_scanner($card);

/*
 * End of main process; below are the actual function implemenations 
 */

/**
 * Gets the scanner context
 * @return mixed valid scanner context
 */
function establish_context()
{
    $context = scard_establish_context();
    if (! scard_is_valid_context($context)) {
        syslog(LOG_EMERG, 'Can\'t establish scanner context');
        exit;
    }

    return $context;
}

/**
 * Connect to the scanner
 * @param array $context valid scanner context
 * @return mixed the handle of the scanner
 */
function connect_scanner($context)
{
    $readers = scard_list_readers($context);
    if(!$readers || count($readers) > 1) {
        syslog(LOG_EMERG, 'Incorrect amount of readers (!= 1)');
        exit;
    }

    return $readers[0];
}

/**
 * Disconnect from the reader
 * @param array $card a valid card resource
 * @return void
 */
function disconnect_scanner($card)
{
    scard_disconnect($card);
    sleep(5);  // Delay is needed for a successful disconnect
}

/**
 * Connect to the scanner and wait for a card to be present
 * @param array $context valid scanner context
 * @param array $reader valid reader resource
 * @return string the unique ID of the card
 */
function scan($context, $reader)
{
    // Wait for a card to be presented
    $card = false;
    while(!$card)
    {
        // Try to connect once per second
        sleep(1);
        $card = scard_connect($context, $reader);
    }

    // Once connected, send the command to disable the default beep when a card is presented
    scard_transmit($card, 'FF00520000');

    // Return the card
    return $card;
}

/**
 * Read the standarised ID from the card in the correct format
 * @param array $card card information data
 * @throws Exception problem indicated in string
 * @return string the unique card ID
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
    $response = scard_transmit($card, 'FFCA000004');

    $status = substr($response, -4);
    $identifier = substr($response, 0, strlen($response) - 4);

    if($status != '9000') {
        throw new Exception('Cannot read ID from card');
    }

    // Return composite ID
    return "$manufacturer-$identifier";
}

/**
 * Authenticates a ID-card against LDAP
 * @param string $card_id the unique card-ID to check
 * @return boolean true if the card is accepted, false if not
 */
function authenticate($card_id)
{
    // Connect to LDAP
    $ldap = ldap_connect();
    if (!$ldap) {
        syslog(LOG_EMERG, 'Can\'t connect to LDAP');
        return false;
    }

    // Search for a card on record
    $card_ldap = ldap_search($ldap, 'dc=bolkhuis,dc=nl', '(&(objectClass=device)(serialNumber='.$card_id.'))');

    // Card is not known in LDAP
    if ($card_ldap == false) {
        syslog(LOG_NOTICE, 'Card rejected: unknown in LDAP. Card ID: '.$card_id);
        return false;
    }

    // Check for multiple entries
    $count = ldap_count_entries($ldap, $card_ldap);
    if($count > 1) {
        syslog(LOG_NOTICE, 'Card rejected: card is added to LDAP twice. Card ID: '.$card_id);
        return false;
    }

    // Check for zero number of entries
    if ($count == 0) {
        syslog(LOG_NOTICE, 'Card rejected: unknown in LDAP. Card ID: '.$card_id);
        return false;
    }

    // Find owner of the card
    $card_ldap = ldap_first_entry($ldap, $card_ldap);
    $card_dn = ldap_get_dn($ldap, $card_ldap);

    $owner = preg_replace('/^[^,]*,/','',$card_dn);
    $owner_ldap = ldap_search($ldap, $owner, '(objectClass=inetOrgPerson)');

    // Reject cards with more than one owner
    if(ldap_count_entries($ldap, $owner_ldap) != 1)
    {
        syslog(LOG_INFO, "Card rejected: card has more than one owner. Card ID: ".$card_id);
        return false;
    }

    // Read the complete LDAP-entry of the owner
    $owner_ldap = ldap_first_entry($ldap, $owner_ldap);
    $attributes = ldap_get_attributes($ldap, $owner_ldap);

    // Check for authorisation
    if(! in_array('gosaIntranetAccount', $attributes['objectClass']))
    {
        syslog(LOG_NOTICE, 'Card rejected: unauthorised to use door. Owner: '.$owner.' Card ID: '.$card_id);
        return false;
    }
    else {
    }

    // All checks passed, card is authorized to use the door
    syslog(LOG_NOTICE, 'Card accepted. Owner: '.$owner.' Card ID: '.$card_id);
    return true;
}

/**
 * Opens the door
 * @return void
 */
function open_door()
{
    for($i = 0; $i < 5; $i++)
    {
        $connection = fopen("/dev/ttyACM0", "w+");
        fwrite($connection, "open");
        usleep(20000);
    }
}

/**
 * Signal the user through LED and sound that the card was accepted
 * @param array $card a valid card resource
 * @return void
 */
function signal_success($card)
{
    scard_transmit($card, "FF0040A60401010201");
}

/**
 * Signal the user through LED and sound that the card was rejected
 * @param array $card a valid card resource
 * @return void
 */
function signal_failure($card)
{
    scard_transmit($card, "FF0040560401010801");
}

/**
 * Signal the user through LED and sound that the system is busy checking the card
 * @param array $card a valid card resource
 * @return void
 */
function signal_busy($card)
{
    scard_transmit($card, 'FF0040CF0400000000');
}
