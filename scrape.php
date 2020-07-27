<?php

/**
 * Quick and dirty scraper of https://www.tax.service.gov.uk/eat-out-to-help-out/find-a-restaurant/results?postcode=SW1a+1aa
 * I don't have a decent list of postcode centroids yet, so will be spidering.
 * That'll cause issues where there's a 2mi gap (places will be lost) but should provide a fairly contiguous map otherwise. We'll see...
 */

define('PLACES', './places.csv');

// Suppress DOM errors:
libxml_use_internal_errors(true);

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

function getList($query)
{
    // Takes in a postcode, returns an array of results
    $url = sprintf("https://www.tax.service.gov.uk/eat-out-to-help-out/find-a-restaurant/results?postcode=%s", urlencode($query));
    $html = file_get_contents($url);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $results = [];
    foreach ($dom->getElementsByTagName('li') as $li) {
        if ($li->getAttribute('class') == 'govuk-results-list-item') {
            // This is a result
            $name = $address = $postcode = $interimAddress = '';
            foreach ($li->childNodes as $node) {
                if ($node->nodeType == XML_TEXT_NODE) {
                    continue;
                }
                switch ($node->getAttribute('class')) {
                    case 'govuk-heading-m':
                        $name = $node->textContent;
                        break;
                    case 'govuk-results-address govuk-body':
                        $interimAddress = $node->textContent;
                        break;
                    default:
                        // Ignore for now
                }
            }
            if (!$name || !$interimAddress) {
                throw new Exception("Could not parse for {$name} {$interimAddress}");
            }
            $addressParts = explode(',', $interimAddress);
            $postcode = normalisePostcode(array_pop($addressParts));
            $address = implode(',', $addressParts);
            $hash = generateHash($name, $postcode);
            $results[] = [time(), $hash, $name, $address, $postcode];
        }
    }
    return $results;
}

$entries = []; // scantime (unix timestamp), name, address ex postcode, postcode

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
        if (count($line) != 5) {
            throw new Exception("Could not read line {$line}");
        }
        $entries[$line[1]] = $line; // Line 1 contains the hash
    }
}

function writeEntries() {
    global $entries;
    $fp = fopen(PLACES, 'w');
    fputcsv($fp, ['scantime','hash','name','address','postcode']);
    foreach($entries as $entry) {
        fputcsv($fp, $entry);
    }
    fclose($fp);
}


// Let's do this...
readEntries();

$searched = []; // Postcodes already scanned
$queue[] = 'SW1A1AA'; // Postcodes to scan


while (count($queue) > 0) {
    // First, check there are no searched entries in the queue:
    $queue = array_diff($queue, $searched);

    $postcode = array_pop($queue);
    $postcode = normalisePostcode($postcode);
    printf("Queue contains %d, searched contains %d, we're searching %s\n", count($queue), count($searched), $postcode);
    $results = getList($postcode);
    foreach ($results as $result) {
        // Have we seen this before? If so, ignore!
        $key = md5($result[2] . $result[4]);
        if (!array_key_exists($key, $entries)) {
            $entries[$key] = $result;
            printf("Adding %s\n", implode("   ", $result));
            $queue[] = $result[4]; // We'll dedupe later...
        }
        
    }
    $searched[] = $postcode;
    sleep(1); // Website courtesy
    writeEntries();
}
