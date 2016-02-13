<?php

namespace PerrysLambda;

class ObjectArray extends ArrayList implements \ArrayAccess, \SeekableIterator
{

    public function getIsValidKey($name)
    {
        return is_string($name) || is_numeric($name) || is_null($name);
    }

    public function __get($name)
    {
        if($this->exists($name)!==true && strlen($name)>6)
        {
            // foobarScalar = $this->getScalar('foobar')
            $scalarname = substr($name, 0, -6);
            $scalar = substr($name, -6);
            if($scalar=="Scalar" && $this->exists($scalarname))
            {
                return $this->getScalar($scalarname);
            }
        }
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($value, $name);
    }

}
