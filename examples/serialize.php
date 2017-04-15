<?php

include(__DIR__."/examples-utils.php");

// examples/serialize.php

use PerrysLambda\ArrayList;
use PerrysLambda\ObjectArray;
use PerrysLambda\IArrayable;
use PerrysLambda\Converter\ItemConverter;
use PerrysLambda\Serializer\Serializer;
use PerrysLambda\Serializer\DateTimeSerializer;
use PerrysLambda\Serializer\BooleanSerializer;
use PerrysLambda\Converter\ListConverter;

// Testdata
$rawstring = <<<HEREDOC
{"foo":419995191,"bar":1492275950.5668,"foobar":"true","barfoo":"2018-12-08T20:24:41+0100","test":"Ivan"}
{"foo":92059366,"bar":1492275950.5669,"foobar":"false","barfoo":"2021-04-11T04:16:27+0200","test":"Gadget"}
{"foo":174207424,"bar":1492275950.5671,"foobar":"true","barfoo":"2017-07-29T21:53:36+0200","test":"Timmy"}
{"foo":624519809,"bar":1492275950.5672,"foobar":"false","barfoo":"2018-06-23T20:22:49+0200","test":"Alice"}
HEREDOC;

// Split into lines
$rawlines = explode("\n", $rawstring);

// Parse lines to one array with fields per line
$deserializer = function(&$row, &$key)
{
    if(is_string($row))
    {
        $data = json_decode($row, true);
        if(is_array($data))
        {
            $row = new ObjectArray($data);
        }
        else
        {
            return false;
        }
    }
    return true;
};

$serializer = function(&$row, &$key)
{
    if($row instanceof IArrayable)
    {
        $row = $row->toArray();
    }

    if(is_array($row))
    {
        $row = json_encode($row);
        if(is_string($row))
        {
            return true;
        }
    }
    return false;
};

// Row serializer
$fieldconverter = new ItemConverter();
$fieldconverter->setSerializer(new Serializer($serializer, $deserializer));

// Field serializer
$fieldconverter->setFieldSerializers(array(
    "barfoo" => DateTimeSerializer::fromIsoFormat(),
    "foobar" => new BooleanSerializer(),
));

// Create converter
$conv = new ListConverter();
$conv->setItemConverter($fieldconverter);
$conv->setArraySource($rawlines);

// Load raw data into ArrayList
$list = new ArrayList($conv);

// Modify data
$list
    ->where(function($v) { return $v->foobar===true; })
    ->each(function($v) { $v->foo = $v->foo+1; });

// Serialize modified data
$serlines = $list->serialize();
var_dump($serlines);
