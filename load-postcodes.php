<?php

// This just loads the postcode data into a PostGIS instance on my machine...

//http://api.getthedata.com/postcode/

// Contains OS data © Crown copyright and database right (2020)
// Contains Royal Mail data © Royal Mail copyright and Database right (2020)
// Contains National Statistics data © Crown copyright and database right (2020)

require_once 'global.php';


$lookups = [];
echo "Loading postcode data\n";
$fp = fopen("/mnt/d/data/open_postcode_geo.csv", 'r');

$conn = new PDO("pgsql:dbname=postcodes");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->exec("DELETE FROM postcode");

$count = 0;
$conn->beginTransaction();
while (($line = fgetcsv($fp)) !== false) {
    $k = normalisePostcode($line[0]);
    if ($line[3] == '\N') {
        continue;  // Erroneous code
    }
    $stm = $conn->prepare("INSERT INTO postcode(postcode, size, easting, northing, lat, lng) VALUES (?,?,?,?,?,?);");
    $stm->execute([$k, $line[2], $line[3], $line[4], $line[7], $line[8]]);
    $count++;
    if ($count % 5000 == 0) { 
        $conn->commit();
        printf("Done %d\n", $count);
        $conn->beginTransaction();
    }
}
$conn->commit();
fclose($fp);

