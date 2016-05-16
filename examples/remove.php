<?php

include(__DIR__."/examples-utils.php");

$testdata = array(
    array('a' => 'foo', 'b'=>'bar', 'c'=>'foobar', 'd'=>'barfoo'),
    array('a' => 'foo2', 'b'=>'bar2', 'c'=>'foobar2', 'd'=>'barfoo2'),
    array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
    array('a' => '12', 'b'=>'22', 'c'=>'32', 'd'=>'42'),
);

$list = \PerrysLambda\ArrayList::asObjectArray($testdata);

$list->each(function($v) { L::vd($v->toArray()); });

$second = $list->getAt(1);
L::vdl($second->toArray());
$list->removeValue($second);

$list->each(function($v) { L::vd($v->toArray()); });

$next = $list->getAt(1);
L::vdl($next->toArray());
$list->removeValue($next);

$list->each(function($v) { L::vd($v->toArray()); });

L::vdl($list->getNames());

$more = $list->getAt(1);
L::vdl($more->toArray());
$list->removeKey(3);

$list->removeAt(0);

$list->each(function($v) { L::vd($v->toArray()); });

L::vdl($list->length());
