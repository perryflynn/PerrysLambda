<?php

namespace PerrysLambda\Serializer;

class DummySerializer extends Serializer
{

    public function __construct()
    {
        $serializer = function(&$value, &$key)
        {
            return true;
        };

        $deserializer = function(&$value, &$key)
        {
            return true;
        };

        parent::__construct($serializer, $deserializer);
    }
    
}
