<?php

use PerrysLambda\ScalarProperty;
use PerrysLambda\ArrayList;
use PerrysLambda\ObjectArray;
use PerrysLambda\Converter\ObjectArrayListConverter;

class ExceptionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \PerrysLambda\Exception\InvalidKeyException
     */
    public function testInvalidKey()
    {
        $list = new ArrayList();
        $list['a'] = "b";
    }


    /**
     * @expectedException \PerrysLambda\Exception\InvalidValueException
     */
    public function testInvalidData()
    {
        new ObjectArray(null);
    }


    /**
     * @expectedException \PerrysLambda\Exception\InvalidKeyException
     */
    public function testInvalidKeyException()
    {
        $test = new ArrayList();
        $test->setData(array('foo'=>'bar'));
    }


    /**
     * @expectedException \PerrysLambda\Exception\InvalidDataException
     */
    public function testInvalidDataException()
    {
        $test = new ArrayList();
        $test->setData(42);
    }


    /**
     * @expectedException \OutOfBoundsException
     */
    public function testWhereFirstOutOfBounds()
    {
        $test = new ArrayList(array(1, 2, 3));
        $test->whereFirst(function($v) { return $v>4; });
    }


    /**
     * @expectedException \OutOfBoundsException
     */
    public function testFirstOutOfBounds()
    {
        $test = new ArrayList();
        $test->first();
    }


    /**
     * @expectedException \OutOfBoundsException
     */
    public function testLastOutOfBounds()
    {
        $test = new ArrayList();
        $test->last();
    }


    /**
     * @expectedException \LengthException
     */
    public function testSingleLengthException()
    {
        $test = new ArrayList();
        $test->single();
    }

    /**
     * @expectedException \PerrysLambda\Exception\InvalidTypeException
     */
    public function testCustomTypeConverterInvalid()
    {
        ArrayList::asType('\PerrysLambda\FooBarNonExistent', array());
    }

    /**
     * @expectedException \PerrysLambda\Exception\PerrysLambdaException
     */
    public function testObjectArrayGeneratorInvalid()
    {
        (new ObjectArray())->serializeGenerator();
    }

    /**
     * @expectedException \PerrysLambda\Exception\SerializerException
     */
    public function testListConverterSerializerInvalidReturntype()
    {
        $list = new \PerrysLambda\Converter\ListConverter();
        $foo = function() {  };
        $list->setSerializer(new PerrysLambda\Serializer\Serializer($foo, $foo));
        $key = 1;
        $row = "bla";
        $list->serialize($row, $key);
    }

    /**
     * @expectedException \PerrysLambda\Exception\SerializerException
     */
    public function testItemConverterSerializerInvalidReturntype()
    {
        $list = new \PerrysLambda\Converter\ItemConverter();
        $foo = function() {  };
        $list->setSerializer(new PerrysLambda\Serializer\Serializer($foo, $foo));
        $key = 1;
        $row = "bla";
        $list->serialize($row, $key);
    }

    /**
     * @expectedException \PerrysLambda\Exception\SerializerException
     */
    public function testItemConverterDeserializerInvalidReturntype()
    {
        $list = new \PerrysLambda\Converter\ItemConverter();
        $foo = function() {  };
        $list->setSerializer(new PerrysLambda\Serializer\Serializer($foo, $foo));
        $key = 1;
        $row = "bla";
        $list->deserialize($row, $key);
    }

    /**
     * @expectedException \PerrysLambda\Exception\SerializerException
     */
    public function testItemConverterSerializerFieldInvalidReturntype()
    {
        $list = new \PerrysLambda\Converter\ItemConverter();
        $foo = function() {  };
        $list->setSerializer(new PerrysLambda\Serializer\Serializer($foo, $foo));
        $list->setFieldSerializer(1, new PerrysLambda\Serializer\Serializer($foo, $foo));
        $key = 1;
        $row = "bla";
        $list->serializeField($row, $key);
    }

    /**
     * @expectedException \PerrysLambda\Exception\SerializerException
     */
    public function testItemConverterDeserializerFieldInvalidReturntype()
    {
        $list = new \PerrysLambda\Converter\ItemConverter();
        $foo = function() {  };
        $list->setSerializer(new PerrysLambda\Serializer\Serializer($foo, $foo));
        $list->setFieldSerializer(1, new PerrysLambda\Serializer\Serializer($foo, $foo));
        $key = 1;
        $row = "bla";
        $list->deserializeField($row, $key);
    }

    /**
     * @expectedException \PerrysLambda\Exception\SerializerException
     */
    public function testItemConverterSerializerException()
    {
        $ic = new \PerrysLambda\Converter\ItemConverter();
        $this->assertNull($ic->getFieldSerializer('kajfhkaf'));
        $ic->setFieldSerializers(array('foo'=>'bar'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTakeInvalidArgument()
    {
        $test = new ArrayList();
        $test->take(1.5);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSkipInvalidArgument()
    {
        $test = new ArrayList();
        $test->skip(1.5);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSkipOutOfBounds()
    {
        $test = new ArrayList();
        $test->skip(1);
    }

    /**
     * @expectedException \PerrysLambda\Exception\InvalidException
     */
    public function testCallableBoolean()
    {
        \PerrysLambda\LambdaUtils::toSelectCallable(true);
    }

    /**
     * @expectedException \PerrysLambda\Exception\InvalidTypeException
     */
    public function testDirectoryInvalid()
    {
        \PerrysLambda\IO\DirectoryConverter::fromPath(new \PerrysLambda\IO\File(__FILE__));
    }

    /**
     * @expectedException \PerrysLambda\IO\CsvParseException
     */
    public function testLineIteratorParseException()
    {
        $file = new PerrysLambda\IO\File('./nonexistentfil-askjfhaf');
        new PerrysLambda\IO\LineIterator($file);
    }


}
