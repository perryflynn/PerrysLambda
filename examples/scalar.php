<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstriert den Scalar Typ
 */

include(__DIR__."/examples-utils.php");

$s = new \PerrysLambda\ScalarProperty("Zähn € zahme Ziegen zögen zwei Zentner Zücker zum Zoö!");

echo "\n";
L::line("Begin");
echo "\n";

L::line($s->toString());

echo "\n";

L::line("Length:", $s->length());
L::line("Begin with \"Zä\":", L::b($s->startsWith("Zä")));
L::line("Begin with \"Ze\":", L::b($s->startsWith("Ze")));
L::line("Ends with \"Zoö!\":", L::b($s->endsWith("Zoö!")));
L::line("Ends with \"Zoo!\":", L::b($s->endsWith("Zoo!")));
L::line("Last 4 chars:", $s->substr(-4));
L::line("Contains \"zögen\":", L::b($s->contains('zögen')));
L::line("Contains \"zogen\":", L::b($s->contains('zogen')));
L::line("Index of \"ö\":", $s->indexOf('ö'));
L::line("Last index of \"ö\":", $s->lastIndexOf('ö'));

echo "\n";
