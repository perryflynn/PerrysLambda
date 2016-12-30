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
    public static function toCallable($mixed=null)
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
        elseif(is_string($mixed))
        {
            return function($v) use($mixed)
            {
                if(is_object($v))
                {
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

}
