<?php

namespace PerrysLambda;

use \PerrysLambda\Serializer as Ser;
use \PerrysLambda\ObjectArray as OA;


class ObjectArraySerializer extends Ser
{

    public function __construct()
    {
        $serializer = function(&$row, &$key)
        {
            if($row instanceof OA)
            {
                $row = $row->toArray();
            }
            return true;
        };

        $deserializer = function(&$row, &$key)
        {
            if(!($row instanceof OA))
            {
                $row = new OA($row);
            }
            return true;
        };

        parent::__construct($serializer, $deserializer);
    }

}
