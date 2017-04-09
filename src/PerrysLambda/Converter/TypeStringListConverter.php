<?php

namespace PerrysLambda\Converter;

use PerrysLambda\Serializer\Serializer;
use PerrysLambda\Converter\ListConverter;
use PerrysLambda\Exception\InvalidTypeException;
use PerrysLambda\IArrayable;
use PerrysLambda\Converter\ItemConverter;


class TypeStringListConverter extends ListConverter
{

    /**
     * The target object type
     * @var string
     */
    protected $type;


    public function __construct($type)
    {
        $this->type = $type;
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

        $ic = new ItemConverter();
        $ic->setSerializer(new Serializer($serializer, $deserializer));

        $this->setItemConverter($ic);
    }


    /**
     * Create a new instance
     * @return \PerrysLambda\Converter\TypeStringListConverter
     */
    public function newInstance()
    {
        $class = get_called_class();
        $instance = new $class($this->type);
        $instance->setSerializer($this->getSerializer());
        $instance->setItemConverter($this->getItemConverter()->newInstance());
        $instance->setDefaults($this->defaults);
        return $instance;
    }

}
