<?php

namespace PerrysLambda;

class ScalarProperty extends Property
{
   
    public function getIsValidData($val) 
    {
        return is_scalar($val) || is_null($val);
    }
   
    public function isNumeric()
    {
        return is_numeric($this->getData());
    }
   
    public function isFloat()
    {
        return is_float($this->getData());
    }
   
    public function isInt()
    {
        return is_int($this->getData());
    }
   
    public function isString()
    {
        return is_string($this->getData());
    }
   
    public function isBool()
    {
        return is_bool($this->getData());
    }
   
    public function isNull()
    {
        return is_null($this->getData());
    }
   
    public function toString()
    {
        return "".$this->getData();
    }
   
    public function __toString() 
    {
        return $this->toString();
    }
   
    public function toNumeric()
    {
        return $this->getData()+0;
    }
   
    public function toInt()
    {
        return ((int)$this->getData());
    }
   
    public function toFloat()
    {
        return ((float)$this->getData());
    }
   
    public function toBool()
    {
        return ($this->getData() ? true : false);
    }
   
    public function length()
    {
        return strlen($this->toString());
    }
   
    public function substr($start, $length=null)
    {
        return substr($this->toString(), $start, $length);
    }
   
    public function split($separator)
    {
        return explode($separator, $this->toString());
    }
   
    public function indexOf($needle)
    {
        $r = strpos($this->toString(), $needle);
        return ($r===false ? -1 : $r);
    }
   
    public function lastIndexOf($needle)
    {
        $r = strrpos($this->toString(), $needle);
        return ($r===false ? -1 : $r);
    }
    
    public function contains($needle)
    {
        return $this->indexOf($needle)>=0;
    }
   
    public function startsWith($needle)
    {
        return $this->indexOf($needle)===0;
    }
   
    public function endsWith($needle)
    {
        // http://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen <= $strlen)
        {
            return substr_compare($string, $test, $strlen - $testlen, $testlen)===0;
        }
        return false;
    }
   
}
