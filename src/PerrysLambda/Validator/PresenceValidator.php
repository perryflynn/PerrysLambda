<?php

namespace PerrysLambda\Validator;

class PresenceValidator extends BasicValidator
{

    public function validate($name, $value, \PerrysLambda\ArrayBase $r)
    {
        if(is_null($value) || (is_string($value) && $value==="") ||
            ((is_int($value) || is_float($value)) && $value<=0) || (is_bool($value) && $value===false))
        {
            return false;
        }
        return true;
    }

}
