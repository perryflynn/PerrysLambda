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

    /**
     * @expectedException \PerrysLambda\IO\CsvParseException
     */
    public function testInvalidFile()
    {
        $parser = new CsvParser();
        $parser->setValidate(true);
        $parser->openFile(new File('kladsjshfkashfkafhfakhf.txt'));
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
        
        unset($records);
    }

    public function testCsvParserIterSkip()
    {
        $file = __DIR__."/../examples/testdata.csv";

        $parser = new CsvParser();

        $conv = new ObjectArrayConverter();
        $conv->setIteratorSource($parser->openFile(new File($file)), 2);
        $records = new ArrayList($conv);

        $conv2 = new ObjectArrayConverter();
        $conv2->setIteratorSource($parser->openFile(new File($file)), 2, 4);
        $records2 = new ArrayList($conv2);

        $this->assertSame('Hihi', $records->first()->Col2);
        $this->assertSame('Bar', $records->last()->Col1);
        $this->assertSame('Hihi', $records2->first()->Col2);
        $this->assertSame('An', $records2->last()->Col1);
    }

}
