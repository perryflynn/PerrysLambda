<?php

namespace PerrysLambda\Serializer;

use PerrysLambda\Serializer\Serializer;
use \PerrysLambda\ObjectArray as OA;


class ObjectArraySerializer extends Serializer
{

    public function __construct()
    {
        $serializer = function(&$row, &$key)
        {
            if($row instanceof OA)
            {
                $row = $row->serialize();
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
