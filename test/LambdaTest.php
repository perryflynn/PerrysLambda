<?php

class Test extends PHPUnit_Framework_TestCase
{
    
    public function testBasics()
    {
        /*
        $data = json_decode(file_get_contents(__DIR__."/../examples/testdata.json"));
        $collection = PerrysLambda\ArrayList::asObjectArray($data);
        */
        $s = new \PerrysLambda\ScalarProperty("Zähn € zahme Ziegen zögen zwei Zentner Zücker zum Zoö!");
        $this->assertSame($s->startsWith("Zä"), true);
    }
    
}
