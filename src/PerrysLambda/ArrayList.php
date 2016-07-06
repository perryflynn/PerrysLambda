<?php

namespace PerrysLambda;

/**
 * Array-Type with numeric index
 */
class ArrayList extends ArrayBase
{

    /**
     * Create arraylist of objectarray
     * @param mixed[][] $data
     * @return \PerrysLambda\ArrayList
     */
    public static function asObjectArray(array $data)
    {
        $converter = new ObjectArrayConverter();
        $converter->setArraySource($data);
        return new static($converter);
    }

    /**
     * Create arraylist of class
     * @param string $type
     * @param mixed[] $data
     * @return \PerrysLambda\ArrayList
     */
    public static function asType($type, array $data)
    {
        $converter = new TypeStringConverter($type);
        $converter->setArraySource($data);
        return new static($converter);
    }

    /**
     * Is field key a valid integer
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
        return true;
    }

}
