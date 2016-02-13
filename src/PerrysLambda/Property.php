<?php

namespace PerrysLambda;

class Property
{

    protected $__data = null;

    public function __construct($data=null)
    {
        if(!is_null($data))
        {
            $this->setData($data);
        }
    }

    protected function newInstance()
    {
        $class = get_called_class();
        return new $class();
    }

    /**
     * Validate data
     * @param mixed $val
     * @return boolean
     */
    public function getIsValidData($val)
    {
        return true;
    }

    /**
     * Set data
     * @param mixed $data
     * @throws InvalidDataException
     */
    public function setData($data)
    {
        if(!$this->getIsValidData($data))
        {
            throw new InvalidDataException();
        }
        $this->__data = $data;
    }

    /**
     * Get data
     * @return mixed
     */
    public function getData()
    {
        return $this->__data;
    }

}
