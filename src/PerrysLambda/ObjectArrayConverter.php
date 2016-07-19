<?php

namespace PerrysLambda;

class ObjectArrayConverter extends Converter
{

    public function __construct()
    {
        parent::__construct();
        $this->setRowConverter(new ObjectArraySerializer());
    }

}
