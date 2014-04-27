<?php

// Validate context
$scard = scard_establish_context();
if (! scard_is_valid_context($scard)) {
    echo "Fatal: context is invalid\n";
    exit;
}

// Connect to reader
$readers = scard_list_readers($scard);

// Can't connect
if(!$readers) {
    echo "Fatal: can't connect to reader";
    exit;
}

// Too many readers found
$count = count($readers);
if ($count > 1) {
    echo "Fatal: too many readers ($count). Unsure which one to use\n";
    exit;
}

// No failures found, report success 
echo "Success: reader online and ready. All checks passed\n";
exit;
