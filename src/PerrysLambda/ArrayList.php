<?php

namespace PerrysLambda;

class ArrayList extends ArrayBase
{

    /**
     * New ArrayList with fixed item type
     * @param string $type
     * @param array $data
     * @return \PerrysLambda\ArrayList
     */
    public static function asType($type, array $data)
    {
        return new static($data, $type);
    }

    /**
     * New ArrayList with ObjectArrays as items
     * @param array $data
     * @return \PerrysLambda\ArrayList
     */
    public static function asObjectArray(array $data)
    {
        return new static($data, '\PerrysLambda\ObjectArray');
    }

    /**
     * Is field key valid
     * @param mixed $name
     * @return bool
     */
    public function getIsValidKey($name)
    {
        return is_int($name);
    }

    /**
     * Is field value valid
     * @param mixed $value
     * @return boolean
     */
    public function getIsValidValue($value)
    {
        if(is_null($this->__fieldtype))
        {
            return true;
        }
        return is_a($value, $this->__fieldtype);
    }

}
