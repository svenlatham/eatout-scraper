<?php

define('PLACES', './places.csv');
define('STATE', './state.json');

// Read this in...
function readEntries() {
    global $entries;
    $entries = [];
    if (!file_exists(PLACES)) { return; }
    $fp = fopen(PLACES, 'r');
    $header = true;
    while (($line = fgetcsv($fp)) !== false) {
        if ($header == true) {
            $header = false; // It's the header
            continue;
        }
        if (count($line) == 1) {
            continue; // Blank line
        }
        if (count($line) == 5) {
            // Extra columns for latlng:
            $line[5] = '';
            $line[6] = '';
        }
        $entries[$line[1]] = $line; // Line 1 contains the hash
    }
}

function writeEntries() {
    global $entries;
    $fp = fopen(PLACES, 'w');
    fputcsv($fp, ['scantime','hash','name','address','postcode','lat','lng']);
    foreach($entries as $entry) {
        fputcsv($fp, $entry);
    }
    fclose($fp);
}

function normalisePostcode($in)
{
    $out = trim($in);
    $out = strtoupper($out);
    $out = str_replace(' ', '', $out);
    return $out;
}

function generateHash($name, $postcode) {
    // Aiming for collision free, reasonably compact and fairly robust...
    $input = strtoupper(trim($name.$postcode));
    $input = preg_replace('/[^A-Z0-9]/', '', $input); // Strip all but letters and numbers
    $hash = md5($input);
    $hash = substr(base64_encode(hex2bin($hash)),0,22);
    return $hash;
}