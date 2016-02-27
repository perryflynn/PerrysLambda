<?php

namespace PerrysLambda;

/**
 * Stores a single value and provides string functions
 */
class ScalarProperty extends StringProperty
{

    /**
     * Check for scalar or null as valid data type
     * @param mixed $val
     * @return boolean
     */
    public function getIsValidData($val)
    {
        return is_scalar($val) || is_null($val);
    }

    /**
     * Is value numeric?
     * @return boolean
     */
    public function isNumeric()
    {
        return is_numeric($this->getData());
    }

    /**
     * Is value a float
     * @return boolean
     */
    public function isFloat()
    {
        return is_float($this->getData());
    }

    /**
     * Is value an int
     * @return boolean
     */
    public function isInt()
    {
        return is_int($this->getData());
    }

    /**
     * Is value a string
     * @return boolean
     */
    public function isString()
    {
        return is_string($this->getData());
    }

    /**
     * Is value an boolean
     * @return boolean
     */
    public function isBool()
    {
        return is_bool($this->getData());
    }

    /**
     * Is value null
     * @return boolean
     */
    public function isNull()
    {
        return is_null($this->getData());
    }

    /**
     * Cast to numeric
     * @return numeric
     */
    public function toNumeric()
    {
        return $this->getData()+0;
    }

    /**
     * Cast to int
     * @return int
     */
    public function toInt()
    {
        return ((int)$this->getData());
    }

    /**
     * Cast to float
     * @return float
     */
    public function toFloat()
    {
        return ((float)$this->getData());
    }

    /**
     * Cast to boolean
     * @return boolean
     */
    public function toBool()
    {
        return ($this->getData() ? true : false);
    }

}
