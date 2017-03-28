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
    
    /**
     * Serialize via converter
     * @return mixed[]
     */
    public function serialize()
    {
        if($this->__converter instanceof IListConverter)
        {
            return $this->__converter->toArray($this);
        }
        else
        {
            return $this->toArray();
        }
    }

    /**
     * Serialize via converter as generator
     * @return mixed[]
     */
    public function serializeGenerator()
    {
        if($this->__converter instanceof IListConverter)
        {
            foreach($this->__converter->toGenerator($this) as $index => $row)
            {
                yield $index => $row;
            }
        }
        else
        {
            foreach($this->toArray() as $index => $row)
            {
                yield $index => $row;
            }
        }
    }

}
