<?php

namespace PerrysLambda;

interface IItemConverter extends IConverterDeserializer, IBaseConverter
{

    public function deserializeAll(&$listitem, &$listitemkey);
    public function serializeAll(&$listitem, &$listitemkey);
    public function deserializeField(&$row, &$key);
    public function serializeField(&$row, &$key);

}
