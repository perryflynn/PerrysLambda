<?php

use PerrysLambda\IO\File;
use PerrysLambda\IO\CsvParser;
use PerrysLambda\Converter\ObjectArrayListConverter;
use PerrysLambda\ArrayList;
use PerrysLambda\Converter\ListConverter;
use PerrysLambda\IO\LineIterator;

class CsvTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \PerrysLambda\IO\CsvParseException
     */
    public function testInvalidParse()
    {
        $file = __DIR__."/../examples/testdata.csv";
        $conv = new ObjectArrayListConverter();

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
        $conv = new ObjectArrayListConverter();

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

        $allconv = new ObjectArrayListConverter();
        $allconv->setIteratorSource($parser->openFile(new File($file)));
        $allrecords = new ArrayList($allconv);

        $conv = new ObjectArrayListConverter();
        $conv->setIteratorSource($parser->openFile(new File($file)), 2);
        $records = new ArrayList($conv);

        $conv2 = new ObjectArrayListConverter();
        $conv2->setIteratorSource($parser->openFile(new File($file)), 2, 4);
        $records2 = new ArrayList($conv2);

        $this->assertSame('Hihi', $records->first()->Col2);
        $this->assertSame('Bar', $records->last()->Col1);
        $this->assertSame('Hihi', $records2->first()->Col2);
        $this->assertSame('An', $records2->last()->Col1);
        $this->assertSame(5, $records->length());
        $this->assertSame(7, $allrecords->length());
        $this->assertSame(3, $records2->length());
    }
    
    public function testLineIterator()
    {
        $iterator = new LineIterator(new File(__DIR__."/../examples/testdata.txt"));
        $c = $iterator->count();
        
        $conv = new ListConverter();
        $conv->setIteratorSource($iterator, $c-500);
        $list = new ArrayList($conv);
        
        $this->assertSame(500, $list->length());
        
        $conv2 = new ListConverter();
        $conv2->setIteratorSource($iterator, 0, 499);
        $list2 = new ArrayList($conv2);
        
        $this->assertSame(500, $list2->length());
    }

}
