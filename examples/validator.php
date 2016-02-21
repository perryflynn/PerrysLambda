<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstriert die Validation von Datenfeldern
 */

include(__DIR__."/examples-utils.php");

// Lambda classes
use PerrysLambda\ObjectArray as OA;
use PerrysLambda\Validator\PresenceValidator as PV;
use PerrysLambda\Validator\CallableValidator as CV;

// Stopwatch
$total = new Stopwatch();
$total->start();

// Memory usage on start
echo "\n";
L::line("Begin.");
echo "\n";

// Custom type with converters
class TestObject extends OA
{
    public function __construct(array $data = null, $fieldtype = null, $convertfield = true)
    {
        parent::__construct($data, $fieldtype, $convertfield);

        $this->addFieldValidator('test', new PV('Feld ist leer'));
        $this->addFieldValidator('test', new CV("Ist keine Zahl > 12", function($n, $v, OA $r)
        {
            return is_numeric($v) && $v>12;
        }));
        $this->addFieldValidator('foobar', new PV());
    }
}

$foo = new TestObject();

L::line("test = null");
$foo['test'] = null;
L::vd($foo->isFieldValid('test'));

echo "\n";

L::line("test = 8");
$foo['test'] = 8;
L::vd($foo->isFieldValid('test'));

echo "\n";
L::line("test = \"\"");
$foo['test'] = "";
L::vd($foo->isFieldValid('test'));

echo "\n";
L::line("test = 43");
$foo['test'] = 43;
L::vd($foo->isFieldValid('test'));

echo "\n";
L::line("All fields valid?");
L::vd($foo->isValid());

echo "\n";
L::line("foobar = barfoo");
$foo['foobar'] = "barfoo";
L::line("All fields valid?");
L::vd($foo->isValid());

echo "\n";
L::line("Finished. Total time:", $total->stop()->result());
echo "\n";
