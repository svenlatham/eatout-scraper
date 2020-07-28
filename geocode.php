<?php

// This script uses the following dataset to provide basic lat/lng coordinates for business addresses:

//http://api.getthedata.com/postcode/

// Contains OS data © Crown copyright and database right (2020)
// Contains Royal Mail data © Royal Mail copyright and Database right (2020)
// Contains National Statistics data © Crown copyright and database right (2020)

require_once 'global.php';


$lookups = [];
echo "Loading postcode data (usually about 20 seconds)\n";
$fp = fopen("/mnt/d/data/open_postcode_geo.csv", 'r');
while (($line = fgetcsv($fp)) !== false) {
    $k = normalisePostcode($line[0]);
    $v = sprintf("%s,%s", $line[7], $line[8]);
    $lookups[$k] = $v;
}
fclose($fp);

// Utterly inefficient:
$entries = [];

readEntries();


function getLatLng($postcode) {
    global $lookups;
    if (!array_key_exists($postcode, $lookups)) {
        printf("Could not find entry for %s\n", $postcode);
        return ['',''];
    }
    $out = explode(",", $lookups[$postcode]);
    return $out;
}

foreach($entries as &$entry) {
    if ($entry[5]) { continue; }
    printf("Processing %s\n", $entry[4]);
    list($lat,$lng) = getLatLng($entry[4]);
    $entry[5] = $lat;
    $entry[6] = $lng;
}
unset($entry);
writeEntries();