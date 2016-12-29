<?php

namespace PerrysLambda;

interface IConverter
{

    public function newInstance();
    public function importInto(ArrayBase $collection);
    public function exportFromAsGenerator(ArrayBase $collection);
    public function exportFromAsArray(ArrayBase $collection);
    public function serializeRow(&$row, &$key);
    public function serializeField(&$field, &$fieldkey);
    public function deserializeRow(&$row, &$key);
    public function deserializeField(&$field, &$fieldkey);

}
