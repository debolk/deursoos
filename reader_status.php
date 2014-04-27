<?php

// Validate context
$scard = scard_establish_context();
if (! scard_is_valid_context($scard)) {
    echo "Context is invalid!\n";
    exit;
}

// Connect to reader
$readers = scard_list_readers($scard);

// Can't connect
if(!$readers) {
    echo "Reader not found";
    exit;
}

// Too many readers found
if (count($readers) > 1) {
    echo "Too many readers: ".count($readers)."\n";
    exit;
}

// No failures found, report success
echo "Reader online and ready. All checks passed\n";
exit;
