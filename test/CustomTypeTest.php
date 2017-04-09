<?php

use PerrysLambda\Converter\ListConverter;
use PerrysLambda\ArrayList;
use PerrysLambda\ObjectArray;
use PerrysLambda\Serializer\Serializer;
use PerrysLambda\IArrayable;
use PerrysLambda\ArrayBase;
use PerrysLambda\Converter\ItemConverter;
use PerrysLambda\IItemConverter;
use PerrysLambda\Serializer\DateTimeSerializer;
use PerrysLambda\Serializer\BooleanSerializer;

class CustomTypeTest extends PHPUnit_Framework_TestCase
{

    public function testCustomTypes()
    {
        $this->assertSame(true, true);
    }

}

class MyObjectArray extends ObjectArray
{

    public function isFlower()
    {
        //return in_array($this->getScalar('flower')->, array(''));
    }

}
