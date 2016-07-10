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
use PerrysLambda\Serializer;
use PerrysLambda\IO\LineIterator;
use PerrysLambda\Converter;
use PerrysLambda\IO\File;
use PerrysLambda\FieldSerializer\DateTime as SerDateTime;
use PerrysLambda\FieldSerializer\Number as SerNumber;

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
        }
        else
        {
            $row = null;
        }
    }
};

$conv = new Converter();
$conv->setRowConverter(new Serializer($rowser, $rowdeser));

// Field converters (string to number or datetime)
$conv->setFieldConverter('date', new SerDateTime("Y-m-d\\TH:i:s.uO", new \DateTimeZone("Europe/Berlin")));
$conv->setFieldConverter('hudperc', new SerNumber());
$conv->setFieldConverter('tempc', new SerNumber());
$conv->setFieldConverter('tempf', new SerNumber());

// Read testdata line by line
$conv->setIteratorSource(new LineIterator(new File(__DIR__."/testdata.txt")));

L::line("Converter created:", $watch->stop()->result());


/**
 * Create list and filter / group
 */

// Create list
$watch->start();
$list = new ArrayList($conv);
L::line("Data imported:", $watch->stop()->result());

// Sort by date
$watch->start();
$list = $list->order(function(ObjectArray $r) { return $r->date; })->toList();
L::line("Data sorted:", $watch->stop()->result());

// Only the newest day, group by hour
$watch->start();

$dates = $list
        ->distinct(function($v) { return $v->date->format('Y-m-d'); })
        ->take(-2)
        ->select(function($v) { return $v->date->format('Y-m-d'); });

$hours = $list
        ->where(function(ObjectArray $r) use($list, $dates)
        {
            return in_array($r->date->format('Y-m-d'), $dates);
        })
        ->groupBy(function(ObjectArray $r) { return $r->date->format('Y-m-d H:00:00'); });

L::line("Filter for last day:", $watch->stop()->result());

unset($list);
L::line("Unset original list");


/**
 * Print results
 */

// Table header
echo "\n";
echo columnline(array(
    array(21, "Time"),
    array(8, "Records"),
    array(10, "Min °C"),
    array(10, "Avg °C"),
    array(10, "Max °C"),
    array(8, "Min H%"),
    array(8, "Avg H%"),
    array(6, "Max H%"),
));

// Print hourly records
foreach($hours as $hour => $records)
{
    $columns = array();
    $columns[] = array(21, $hour);
    $columns[] = array(8, $records->length());
    $columns[] = array(10, number_format($records->min(function($v) { return $v->tempc; }), 2)."°C");
    $columns[] = array(10, number_format($records->avg(function($v) { return $v->tempc; }), 2)."°C");
    $columns[] = array(10, number_format($records->max(function($v) { return $v->tempc; }), 2)."°C");
    $columns[] = array(8, number_format($records->min(function($v) { return $v->hudperc; }), 2)."%");
    $columns[] = array(8, number_format($records->avg(function($v) { return $v->hudperc; }), 2)."%");
    $columns[] = array(6, number_format($records->max(function($v) { return $v->hudperc; }), 2)."%");

    echo columnline($columns);
}


echo "\n";
L::line("Finished. Total time:", $total->stop()->result());
echo "\n";
