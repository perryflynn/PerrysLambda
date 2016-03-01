<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstriert das Auflisten eines Verzeichnisses
 */

include(__DIR__."/examples-utils.php");

// Lambda classes
use PerrysLambda\DirectoryIterator as DI;
use PerrysLambda\ArrayList as AL;

$d = new AL(new DI(__DIR__."/../src/PerrysLambda/"));

var_dump($d->first());
