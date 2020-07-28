<?php

// This script works out suitable postcodes based on a grid of the british isles, to optimise searches

//http://api.getthedata.com/postcode/

// Contains OS data © Crown copyright and database right (2020)
// Contains Royal Mail data © Royal Mail copyright and Database right (2020)
// Contains National Statistics data © Crown copyright and database right (2020)

// Note: GTD data covers British Isles: England, Scotland, Wales but not Northern Ireland

require_once 'global.php';

// We'll dump the postcode searches here:
$fp = fopen("./searches.csv", "w");


$conn = new PDO("pgsql:dbname=postcodes");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getPostcode($e,$n) {
    global $conn, $radius;
    // Radius just helps us keep things faster and identifies gaps more easily:
    $stm = $conn->prepare("select postcode from postcode where abs(?-easting)<=? and abs(?-northing)<=? order by pow(pow(abs(?-easting),2) + pow(abs(?-northing),2),0.5) limit 1");
    $stm->execute([$e, $radius, $n, $radius, $e, $n]);
    if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        return $row['postcode'];
    } else {
        return false;
    }
}

// 700000, 1200000 would be an absolute limit...
// We need GOV.UK search radius to be greater than the distance to the midpoint between two search boxes
// So, if dist is the "padding" of a point, we'd need r > sqrt(dist^2 + dist^2)... I think
// search radius = 5mi=>8.05km
// dist of 5km would give us a midpoint at sqrt(25+25) which is 7.07km ... nice and comfortable, it seems?
// The higher we go, the fewer searches we need, but beware of sparse postcode areas and centroid inaccuracies.
// It's late. I'm tired. Are these maths right?
$radius = 5000;
for ($e=$radius; $e<700000;$e+= $radius*2) {
    for ($n = $radius; $n< 1200000; $n += $radius * 2) {
        $postcode = getPostcode($e, $n);
        if ($postcode) {
            fputs($fp, $postcode."\n");
        } else {
            printf("Nothing found for %d %s\n", $e, $n);
        }
    }
}

fclose($fp);