<?php

namespace PerrysLambda\Serializer;

class BooleanSerializer extends Serializer
{

    public function __construct()
    {
        $serializer = function(&$value, &$key)
        {
            $value = ($value===true ? "true" : "false");
            return true;
        };

        $deserializer = function(&$value, &$key)
        {
            $value = ($value==="true" || $value===true || $value===1);
            return true;
        };

        parent::__construct($serializer, $deserializer);
    }

}
