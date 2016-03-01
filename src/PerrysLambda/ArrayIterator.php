<?php

namespace PerrysLambda;

class ArrayIterator implements \SeekableIterator, \ArrayAccess
{

    protected $__iterator = 0;
    protected $__data;
    protected $__keys;

    public function __construct(array $data=array())
    {
        $this->__data = $data;
        $this->__keys = array_keys($this->__data);
    }

    public function current()
    {
        return $this->__data[$this->__iterator];
    }

    public function key()
    {
        return $this->__keys[$this->__iterator];
    }

    public function next()
    {
        $this->__iterator++;
    }

    public function rewind()
    {
        $this->__iterator=0;
    }

    public function valid()
    {
        return isset($this->__data[$this->__iterator]);
    }

    public function seek($position)
    {
        $this->__iterator = $position;
    }

    public function offsetExists($offset)
    {
        return isset($this->__data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->__data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if(is_null($offset))
        {
            $this->__data[] = $value;
        }
        else
        {
            $this->__data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->__data[$offset]);
    }

}
