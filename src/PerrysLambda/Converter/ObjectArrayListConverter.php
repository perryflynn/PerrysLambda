<?php

namespace PerrysLambda\Converter;

use PerrysLambda\Serializer\ObjectArraySerializer;


class ObjectArrayListConverter extends ListConverter
{

    public function __construct()
    {
        parent::__construct();
        $this->setSerializer(new ObjectArraySerializer());
    }

}
