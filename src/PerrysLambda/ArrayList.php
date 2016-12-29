<?php

namespace PerrysLambda;

use PerrysLambda\Converter\ObjectArrayListConverter;
use PerrysLambda\Converter\TypeStringListConverter;

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
        $converter = new ObjectArrayListConverter();
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
        $converter = new TypeStringListConverter($type);
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
        return is_int($name) && $name>=0;
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
