<?php

// Get card ID parameter
$card = preg_replace('/[()*\\\\]/','\\\\$0', $argv[1]);

// Connect to LDAP
$ldap = ldap_connect();
if (!$ldap) {
    end_process("Fatal: cannot connect to LDAP\n");
}

// Search for a card on record
$card_ldap = ldap_search($ldap, 'dc=bolkhuis,dc=nl', "(&(objectClass=device)(serialNumber=$card))");
if ($card_ldap != false) {
    // Check for multiple entries
    $count = ldap_count_entries($ldap, $card_ldap);
    if($count > 1) {
        end_process("Card rejected: card is added to the system twice\n");
    }
    elseif ($count == 0) {
        // Log the card ID (used for adding new cards by admins)
        file_put_contents('failures.txt', strftime('%c').' '.$card, FILE_APPEND);
        end_process("Card rejected: card is not known in the system\n");
    }
}
else {
    // Log the card ID (used for adding new cards by admins)
    file_put_contents('failures.txt', strftime('%c').' '.$card, FILE_APPEND);
    end_process("Card rejected: card is not known in the system\n");
}

// Find owner of the card
$card_ldap = ldap_first_entry($ldap, $card_ldap);
$card_dn = ldap_get_dn($ldap, $card_ldap);

$owner = preg_replace('/^[^,]*,/','',$card_dn);
$owner_ldap = ldap_search($ldap, $owner, '(objectClass=inetOrgPerson)');

$owner_count = ldap_count_entries($ldap, $owner_ldap);

// Reject cards with more than one owner
if($owner_count != 1)
{
    end_process("Card rejected: card has more than one owner");
}

// Read the complete LDAP-entry of the owner
$owner_ldap = ldap_first_entry($ldap, $owner_ldap);
$attributes = ldap_get_attributes($ldap, $owner_ldap);

// Check for authorisation
if(in_array('gosaIntranetAccount', $attributes['objectClass']))
{
    echo "Access granted: opening door\n";
	syslog(LOG_INFO, "Access granted: $owner ($card)");
    // Open the door
	for($i = 0; $i < 5; $i++)
	{
		exec('./open');
		usleep(200000);
	}
}
else {
    end_process("Card rejected: $owner unauthorised to use door\n");
}


// Utility functions

/**
 * Ends the execution of the script with the given message printed and logged
 * @param string $message the message to print
 * @return void
 */
function end_process($message)
{
    syslog(LOG_INFO, $message);
    exec('/usr/bin/php ./set_state.php rejected');
    exit($message);
}
