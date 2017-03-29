<?php

namespace PerrysLambda\IO;

class CsvParser
{

    protected $hasheader;
    protected $separator;
    protected $qualifier;
    protected $validate;

    public function __construct($hasheader=true, $fieldseparator=';', $fieldqualifier='"', $validate=false)
    {
        $this->hasheader = $hasheader===true;
        $this->validate = $validate===true;
        $this->separator = $fieldseparator;
        $this->qualifier = $fieldqualifier;
        
        if(!is_string($this->separator) || strlen($this->separator)!=1)
        {
            throw new CsvParseException("Separator must be one character");
        }
        
        if($this->qualifier!==null && strlen($this->qualifier)!=1)
        {
            throw new CsvParseException("Qualifier must be one character or NULL");
        }
    }
    
    public function hasHeader()
    {
        return $this->hasheader===true;
    }
    
    public function setHasHeader($b)
    {
        $this->hasheader = $b===true;
    }
    
    public function isValidate()
    {
        return $this->validate===true;
    }
    
    public function setValidate($b)
    {
        $this->validate = $b===true;
    }
    
    public function getSeparator()
    {
        return $this->separator;
    }
    
    public function getQualifier()
    {
        return $this->qualifier;
    }
    
    public function openFile(File $file)
    {
        return new CsvIterator($file, $this);
    }
    

}
