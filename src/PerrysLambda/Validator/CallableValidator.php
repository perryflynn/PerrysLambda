<?php

namespace PerrysLambda\Validator;

class CallableValidator extends BasicValidator
{

    protected $function;

    public function __construct($message, callable $function)
    {
        parent::__construct($message);
        $this->function = $function;
    }

    public function validate($name, $value, \PerrysLambda\ArrayList $r)
    {
        return call_user_func($this->function, $name, $value, $r);
    }

}
