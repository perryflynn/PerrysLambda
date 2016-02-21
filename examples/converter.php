<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstriert die Konvertierung von Datenfeldern
 */

include(__DIR__."/examples-utils.php");

// Lambda classes
use PerrysLambda\ObjectArray as OA;
use PerrysLambda\ArrayList as AL;
use PerrysLambda\Converter\CallableConverter as CC;

// Stopwatch
$total = new Stopwatch();
$watch = new Stopwatch();

$total->start();

// Memory usage on start
echo "\n";
L::line("Begin.");

// Custom type with converters
class AccessLog extends OA
{
    public function __construct(array $data = null, $fieldtype = null, $convertfield = true)
    {
        parent::__construct($data, $fieldtype, $convertfield);

        // Convert timestamp string to DateTime object
        $this->setFieldConverter('timestamp', new CC(function($in, OA $r)
        {
            // 01/Feb/2016:07:06:16 +0100
            return \DateTime::createFromFormat('d/M/Y:H:i:s O', $in);
        }));

        // auto generated field: HTTP method
        $this->setFieldConverter('method', new CC(function($in, OA $r)
        {
            // GET /ajax/unseen-notices-count/?_=1454313293675 HTTP/1.1
            return substr($r->uri, 0, strpos($r->uri, ' '));
        }));

        // auto generated field: HTTP version
        $this->setFieldConverter('version', new CC(function($in, OA $r)
        {
            // GET /ajax/unseen-notices-count/?_=1454313293675 HTTP/1.1
            return substr($r->uri, strrpos($r->uri, ' ')+1);
        }));
    }
}

// Parse JSON
$watch->start();
$data = json_decode(file_get_contents(__DIR__."/testdata.json"), true);
$collection = AL::asType('AccessLog', $data); // Collection<AccessLog>
unset($data);
L::line("Loaded data from JSON file.", $watch->stop()->result());

echo "\n";

// Convert timestamp
$watch->start();
$collection
    ->where(function(OA $r) { return $r->timestamp->format('s')==3; })
    ->each(function(OA $r) { L::vd([ $r->timestamp->format('Y-m-d H:i:s'), $r->method, $r->version ]); });

echo "\n";
L::line("All timestamp where second=3", $watch->stop()->result());
unset($collection);
L::line("Finished. Total time:", $total->stop()->result());
echo "\n";
