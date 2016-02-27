<?php

namespace PerrysLambda;

class StringProperty extends Property
{

    protected $encoding;


    /**
     * Constructor
     * @param mixed $data
     * @param string $encoding
     */
    public function __construct($data = null, $encoding='UTF-8')
    {
        $this->encoding = $encoding;
        parent::__construct($data);
    }

    /**
     * Check for scalar or null as valid data type
     * @param mixed $val
     * @return boolean
     */
    public function getIsValidData($val)
    {
        return is_string($val);
    }

    /**
     * Cast to string
     * @return string
     */
    public function toString()
    {
        return "".$this->getData();
    }

    /**
     * Magic toString method
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Helper function to set default encoding
     * @param string $encoding
     * @return string
     */
    protected function getEncoding($encoding=null)
    {
        if(is_null($encoding))
        {
            if(!is_null($this->encoding))
            {
                return $this->encoding;
            }
            return mb_internal_encoding();
        }
        return $encoding;
    }

    /**
     * Return the length of a string
     * @return string
     */
    public function length()
    {
        return mb_strlen($this->toString(), $this->getEncoding());
    }

    /**
     * Return a substring of value
     * @param int $start
     * @param int $length
     * @return string
     */
    public function substr($start, $length=null)
    {
        return mb_substr($this->toString(), $start, $length, $this->getEncoding());
    }

    /**
     * Split string by separator
     * @param string $separator
     * @return array
     */
    public function split($separator)
    {
        return explode($separator, $this->toString());
    }

    /**
     * The first position of a substring in this string
     * Returns -1 if substring not found
     * @param string $needle
     * @param int $offset
     * @return int
     */
    public function indexOf($needle, $offset=0)
    {
        $r = mb_strpos($this->toString(), $needle, $offset, $this->getEncoding());
        return ($r===false ? -1 : $r);
    }

    /**
     * This last position if a substring in this string
     * Returns -1 if substring not found
     * @param string $needle
     * @param int $offset
     * @return int
     */
    public function lastIndexOf($needle, $offset=0)
    {
        $r = mb_strrpos($this->toString(), $needle, $offset, $this->getEncoding());
        return ($r===false ? -1 : $r);
    }

    /**
     * Contains this string the griven substring
     * @param string $needle
     * @return boolean
     */
    public function contains($needle)
    {
        return $this->indexOf($needle)>=0;
    }

    /**
     * Begins this string with the given substring
     * @param string $needle
     * @return boolean
     */
    public function startsWith($needle)
    {
        return $this->indexOf($needle)===0;
    }

    /**
     * Ends this string with the given substring
     * @param string $needle
     * @return boolean
     */
    public function endsWith($needle)
    {
        $enc = $this->getEncoding();
        $strlen = $this->length($enc);
        $testlen = mb_strlen($needle, $enc);

        if ($testlen <= $strlen)
        {
            return (mb_strpos($this->toString(), $needle, ($strlen-$testlen), $enc)!==false);
        }
        return false;
    }

}
