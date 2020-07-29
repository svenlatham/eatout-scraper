<?php

/**
 * Quick and dirty scraper of https://www.tax.service.gov.uk/eat-out-to-help-out/find-a-restaurant/results?postcode=SW1a+1aa
 * I don't have a decent list of postcode centroids yet, so will be spidering.
 * That'll cause issues where there's a 2mi gap (places will be lost) but should provide a fairly contiguous map otherwise. We'll see...
 */

require_once 'global.php';

// Suppress DOM errors:
libxml_use_internal_errors(true);

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
            $results[] = [time(), $hash, $name, $address, $postcode, '', ''];
        }
    }
    return $results;
}

$entries = []; // scantime (unix timestamp), name, address ex postcode, postcode

function readState() {
    global $searched, $queue;
    if (!file_exists(STATE)) { return; }
    $state = json_decode(file_get_contents(STATE), true);
    $searched = $state['searched'];
    $queue = $state['queue'];
}

function writeState() {
    global $searched, $queue;
    $state = ['searched'=> $searched, 'queue' => $queue];
    $fp = fopen(STATE,'w');
    fputs($fp, json_encode($state));
    fclose($fp);
}

$searched = []; // Postcodes already scanned

// Let's do this...
readState();
readEntries();

// Read in our searches list...
$queue = explode("\n", file_get_contents('searches.csv'));


$nextWrite = time() + 60;
while (count($queue) > 0) {
    // First, check there are no searched entries in the queue:
    $queue = array_unique($queue);
    $queue = array_diff($queue, $searched);

    if (count($queue) == 0) { break; }
    $postcode = array_pop($queue);
    $postcode = normalisePostcode($postcode);
    printf("Queue contains %d, searched contains %d, we're searching %s\n", count($queue), count($searched), $postcode);
    $results = getList($postcode);
    $incorporatePostcodes = false;
    if (count($results) == 100) {
        printf("100 results, so we're going to delve deeper into this postcode area\n");
        $incorporatePostcodes = true; // The search returns a maximum of 100 postcodes, so we might need to scour more closely here...
    }
    foreach ($results as $result) {
        // Have we seen this before? If so, ignore!
        $key = $result[1];
        if (!array_key_exists($key, $entries)) {
            $entries[$key] = $result;
            printf("Adding %s\n", implode("   ", $result));
        }
        if ($incorporatePostcodes) {
            $queue[] = $result[4]; // Include this postcode in the search list
        }
    }
    $searched[] = $postcode;
    sleep(1); // Website courtesy
    if ($nextWrite < time() ) {
        writeEntries();
        writeState();
        $nextWrite = time() + 60;
    }
}
writeEntries();
writeState();