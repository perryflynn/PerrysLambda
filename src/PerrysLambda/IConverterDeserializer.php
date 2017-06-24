<?php

namespace PerrysLambda;

interface IConverterDeserializer
{

    public function deserialize(&$row, &$key);

}
