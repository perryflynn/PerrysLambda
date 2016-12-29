<?php

namespace PerrysLambda;

interface IBaseConverter extends ICloneable
{

    public function serialize(&$row, &$key);
    public function deserialize(&$row, &$key);
    
}
