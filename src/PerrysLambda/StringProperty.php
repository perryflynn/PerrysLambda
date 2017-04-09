<?php

namespace PerrysLambda;

class StringProperty extends Property
{

    protected static $defaultencoding;

    protected $encoding;


    /**
     * Set global default encoding
     * @param string $encoding
     */
    public static function setDefaultEncoding($encoding=null)
    {
        if(!is_null($encoding) && is_string($encoding) && !empty($encoding))
        {
            self::$defaultencoding = $encoding;
        }
        else
        {
            self::$defaultencoding = null;
        }
    }

    /**
     * Get global default encoding
     * @return string
     */
    public static function getDefaultEncoding()
    {
        if(!is_null(self::$defaultencoding))
        {
            return self::$defaultencoding;
        }
        return mb_internal_encoding();
    }

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
     * Creates new instance of current class type
     * @return \PerrysLambda\StringProperty
     */
    public function newInstance($value = null)
    {
        $class = $this->getClassName();
        $o = new $class($value, $this->encoding);
        return $o;
    }

    /**
     * Check for scalar or null as valid data type
     * @param mixed $val
     * @return boolean
     */
    public function getIsValidData($val)
    {
        return is_string($val) || is_null($val);
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
            return self::getDefaultEncoding();
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
        return $this->newInstance(mb_substr($this->toString(), $start, $length, $this->getEncoding()));
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
     * Helper function for strpos
     * @param string $function
     * @param string $needle
     * @param int $offset
     * @return int
     */
    protected function indexOfDyamic($function, $needle, $offset)
    {
        $r = $function($this->toString(), $needle, $offset, $this->getEncoding());
        return ($r===false ? -1 : $r);
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
        return $this->indexOfDyamic('mb_strpos', $needle, $offset);
    }

    /**
     * The first position of a substring in this string, case insensitive
     * Returns -1 if substring not found
     * @param string $needle
     * @param int $offset
     * @return int
     */
    public function indexOfI($needle, $offset=0)
    {
        return $this->indexOfDyamic('mb_stripos', $needle, $offset);
    }

    /**
     * Helper function for strrpos
     * @param string $function
     * @param string $needle
     * @param int $offset
     * @return int
     */
    protected function lastIndexOfDynamic($function, $needle, $offset=0)
    {
        $r = $function($this->toString(), $needle, $offset, $this->getEncoding());
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
        return $this->lastIndexOfDynamic('mb_strrpos', $needle, $offset);
    }

    /**
     * This last position if a substring in this string, case insenitive
     * Returns -1 if substring not found
     * @param string $needle
     * @param int $offset
     * @return int
     */
    public function lastIndexOfI($needle, $offset=0)
    {
        return $this->lastIndexOfDynamic('mb_strripos', $needle, $offset);
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
     * Contains this string the griven substring, case insensitive
     * @param string $needle
     * @return boolean
     */
    public function containsI($needle)
    {
        return $this->indexOfI($needle)>=0;
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
     * Begins this string with the given substring, case insensitive
     * @param string $needle
     * @return boolean
     */
    public function startsWithI($needle)
    {
        return $this->indexOfI($needle)===0;
    }

    /**
     * Helper function for endsWith
     * @param string $function
     * @param string $needle
     * @return boolean
     */
    protected function endsWithDynamic($function, $needle)
    {
        $enc = $this->getEncoding();
        $strlen = $this->length($enc);
        $testlen = mb_strlen($needle, $enc);

        if ($testlen <= $strlen)
        {
            return ($function($this->toString(), $needle, ($strlen-$testlen), $enc)!==false);
        }
        return false;
    }

    /**
     * Ends this string with the given substring
     * @param string $needle
     * @return boolean
     */
    public function endsWith($needle)
    {
        return $this->endsWithDynamic('mb_strpos', $needle);
    }

    /**
     * Ends this string with the given substring
     * @param string $needle
     * @return boolean
     */
    public function endsWithI($needle)
    {
        return $this->endsWithDynamic('mb_stripos', $needle);
    }

}
