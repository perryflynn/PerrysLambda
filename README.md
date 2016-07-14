This project tries to implement C# .NET lambda expressions in PHP.

[![Build Status](https://travis-ci.org/perryflynn/PerrysLambda.svg?branch=master)](https://travis-ci.org/perryflynn/PerrysLambda) [![Coverage Status](https://coveralls.io/repos/github/perryflynn/PerrysLambda/badge.svg?branch=master)](https://coveralls.io/github/perryflynn/PerrysLambda?branch=master)

## Status

In development. API can change anytime.

## Composer

[Packagegist](https://packagist.org/packages/perryflynn/perrys-lambda)

```
composer require perryflynn/perrys-lambda:dev-master
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
`each` | Execute `callable` expression on each record
`sum` | Get the sum of numberic return values from `callable` expression
`min` | Get the smallest of numberic return values from `callable` expression
`max` | Get the biggest of numberic return values from `callable` expression
`avg` | Get the average of numberic return values from `callable` expression
`joinString` | Join a string from values returned by `callable` expression
`order` | Start sorting, begin ascending, more possible by `thenBy` / `thenByDesc`
`orderDesc` | Start sorting, begin descending, more possible by `thenBy` / `thenByDesc`

## Basic usage

```php
$basic = new \PerrysLambda\ArrayList(array(1,2,3,4,5,6,7,8,9));

$basic->where(function($n) { return $n<=3; }); // Returns ArrayList of [1,2,3]
$basic->where(function($n) { return $n<=3; })->sum(); // Returns 6
```

// TODO: More basic examples

// TODO: Example for StringProperty

// TODO: Example for serialize / deserialize

// TODO: Example for filesystem access

// TODO: Example for csv parsing

## More examples

[See the tests](test/)
