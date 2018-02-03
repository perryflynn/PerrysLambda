<?php

namespace PerrysLambda;

use PerrysLambda\Exception\InvalidException;


class LambdaUtils
{

    /**
     * Check the given variable is a closure
     * @param mixed $var
     * @return bool
     */
    public static function isClosure($var)
    {
        return is_object($var) && ($var instanceof \Closure);
    }


    /**
     * Check the given variable is a callable for a instance method
     * example: array($obj, "someMethod");
     * @param mixed $var
     * @return bool
     */
    public static function isInstanceCallable($var)
    {
        return is_array($var) && count($var)===2 &&
            array_key_exists(0, $var) && array_key_exists(1, $var) &&
            is_object($var[0]) && is_string($var[1]) &&
            method_exists($var[0], $var[1]);
    }


    /**
     * Converts strings, empty strings and NULL into a callable
     * @param string|callable|null $mixed
     * @return callable
     * @throws \PerrysLambda\Exception\InvalidException
     */
    public static function toSelectCallable($mixed=null)
    {
        // callable
        if(self::isClosure($mixed) || self::isInstanceCallable($mixed))
        {
            return $mixed;
        }
        // nothing, return full row
        elseif(is_null($mixed) || $mixed==="")
        {
            return function($v) { return $v; };
        }
        // row field from string
        elseif(is_int($mixed) || is_float($mixed) || is_string($mixed))
        {
            return function($v) use($mixed)
            {
                if(is_object($v))
                {
                    if(method_exists($v, $mixed))
                    {
                        return $v->$mixed();
                    }
                    else
                    {
                        return $v->$mixed;
                    }
                }
                else if(is_array($v) || ($v instanceof \ArrayAccess))
                {
                    return $v[$mixed];
                }
            };
        }

        throw new InvalidException("Could not convert expression of type ".gettype($mixed)." into a lambda callable");
    }


    /**
     * Convert strings, numbers, booleans and arrays into a callable
     * @param type $mixed
     * @return type
     * @throws InvalidException
     */
    public static function toConditionCallable($mixed=null)
    {
        // callable
        if(self::isClosure($mixed) || self::isInstanceCallable($mixed))
        {
            return $mixed;
        }
        // is bool
        elseif(is_bool($mixed) || is_string($mixed) || is_numeric($mixed))
        {
            return function($v) use($mixed) { return $v===$mixed; };
        }
        // conditions from array
        elseif(is_array($mixed) && count($mixed)>0)
        {
            return function($v) use($mixed)
            {
                foreach($mixed as $field => $expression)
                {
                    if(self::isClosure($expression) || self::isInstanceCallable($expression))
                    {
                        if(call_user_func($expression, $v[$field])!==true)
                        {
                            return false;
                        }
                    }
                    else
                    {
                        if((is_array($v) || $v instanceof \ArrayAccess) &&
                           $v[$field]!==$expression)
                        {
                            return false;
                        }
                    }
                }
                return true;
            };
        }

        throw new InvalidException("Could not convert expression of type ".gettype($mixed)." into a lambda callable");
    }

}
