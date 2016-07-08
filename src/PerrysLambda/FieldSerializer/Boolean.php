<?php

namespace PerrysLambda\FieldSerializer;

class Boolean extends \PerrysLambda\Serializer
{

    public function __construct()
    {
        $serializer = function(&$value, &$key)
        {
            $value = ($value===true ? "true" : "false");
        };
        
        $deserializer = function(&$value, &$key)
        {
            $value = ($value=="true");
        };
        
        parent::__construct($serializer, $deserializer);
    }
    
}
