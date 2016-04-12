<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script testet referenzierung
 */

include(__DIR__."/examples-utils.php");

$test = new \PerrysLambda\ObjectArray();

echo "\n";
L::line("->a = 12; ->a++;");
$test->a = 12;
$test->a++;

var_dump($test->a, $test('a'));

echo "\n";
L::line("copy a; a++");
$a = $test->a;
$a++;
var_dump($test->a);

L::line("ref copy a; a++");
$a = &$test->a;
$a++;
var_dump($test->a);


echo "\n";
L::line("b=12; b++;");
$test['b']=12;
$test['b']++;

var_dump($test['b']);

echo "\n";
L::line("copy b; \$b++; echo b");
$b = $test['b'];
$b++;
var_dump($test['b']);

echo "\n";
L::line("ref copy b; \$b++; echo b");
$b = &$test['b'];
$b++;
var_dump($b);

echo "\n";
