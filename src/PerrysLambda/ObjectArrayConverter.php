<?php

namespace PerrysLambda;

use \PerrysLambda\Serializer as Ser;
use \PerrysLambda\ObjectArray as OA;


class ObjectArrayConverter extends Converter
{

    public function __construct()
    {
        parent::__construct();

        $serializer = function(&$row, &$key)
        {
            if($row instanceof OA)
            {
                $row = $row->toArray();
            }
        };

        $deserializer = function(&$row, &$key)
        {
            if(!($row instanceof OA))
            {
                $row = new OA($row);
            }
        };

        $this->setRowConverter(new Ser($serializer, $deserializer));
    }

}