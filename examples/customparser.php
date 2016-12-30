<?php

/**
 * Dieses Script einfach in einer CLI ausführen
 * Ein Webserver ist nicht notwendig.
 *
 * Dieses Script demonstiert das einlesen von
 * Textdateien mit besonderen Format und das Parsen von JSON
 */

include(__DIR__."/examples-utils.php");


/**
 * Print columns
 * @param array $columns
 * @return string
 */
function columnline(array $columns)
{
    $result = "";
    foreach($columns as $column)
    {
        list($length, $text) = $column;
        $result .= str_pad($text, $length, " ", STR_PAD_RIGHT);
    }
    return $result."\n";
}


// Lambda classes
use PerrysLambda\ObjectArray;
use PerrysLambda\ArrayList;
use PerrysLambda\Serializer\Serializer;
use PerrysLambda\IO\LineIterator;
use PerrysLambda\Converter\ListConverter;
use PerrysLambda\Converter\FieldConverter;
use PerrysLambda\IO\File;
use PerrysLambda\Serializer\DateTimeSerializer as SerDateTime;

// Timer
$total = new Stopwatch();
$watch = new Stopwatch();

$total->start();

echo "\n";
L::line("Begin.");

$watch->start();


/**
 * Parser
 */

// Serialize row to JSON
$rowser = function(&$row, &$key)
{
    $row = json_encode(array("sensordata" => $row));
    return true;
};

// Deserialize JSON String to ObjectArray
$rowdeser = function(&$row, &$key)
{
    if(!($row instanceof ObjectArray))
    {
        $json = json_decode($row, true);
        if(isset($json['sensordata']) && is_array($json['sensordata']))
        {
            $row = new ObjectArray($json['sensordata']);
            $row->outdoor = new ObjectArray($json['sensordata']['outdoor']);
            $row->brightness = new ObjectArray($json['sensordata']['brightness']);
            $row->indoor = new ObjectArray($json['sensordata']['indoor']);
            return true;
        }
        else
        {
            $row = null;
        }
        return false;
    }
    return true;
};

// Field converters (string to number or datetime)
$fconv = new FieldConverter();
$fconv->setSerializer('timestamp', new SerDateTime("Y-m-d\\TH:i:s.uO", new \DateTimeZone("Europe/Berlin")));

$conv = new ListConverter();
$conv->setSerializer(new Serializer($rowser, $rowdeser));
$conv->setFieldConverter($fconv);

// Read testdata line by line
$iterator = new LineIterator(new File(__DIR__."/testdata.txt"));

// Load only last 500 records
$c = $iterator->count();
$conv->setIteratorSource($iterator/*, $c-500*/);

L::line("Converter created:", $watch->stop()->result());


/**
 * Create list and filter / group
 */

// Create list
$watch->start();
$list = new ArrayList($conv);
L::line("Data imported:", $watch->stop()->result());

// Stats
echo "\n";
L::line($list->length(), "records");
L::line("First record:", $list->first()->timestamp->format('Y-m-d H:i:s'));
L::line("Last record:", $list->last()->timestamp->format('Y-m-d H:i:s'));
echo "\n";

// Only the newest day, group by hour
$watch->start();

$dates = $list
        ->distinct(function($v) { return $v->timestamp->format('Y-m-d'); })
        ->take(-2)
        ->select(function($v) { return $v->timestamp->format('Y-m-d'); });

$hours = $list
        ->where(function(ObjectArray $r) use($list, $dates)
        {
            return in_array($r->timestamp->format('Y-m-d'), $dates);
        })
        ->groupBy(function(ObjectArray $r) { return $r->timestamp->format('Y-m-d H:00'); });

L::line("Filter for last day:", $watch->stop()->result());

/**
 * Print results
 */

// Table header
echo "\n";
echo columnline(array(
    array(19, "Time"),
    array(8, "Records"),
    array(10, "OMin °C"),
    array(10, "OAvg °C"),
    array(10, "OMax °C"),
    array(10, "IMin °C"),
    array(10, "IAvg °C"),
    array(10, "IMax °C"),
    array(8, "Min H%"),
    array(8, "Avg H%"),
    array(6, "Max H%"),
));

// Print hourly records
foreach($hours as $hour => $records)
{
    $columns = array();
    $columns[] = array(19, $hour);
    $columns[] = array(8, $records->length());
    $columns[] = array(10, number_format($records->min(function($v) { return $v->outdoor->tempc; }), 2)."°C");
    $columns[] = array(10, number_format($records->avg(function($v) { return $v->outdoor->tempc; }), 2)."°C");
    $columns[] = array(10, number_format($records->max(function($v) { return $v->outdoor->tempc; }), 2)."°C");
    $columns[] = array(10, number_format($records->min(function($v) { return $v->indoor->tempc; }), 2)."°C");
    $columns[] = array(10, number_format($records->avg(function($v) { return $v->indoor->tempc; }), 2)."°C");
    $columns[] = array(10, number_format($records->max(function($v) { return $v->indoor->tempc; }), 2)."°C");
    $columns[] = array(8, number_format($records->min(function($v) { return $v->outdoor->hudperc; }), 2)."%");
    $columns[] = array(8, number_format($records->avg(function($v) { return $v->outdoor->hudperc; }), 2)."%");
    $columns[] = array(6, number_format($records->max(function($v) { return $v->outdoor->hudperc; }), 2)."%");

    echo columnline($columns);
}


// Last 15 minutes
$date = clone $list->last()->timestamp;
$date->modify("-15 minutes");

$lastminutes = $list->where(function(ObjectArray $o) use($date) { return $o->timestamp >= $date; });

// Create JSON
$avg = array(
   "date" => date('c'),
   "indoortemp" => $lastminutes->avg(function(ObjectArray $o) { return $o->indoor->tempc; }),
   "outdoortemp" => $lastminutes->avg(function(ObjectArray $o) { return $o->outdoor->tempc; }),
   "airhumidity" => $lastminutes->avg(function(ObjectArray $o) { return $o->outdoor->hudperc; }),
   "pressure" => $lastminutes->avg(function(ObjectArray $o) { return $o->indoor->pressurehpa; }),
   "brightness" => $lastminutes->avg(function(ObjectArray $o) { return $o->brightness->lux; }),
);

echo "\n";
L::vdl($avg);

echo "\n";
L::line("Finished. Total time:", $total->stop()->result());
echo "\n";
