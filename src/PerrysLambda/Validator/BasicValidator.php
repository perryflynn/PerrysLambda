<?php

namespace PerrysLambda\Validator;

abstract class BasicValidator
{

    protected $message = "Invalid value";

    public function __construct($message=null)
    {
        if(!is_null($message))
        {
            $this->message = $message;
        }
    }

    abstract public function validate($name, $value, \PerrysLambda\ArrayList $r);

    public function getMessage()
    {
        return $this->message;
    }

}
