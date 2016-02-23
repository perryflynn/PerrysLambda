<?php

class Test extends PHPUnit_Framework_TestCase
{
    
    public function testScalar()
    {
        $s = new \PerrysLambda\ScalarProperty("Zähn € zahme Ziegen zögen zwei Zentner Zücker zum Zoö!");
        $this->assertSame($s->startsWith("Zä"), true);
    }
    
    public function testLambda()
    {
        $data = json_decode(file_get_contents(__DIR__."/../examples/testdata.json"), true);
        $collection = \PerrysLambda\ArrayList::asObjectArray($data);
        $this->assertSame($collection->take(1)->length(), 1);
    }
    
}
