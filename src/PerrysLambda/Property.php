<?php

namespace PerrysLambda;

/**
 * Baseclass of this project
 */
class Property
{

    /**
     * Data store
     * @var mixed
     */
    protected $__data = null;


    /**
     * Constructor
     * @param mixed $data
     */
    public function __construct($data=null)
    {
        if(!is_null($data))
        {
            $this->setData($data);
        }
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

    /**
     * Get current classname as string
     * @return string
     */
    protected function getClassName()
    {
        return get_called_class();
    }

    /**
     * Creates new instance of current class type
     * @return \PerrysLambda\Property
     */
    public function newInstance($value=null)
    {
        $class = $this->getClassName();
        $o = new $class($value);
        return $o;
    }

}
