<?php

namespace PerrysLambda;

use PerrysLambda\ArrayBase;


interface IListConverter extends IBaseConverter
{

    public function importInto(ArrayBase $collection);
    public function getItemConverter();
    public function toArray(ArrayBase $collection);
    public function toGenerator(ArrayBase $collection);

}
