<?php

namespace PerrysLambda;

/**
 * Array-Type with alphanumeric index
 */
class ObjectArray extends ArrayBase
{

    protected function &getFieldOrScalar($name)
    {
        if($this->exists($name)!==true && strlen($name)>6)
        {
            // foobarScalar => $this->getScalar('foobar')
            $scalarname = substr($name, 0, -6);
            $scalar = substr($name, -6);
            if($scalar=="Scalar" && $this->exists($scalarname))
            {
                $temp = $this->getScalar($scalarname);
                return $temp;
            }
        }
        $temp = &$this->get($name);
        return $temp;
    }


    /**
     * Check for string, numeric or null as key
     * @param mixed $name
     * @return boolean
     */
    public function getIsValidKey($name)
    {
        return is_string($name) || is_numeric($name) || is_null($name);
    }

    /**
     * Check for anything as value
     * Returns only true
     * @param mixed $value
     * @return boolean
     */
    public function getIsValidValue($value)
    {
        return true;
    }

    /**
     * Magic method for object access to data
     * Do not call this method directly
     * @param mixed $name
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->exists($name);
    }

    /**
     * Magic method for object access to data
     * Do not call this method directly
     * @param mixed $name
     * @return mixed
     */
    public function &__get($name)
    {
        return $this->getFieldOrScalar($name);
    }

    /**
     * Magic method for method access to data
     * Do not call this method directly
     * @param mixed $name
     * @return mixed
     */
    public function __invoke($name)
    {
        return $this->getFieldOrScalar($name);
    }

    /**
     * Magic method for object access to data
     * Do not call this method directly
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

}
