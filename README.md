This project tries to implement C# .NET lambda expressions in PHP.

[![Build Status](https://travis-ci.org/perryflynn/PerrysLambda.svg?branch=master)](https://travis-ci.org/perryflynn/PerrysLambda) [![Coverage Status](https://coveralls.io/repos/github/perryflynn/PerrysLambda/badge.svg?branch=master)](https://coveralls.io/github/perryflynn/PerrysLambda?branch=master)

## Status

In development, semi-stable API.

## Composer

[Packagegist](https://packagist.org/packages/perryflynn/perrys-lambda)

```
composer require perryflynn/perrys-lambda
```

## Features

- Implements many C# .NET like lambda expressions
- Support for auto conversion of records in custom objects
- Support for iteation and foreach
- Support for [generator](http://php.net/manual/en/language.generators.syntax.php) (helps with call-by-reference in loops)
- Helper classes for filesystem access
- Helper classes for CSV file parsing

## Lambda methods

| Method | Description |
|--------|-------------|
*OrDefault | Define return value if expression has no hit, available for many methods
`first` | Get the first record
`last` | Get the last record
`single` | Get the single record, if `count!=0`, throw exception
`take` | Get the first `X` records
`skip` | Skip the first `X` records
`where` | Filter records by `callable` expression
`whereFirst` | Get first record matching the `callable` expression
`groupby` | Group records by `callable` expression
`distrinct` | Eliminate duplicate records bei `callable` expression
`intersect` | Returns the intersection of two ArrayList's
`except` | Returns the bidirectional diffrence of two ArrayList's
`any` | Returns true if `callable` expression returns at least one record `true`
`all` | Returns true if `callable` expression returns for all records `true`
`select` | Get an `array` of the values returned by `callable` expression
`selectMany` | Get an `array` of all values, arrays will be merged
`joinString` | Join a string from values returned by `callable` expression
`each` | Execute `callable` expression on each record
`sum` | Get the sum of numberic return values from `callable` expression
`min` | Get the smallest of numberic return values from `callable` expression
`max` | Get the biggest of numberic return values from `callable` expression
`avg` | Get the average of numberic return values from `callable` expression
`order` | Start sorting, begin ascending, more possible by `thenBy` / `thenByDesc`
`orderDesc` | Start sorting, begin descending, more possible by `thenBy` / `thenByDesc`

## Basic usage

```php
$basic = new \PerrysLambda\ArrayList(array(1,2,3,4,5,6,7,8,9));

$basic->where(function($n) { return $n<=3; }); // Returns ArrayList of [1,2,3]
$basic->where(function($n) { return $n<=3; })->sum(); // Returns 6
```

## Basic usage with ObjectArray and Strings instead of callables

```php
$data = array(
    array('name'=>'Frank', age=>12),
    array('name'=>'Gene', age=>42),
    array('name'=>'Jessie', age=>31),
    array('name'=>'Carl', age=>55),
);

$list = \PerrysLambda\ArrayList::asObjectArray($data);
$list->select('age')->sum(); // Returns 140
$list->where(function($v) { return $v->age > 40; })->select('age')->sum(); // Returns 97
```

## Deserialize / Serialize

- Deserialize json data
- Modify data
- Serialize again into json data
- Display modified json data

```php
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
```

## More examples

[See the unit tests](test/)
