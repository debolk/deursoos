<?php
$card = $argv[1];
$ldap = ldap_connect();
openlog("authenticator", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$card = preg_replace('/[()*\\\\]/','\\\\$0',$card);
$card_ldap = ldap_search($ldap, 'dc=bolkhuis,dc=nl', '(&(objectClass=device)(serialNumber=' . $card . '))');

$count = ldap_count_entries($ldap, $card_ldap);
if($count > 1)
{
	syslog(LOG_ERR, 'Multiple owners detected for card ' . $card);
	echo 'Multiple owners detected for card ' . $card;
	die();
} elseif($count == 0)
{
	syslog(LOG_NOTICE, 'Rejected card ' . $card);
	echo 'Rejected card ' . $card;
	exec('/opt/deur/log_unknown ' . escapeshellarg($card));
	die();
}

$card_ldap = ldap_first_entry($ldap, $card_ldap);
$card_dn = ldap_get_dn($ldap, $card_ldap);

$owner = preg_replace('/^[^,]*,/','',$card_dn);
$owner_ldap = ldap_search($ldap, $owner, '(objectClass=inetOrgPerson)');
if(ldap_count_entries($ldap, $owner_ldap) != 1)
{
	print ldap_count_entries($ldap, $owner_ldap);
	syslog(LOG_ERR, 'Not right amount of owners for card ' . $card . ' (' . $owner . ')');
	echo 'Not right amount of owners for card ' . $card . ' (' . $owner . ')';
	die();
}
$owner_ldap = ldap_first_entry($ldap, $owner_ldap);
$attributes = ldap_get_attributes($ldap, $owner_ldap);
if(in_array('gosaIntranetAccount', $attributes['objectClass']))
{
	syslog(LOG_INFO, 'Allowed entry of card ' . $card . ' (' . $owner . ')');
	echo 'Allowed entry of card ' . $card . ' (' . $owner . ')';
	for($i = 0; $i < 5; $i++)
	{
		exec('/opt/deur/open');
		usleep(200000);
	}
} else {
	syslog(LOG_ERR, 'Rejected user ' . $owner . ' with card ' . $card . ' (no permission)');
	echo 'Rejected user ' . $owner . ' with card ' . $card . ' (no permission)';
}
