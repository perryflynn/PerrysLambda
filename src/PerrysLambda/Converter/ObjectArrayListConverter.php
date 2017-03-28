<?php

namespace PerrysLambda\Converter;

use PerrysLambda\Serializer\ObjectArraySerializer;
use PerrysLambda\Converter\ItemConverter;


class ObjectArrayListConverter extends ListConverter
{

    public function __construct()
    {
        parent::__construct();
        $ic = new ItemConverter();
        $ic->setSerializer(new ObjectArraySerializer());
        $this->setItemConverter($ic);
    }

}
