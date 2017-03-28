<?php

namespace PerrysLambda\Serializer;

class BooleanSerializer extends Serializer
{

    public function __construct()
    {
        $serializer = function(&$value, &$key)
        {
            if($value!=="true" && $value!=="false")
            {
                $value = ($value===true ? "true" : "false");
            }
            return true;
        };

        $deserializer = function(&$value, &$key)
        {
            if($value!==true && $value!==false)
            {
                $value = ($value==="true" || $value===true || $value===1);
            }
            return true;
        };

        parent::__construct($serializer, $deserializer);
    }

}
