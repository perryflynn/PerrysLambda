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

class CustomConverterTest extends PHPUnit_Framework_TestCase
{

    protected function createTestdata()
    {
        $length = 1000;
        $titles = array("foo", "bar", "foobar", "barfoo", "test");

        $generators = array(
            function() { return mt_rand(0, 999999999); },
            function() { return microtime(true); },
            function() { return mt_rand(0,1)===1 ? "true" : "false"; },
            function() { return (new \DateTime())->add(new \DateInterval("PT".mt_rand(0, 999999999)."S"))->format(\DateTime::ISO8601); },
            function() { $f = array("Alice", "Bob", "Franz", "Carl", "Timmy", "Gadget", "Ivan"); shuffle($f); return $f[0]; },
        );

        $data = array();
        for($i=0; $i<$length; $i++)
        {
            $temp = array();
            foreach($titles as $titlei => $title)
            {
                $gen = $generators[$titlei % count($generators)];
                $temp[$title] = $gen();
            }
            $data[] = $temp;
        }

        return $data;
    }

    protected function createJsonLinesString()
    {
        $lines = "";
        foreach($this->createTestdata() as $row)
        {
            $lines .= json_encode($row)."\r\n";
        }
        return trim($lines);
    }

    public function testJsonLineParser()
    {
        // Testdata
        $rawstring = $this->createJsonLinesString();

        // Split into lines
        $rawlines = explode("\r\n", $rawstring);

        // Parse lines to one array with fields per line
        $deserializer = function(&$row, &$key, IItemConverter $converter)
        {
            if(is_string($row))
            {
                $data = json_decode($row, true);
                if(is_array($data))
                {
                    $row = new ObjectArray($data);
                }
                else
                {
                    return false;
                }
            }
            return true;
        };

        $serializer = function(&$row, &$key, IItemConverter $converter)
        {
            if($row instanceof IArrayable)
            {
                $row = $row->toArray();
            }

            if(is_array($row))
            {
                $row = json_encode($row);
                if(is_string($row))
                {
                    return true;
                }
            }
            return false;
        };

        // Field serializers
        $fieldconverter = new ItemConverter();
        $fieldconverter->setSerializer(new Serializer($serializer, $deserializer));
        $fieldconverter->setFieldSerializers(array(
            "barfoo" => DateTimeSerializer::fromIsoFormat(),
            "foobar" => new BooleanSerializer(),
        ));

        // Create converter
        $conv = new ListConverter();
        $conv->setItemConverter($fieldconverter);
        $conv->setArraySource($rawlines);

        $list = new ArrayList($conv);

        // Serialize single and compare with original
        $temp = $list->newInstance();
        $temp->add($list->first());
        $tempserialized = $temp->serialize();

        $firstrow = $rawlines[0];
        $this->assertSame($firstrow, $tempserialized[0]);

        // Serialize single without list
        $this->assertSame($firstrow, $list->first()->serialize());

        // Grouping test
        $grouping = $list->groupBy(function($r) { return "fake"; });
        $tempgroupser = $grouping->fake->serialize();
        $this->assertSame($firstrow, $tempgroupser[0]);
    }

}
