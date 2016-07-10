<?php

namespace PerrysLambda\FieldSerializer;

class DateTime extends \PerrysLambda\Serializer
{

    public static function fromIsoFormat()
    {
        return new static(\DateTime::ISO8601);
    }

    public function __construct($format, \DateTimeZone $timezone=null)
    {
        $serializer = function(&$value, &$key) use($format)
        {
            if($value instanceof \DateTime)
            {
                $value = $value->format($format);
            }
        };

        $deserializer = function(&$value, &$key) use($format, $timezone)
        {
            if(!($value instanceof \DateTime))
            {
                $value = \DateTime::createFromFormat($format, $value);
                if($timezone instanceof \DateTimeZone)
                {
                    $value->setTimezone($timezone);
                }
            }
        };

        parent::__construct($serializer, $deserializer);
    }

}
