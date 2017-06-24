<?php

use PerrysLambda\ArrayList;
use PerrysLambda\ObjectArray;
use PerrysLambda\Converter\ItemConverter;
use PerrysLambda\Converter\ListConverter;
use PerrysLambda\Serializer\Serializer;
use PerrysLambda\Serializer\ObjectArraySerializer;

class CustomTypeTest extends PHPUnit_Framework_TestCase
{

    protected function getTestData()
    {
        return array(
            array('name' => 'anemone', 'ranking'=>5),
            array('name' => 'steak', 'ranking'=>10),
            array('name' => 'crocus', 'ranking'=>5.5),
            array('name' => 'super mario', 'ranking'=>8),
            array('name' => 'daisy', 'ranking'=>6),
            array('name' => 'money', 'ranking'=>7),
        );
    }

    public function testCustomTypes()
    {
        $list = MyArrayList::asType('MyObjectArray', $this->getTestData());
        $this->assertSame(true, $list->first() instanceof MyObjectArray);

        $flowers = $list->getFlowers();
        $this->assertSame(true, $flowers instanceof MyArrayList);
        $this->assertSame(3, $flowers->length());
        $this->assertSame('daisy', $flowers->last()->name);
        $this->assertSame(true, $flowers->last()->isFlower());
    }

    public function testCustomTypeWithSerializer()
    {
        $deserializer = function(&$value, &$key)
        {
            $value = $value*10;
            return true;
        };

        $serializer = function(&$value, &$key)
        {
            $value = $value/10;
            return true;
        };

        $fieldcon = new ItemConverter();
        $fieldcon->setSerializer(new ObjectArraySerializer());
        $fieldcon->setFieldSerializer('ranking', new Serializer($serializer, $deserializer));

        $listcon = new ListConverter();
        $listcon->setItemConverter($fieldcon);
        $listcon->setArraySource($this->getTestData());

        $list = new MyArrayList($listcon);

        $this->assertSame(50, $list->first()->ranking);
        $this->assertSame(5, $list->first()->serialize()['ranking']);
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
