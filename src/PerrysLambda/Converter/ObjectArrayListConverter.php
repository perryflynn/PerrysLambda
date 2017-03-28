<?php

namespace PerrysLambda\Converter;

use PerrysLambda\Serializer\ObjectArraySerializer;
use PerrysLambda\Converter\ItemConverter;


class ObjectArrayListConverter extends ListConverter
{
    
    protected static $serializerinstance;
    
    public function __construct()
    {
        if(is_null(self::$serializerinstance))
        {
            self::$serializerinstance = new ObjectArraySerializer();
        }
        
        parent::__construct();
        $ic = new ItemConverter();
        $ic->setSerializer(self::$serializerinstance);
        $this->setItemConverter($ic);
    }

}
