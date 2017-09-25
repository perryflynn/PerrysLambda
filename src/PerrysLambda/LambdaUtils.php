<?php

namespace PerrysLambda;

use PerrysLambda\Exception\InvalidException;


class LambdaUtils
{

    /**
     * Converts strings, empty strings and NULL into a callable
     * @param string|callable|null $mixed
     * @return callable
     * @throws \PerrysLambda\Exception\InvalidException
     */
    public static function toSelectCallable($mixed=null)
    {
        // callable
        if(is_callable($mixed))
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
                    return $v->$mixed;
                }
                else
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
        if(is_callable($mixed))
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
                    if(is_callable($expression))
                    {
                        if($expression($v[$field])!==true)
                        {
                            return false;
                        }
                    }
                    else
                    {
                        if($v[$field]!==$expression)
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
