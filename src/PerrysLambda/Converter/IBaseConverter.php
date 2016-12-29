<?php

namespace PerrysLambda\Converter;

interface IBaseConverter extends \PerrysLambda\ICloneable
{

    public function serialize(&$row, &$key);
    public function deserialize(&$row, &$key);
    
}
