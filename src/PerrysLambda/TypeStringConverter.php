<?php

namespace PerrysLambda;
use \PerrysLambda\Serializer as Ser;

class TypeStringConverter extends Converter
{

    public function __construct($type)
    {
        parent::__construct();

        if(!class_exists($type))
        {
            throw new InvalidTypeException();
        }

        $serializer = function(&$row, &$key)
        {
            if(is_object($row) && $row instanceof \PerrysLambda\IArrayable)
            {
                $row = $row->toArray();
            }
            elseif(is_object($row))
            {
                throw new \Exception("Row object must implement \\PerrysLambda\\IArrayable");
            }
            elseif(!is_array())
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

        $this->setRowConverter(new Ser($serializer, $deserializer));
    }

}
