<?php

use PerrysLambda\ArrayList;
use PerrysLambda\ObjectArray;

class CustomTypeTest extends PHPUnit_Framework_TestCase
{

    public function testCustomTypes()
    {
        $data = array(
            array('name' => 'anemone', 'ranking'=>5),
            array('name' => 'steak', 'ranking'=>10),
            array('name' => 'crocus', 'ranking'=>5.5),
            array('name' => 'super mario', 'ranking'=>8),
            array('name' => 'daisy', 'ranking'=>6),
            array('name' => 'money', 'ranking'=>7),
        );

        $list = MyArrayList::asType('MyObjectArray', $data);
        $this->assertSame(true, $list->first() instanceof MyObjectArray);

        $flowers = $list->getFlowers();
        $this->assertSame(true, $flowers instanceof MyArrayList);
        $this->assertSame(3, $flowers->length());
        $this->assertSame('daisy', $flowers->last()->name);
        $this->assertSame(true, $flowers->last()->isFlower());
    }

}

class MyArrayList extends ArrayList
{
    public function getFlowers()
    {
        return $this->where(function($v) { return $v->isFlower(); });
    }
}

class MyObjectArray extends ObjectArray
{
    public function isFlower()
    {
        $flowers = new ArrayList(array('anemone', 'crocus', 'daisy', 'hibiscus'));
        return $flowers->any(function($v) { return $v==$this->nameScalar->toLower()->toString(); });
    }
}
