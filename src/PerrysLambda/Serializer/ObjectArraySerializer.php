<?php

namespace PerrysLambda\Serializer;

use PerrysLambda\Serializer\Serializer;
use \PerrysLambda\ObjectArray as OA;
use PerrysLambda\IListConverter;
use PerrysLambda\IItemConverter;


class ObjectArraySerializer extends Serializer
{

    public function __construct()
    {
        $serializer = function(&$row, &$key, IItemConverter $converter)
        {
            if($row instanceof OA)
            {
                $row = $row->toArray();
                return $converter->serializeFields($row, $key);
            }
            return true;
        };

        $deserializer = function(&$row, &$key)
        {
            if(is_array($row) || $row instanceof IListConverter)
            {
                $row = new OA($row);
            }
            return true;
        };

        parent::__construct($serializer, $deserializer);
    }

}
