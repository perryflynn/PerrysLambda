<?php

namespace PerrysLambda\FieldSerializer;

class DateTime extends \PerrysLambda\Serializer
{

    protected $format;
    protected $timezone;

    public static function fromIsoFormat(\DateTimeZone $timezone=null)
    {
        if(is_null($timezone))
        {
            return new static(\DateTime::ISO8601);
        }
        else
        {
            return new static(\DateTime::ISO8601, $timezone);
        }
    }
    
    public function __construct($format, \DateTimeZone $timezone=null)
    {
        $this->format = $format;
        $this->timezone = $timezone;
        
        $serializer = function(&$value, &$key)
        {
            if($value instanceof \DateTime)
            {
                $value = $value->format($this->format);
            }
        };

        $deserializer = function(&$value, &$key)
        {
            if(!($value instanceof \DateTime))
            {
                $value = \DateTime::createFromFormat($this->format, $value);
                if($this->timezone instanceof \DateTimeZone)
                {
                    $value->setTimezone($this->timezone);
                }
            }
        };

        parent::__construct($serializer, $deserializer);
    }
    
    public function setTimezone(\DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }
    
    public function setFormat($format)
    {
        $this->format = $format;
    }

}
