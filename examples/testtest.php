<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstriert den Scalar Typ
 */

include(__DIR__."/examples-utils.php");

$numbers = array(9, 5, 7, 2, 4, 8, 3, 3, 3, 1, 9, 5);
echo implode(", ", $numbers)."\n";
// Output: 9, 5, 7, 2, 4, 8, 3, 3, 3, 1, 9, 5

$lambda = new \PerrysLambda\ArrayList($numbers);
$temp = $lambda->where(function($v) { return $v > 4; })
               ->distinct(function($v) { return $v; })
               ->order(function($v) { return $v; })
               ->toList();

echo implode(", ", $temp->toArray());
// Output: 5, 7, 8, 9

