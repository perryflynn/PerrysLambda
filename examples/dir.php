<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstriert das Auflisten eines Verzeichnisses
 */

include(__DIR__."/examples-utils.php");

// Lambda classes
use PerrysLambda\IO\DirectoryIteratorNoDots as DI;
use PerrysLambda\ArrayList as AL;

$watch = new Stopwatch();

echo "\n";
L::line("Begin");
echo "\n";

$watch->start();
$d = new PerrysLambda\IO\Directory(new PerrysLambda\IO\File("/usr/bin"));
var_dump($d->first());
var_dump($d->lengthCached());
var_dump($d->whereFirst(function($v) { return strpos($v, "x")!==false; }));
var_dump($d->lengthCached());
L::line("First by own iterator", $watch->stop()->result());

echo "\n";

$watch->start();
$d = new AL(scandir("/usr/bin"));
var_dump($d->first());
var_dump($d->whereFirst(function($v) { return strpos($v, "x")!==false; }));
L::line("First by scandir", $watch->stop()->result());

$watch->start();
var_dump($d->lengthCached());
L::line("Cached Length", $watch->stop()->result());

echo "\n";
