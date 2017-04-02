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
        \PerrysLambda\LambdaUtils::toCallable(true);
    }
    
    /**
     * @expectedException \PerrysLambda\Exception\InvalidTypeException
     */
    public function testDirectoryInvalid()
    {
        \PerrysLambda\IO\DirectoryConverter::fromPath(new \PerrysLambda\IO\File(__FILE__));
    }
    
    
}
