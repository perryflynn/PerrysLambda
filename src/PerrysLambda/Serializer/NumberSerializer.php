<?php

namespace PerrysLambda\Serializer;

class NumberSerializer extends Serializer
{

    public function __construct()
    {
        $serializer = function(&$value, &$key)
        {
            $value = "".$value;
            return true;
        };

        $deserializer = function(&$value, &$key)
        {
            if(strpos($value, "."))
            {
                $value = (float)$value;
            }
            else
            {
                $value = (int)$value;
            }
            return true;
        };

        parent::__construct($serializer, $deserializer);
    }

}
