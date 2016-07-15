<?php

namespace PerrysLambda\FieldSerializer;

class Number extends \PerrysLambda\Serializer
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
