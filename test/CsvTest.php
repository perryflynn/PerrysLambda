<?php

use PerrysLambda\IO\File;
use PerrysLambda\IO\CsvParser;
use PerrysLambda\ObjectArrayConverter;
use PerrysLambda\ArrayList;

class CsvTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \PerrysLambda\IO\CsvParseException
     */
    public function testInvalidParse()
    {
        $file = __DIR__."/../examples/testdata.csv";
        $conv = new ObjectArrayConverter();
        
        $parser = new CsvParser();
        $parser->setValidate(true);
        $conv->setIteratorSource($parser->openFile(new File($file)));
        new ArrayList($conv);
    }
    
    public function testCsvParser()
    {
        $file = __DIR__."/../examples/testdata.csv";
        $conv = new ObjectArrayConverter();
        
        $parser = new CsvParser();
        $conv->setIteratorSource($parser->openFile(new File($file)));
        
        $records = new ArrayList($conv);
        //$records->each(function($r) { var_dump($r->toArray()); });
        
        $this->assertSame(7, $records->length());
        $this->assertSame(2, $records->where(function($v) { return $v->Col1=="Bar"; })->length());
        $this->assertSame(6, $records->max(function($v) { return $v->length(); }));
    }

}
