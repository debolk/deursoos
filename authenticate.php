<?php
/*
 * Read the card ID (passed as a command line parameter)
 * authenticate the card in LDAP
 * open the door if access is granted 
 */
$card_id = preg_replace('/[()*\\\\]/','\\\\$0',$argv[1]);

// Connect to ldap
$ldap = ldap_connect();
if (!$ldap) {
    syslog(LOG_EMERG, 'Cannot connect to LDAP');
    exit;
}

// Find the card in LDAP
$card_ldap = ldap_search($ldap, 'dc=bolkhuis,dc=nl', '(&(objectClass=device)(serialNumber=' . $card_id . '))');

// Check how many entries we have retrieved, bugging out if != 1
$count = ldap_count_entries($ldap, $card_ldap);
if($count > 1) {
	syslog(LOG_NOTICE, "Multiple owners detected for card $card_id");
	exit(1);
}
elseif($count == 0)
{
	syslog(LOG_NOTICE, "Rejected card $card_id");
    // Card could not be found in LDAP, log to failures.txt
    file_put_contents('failures.txt', date().' '.$card_id);
	exit(2);
}

// Check whether the card has only one owner
$card_ldap = ldap_first_entry($ldap, $card_ldap);
$card_dn = ldap_get_dn($ldap, $card_ldap);

$owner = preg_replace('/^[^,]*,/','',$card_dn);
$owner_ldap = ldap_search($ldap, $owner, '(objectClass=inetOrgPerson)');
$count = ldap_count_entries($ldap, $owner_ldap)
if($count != 1)
{
	syslog(LOG_NOTICE, "Card $card_id has too many owners ($count)");
	exit(3);
}

// Check for authorisation to use the door
$owner_ldap = ldap_first_entry($ldap, $owner_ldap);
$attributes = ldap_get_attributes($ldap, $owner_ldap);
if(in_array('gosaIntranetAccount', $attributes['objectClass']))
{
	syslog(LOG_NOTICE, "Allowed entry of $owner with card ID $card_id");
    // Actually open the door
	for($i = 0; $i < 5; $i++)
	{
		exec('/home/deursysteem/open');
		sleep(1);
	}
}
else {
	syslog(LOG_NOTICE, "Denied entry of $owner with card ID $card_id");
}
