<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstriert die Basisfunktionen und
 * fuehrt einige Benchmarks durch.
 */

include(__DIR__."/examples-utils.php");

// Lambda classes
use PerrysLambda\ObjectArray as OA;
use PerrysLambda\ArrayList as AL;

// Stopwatch
$total = new Stopwatch();
$watch = new Stopwatch();

$total->start();

// Memory usage on start
echo "\n";
L::line("Begin.");

// Basic example
$watch->start();
$test = new AL(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
L::vdl($test->where(function($v) { return $v>5; })->toArray());
L::line("Select values greater than X from simple array", $watch->stop()->result());

echo "\n";

// Parse JSON (testdata)
L::line("Filesize of JSON is", number_format((filesize(__DIR__."/testdata.json")/1024), 2), "KB");
$watch->start();
$data = json_decode(file_get_contents(__DIR__."/testdata.json"), true);
L::line("Loaded data from JSON file.", $watch->stop()->result(), ",", count($data), "records");

// Load JSON data into lambda
$watch->start();
$collection = AL::asObjectArray($data); // ArrayList<ObjectArray>
L::line("Data imported into Lambda.", $watch->stop()->result(), ",", $collection->length(), "records");

// Unset JSON Data
unset($data);
L::line("Unset original JSON data.");

// where
$watch->start();
$subc = $collection->where(function(OA $r){ return $r->username=="userfoo"; });
L::line("Where user is userfoo.", $watch->stop()->result(), ",", $subc->length(), "records");

// groupby
$watch->start();
$grouped = $collection->groupBy(function(OA $r) { return $r->username; });

// count records in groups
$temp = $grouped->select(function(AL $c, $key) {
    return array("key"=>$key, "count"=>$c->length());
});

L::vdl($temp);
L::line("Grouped by username.", $watch->stop()->result());

// select
$watch->start();
L::vdl($grouped['userfoo']->select(function(OA $r) { return $r->uri; }));
L::line("Select uris from userfoo.", $watch->stop()->result());

echo "\n";

// sum
$watch->start();
$sum = $collection->sum(function(OA $r) { return $r->bytes; });
L::line("Sum of bytes:", $sum, $watch->stop()->result());

// avg
$watch->start();
$avg = $collection->avg(function(OA $r) { return $r->bytes; });
L::line("Avg of bytes:", $avg, $watch->stop()->result());

// min
$watch->start();
$min = $collection->min(function(OA $r) { return $r->bytes; });
L::line("Smallest bytes:", $min, $watch->stop()->result());

// max
$watch->start();
$max = $collection->max(function(OA $r) { return $r->bytes; });
L::line("Biggest bytes:", $max, $watch->stop()->result());

// contains / groupby
$watch->start();
$agents = $collection
        ->where(function(OA $r) { return $r->agentScalar->contains('Ubuntu'); })
        ->groupBy(function(OA $r) { return $r->agent; });

echo "\n";
foreach($agents as $key => $items)
{
    L::vd(array("agent"=>$key, "count"=>$items->length()));
}

echo "\n";

L::line("Grouped ubuntu agents.", $watch->stop()->result());

// distinct / skip / take
$watch->start();
$skiptake = $collection
        ->where(function(OA $r) { return $r->agentScalar->contains('Android'); })
        ->distinct(function(OA $r) { return $r->agent; })
        ->skip(3)
        ->take(5);

L::line("Distinct by agent, skip first X records, take next Y records", $watch->stop()->result());

echo "\n";

// iterate list
$watch->start();
$skiptake->each(function(OA $r) { L::line($r->agent); });

echo "------\n";

// sort
$orderedskiptake = $skiptake->order(function(OA $r) { return $r->agentScalar->contains('Linux') ? 1 : 0; })
        ->thenBy(function(OA $r) { return $r->agent; })
        ->toList();

$orderedskiptake->each(function(OA $r) { L::line($r->agent); });

echo "\n";

L::line("Sort by linux / not linux and useragent", $watch->stop()->result());

echo "\n";

// unset
unset($subc);
L::line("Unset \$subc.");

unset($agents);
L::line("Unset \$agents.");

unset($skiptake);
L::line("Unset \$skiptake.");

unset($orderedskiptake);
L::line("Unset \$orderedskiptake.");

unset($collection);
L::line("Unset \$collection.");

unset($grouped);
L::line("Unset \$grouped.");
L::line("Finished. Total time:", $total->stop()->result());

echo "\n";
