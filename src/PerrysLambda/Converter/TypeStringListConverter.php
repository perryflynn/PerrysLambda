<?php

namespace PerrysLambda\Converter;

use PerrysLambda\Serializer\Serializer;
use PerrysLambda\Converter\ListConverter;
use PerrysLambda\Exception\InvalidTypeException;
use PerrysLambda\IArrayable;


class TypeStringListConverter extends ListConverter
{

    public function __construct($type)
    {
        parent::__construct();

        if(!class_exists($type))
        {
            throw new InvalidTypeException("Typ ".$type." not found");
        }

        $serializer = function(&$row, &$key)
        {
            if(is_object($row) && $row instanceof IArrayable)
            {
                $row = $row->toArray();
            }
            elseif(is_object($row))
            {
                throw new \Exception("Row object must implement \\PerrysLambda\\IArrayable");
            }
            elseif(!is_array($row))
            {
                $row = array('row'=>$row);
            }
            return true;
        };

        $deserializer = function(&$row, &$key) use($type)
        {
            if(!is_a($row, $type))
            {
                $row = new $type($row);
            }
            return true;
        };

        $this->setSerializer(new Serializer($serializer, $deserializer));
    }

}
