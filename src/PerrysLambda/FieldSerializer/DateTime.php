<?php

namespace PerrysLambda\FieldSerializer;

class DateTime extends \PerrysLambda\Serializer
{
    
    public static function fromIsoFormat()
    {
        return new static(\DateTime::ISO8601);
    }

    public function __construct($format)
    {
        $serializer = function(&$value, &$key) use($format)
        {
            if($value instanceof \DateTime)
            {
                $value = $value->format($format);
            }
        };
        
        $deserializer = function(&$value, &$key) use($format)
        {
            if(!($value instanceof \DateTime))
            {
                $value = \DateTime::createFromFormat($format, $value);
            }
        };
        
        parent::__construct($serializer, $deserializer);
    }
    
}
