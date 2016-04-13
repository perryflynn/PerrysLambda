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
L::line("b=23; b++;");
$test['b'] = 23;
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
L::line("objectfor++");

$test->c = 34;
$test->d = 45;
$test->e = 56;
$test->f = 67;

$test->each(function($v) { $v++; });
L::vd($test->toArray());

$test->each(function(&$v) { $v++; });
L::vd($test->toArray());

echo "\n";
L::line("foreach++");
foreach($test->generator() as $item) { $item++; } unset($item);
L::vd($test->toArray());
foreach($test->generator() as &$item) { $item++; } unset($item);
L::vd($test->toArray());

echo "\n";

L::line("getAt() referencing");
$temp = $test->getAt(0);
$temp++;
L::vd(array($test->getAt(0), $test->a));

$temp = &$test->getAt(0);
$temp++;
L::vd(array($test->getAt(0), $test->a));

echo "\n";
