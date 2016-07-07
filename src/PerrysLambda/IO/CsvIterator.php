<?php

namespace PerrysLambda\IO;

class CsvIterator implements \Iterator
{

    protected $file;
    protected $settings;
    protected $index;
    protected $currentline;
    protected $header;
    protected $columncount;
    

    public function __construct(File $file, CsvParser $settings)
    {
        if(!$file->isFile())
        {
            throw new CsvParseException("Could not open file");
        }
        
        $this->index = 0;
        $this->currentline = null;
        $this->header = null;
        $this->columncount = null;
        $this->file = fopen($file, 'r');
        $this->settings = $settings;
        
        if($this->settings->hasHeader())
        {
            $temp = $this->parseLine();
            if(!is_array($temp))
            {
                throw new CsvParseException("Could not parse header");
            }
            $this->header = $temp;
        }
    }
    
    public function __destruct()
    {
        $this->dispose();
    }
    
    public function dispose()
    {
        if(is_resource($this->file))
        {
            fclose($this->file);
            $this->file = null;
        }
    }
    
    protected function parseLine()
    {
        $s = $this->settings->getSeparator();
        $q = $this->settings->getQualifier();
        $temp = fgetcsv($this->file, 0, $s, $q);
        
        if(is_array($temp))
        {
            if($this->columncount===null)
            {
                $this->columncount = count($temp);
            }
            return $temp;
        }
        else
        {
            return null;
        }
    }
    
    protected function nextLine()
    {
        $temp = $this->parseLine();
        
        if(is_array($temp) && $this->settings->isValidate() &&
                is_int($this->columncount) && $this->columncount!=count($temp))
        {
            $dir = ($this->columncount>count($temp) ? "undersized" : "oversized");
            throw new CsvParseException("Record No ".($this->index+1)." is ".$dir);
        }
        
        if(is_array($this->header) && is_array($temp))
        {
            $result = array();
            $length = max(array(count($temp), count($this->header)));
            for($i=0; $i<$length; $i++)
            {
                if(!isset($this->header[$i]))
                {
                    $this->header[$i] = "Column".$i;
                }
                if(isset($temp[$i]))
                {
                    $result[$this->header[$i]] = $temp[$i];
                }
                else
                {
                    $result[$this->header[$i]] = null;
                }
            }
            return $result;
        }
        
        return $temp;
    }

    public function current()
    {
        return $this->currentline;
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->currentline = $this->nextLine();
        if(is_array($this->currentline))
        {
            $this->index++;
        }
    }

    public function rewind()
    {
        $this->index = -1;
        $this->next();
    }

    public function valid()
    {
        return is_array($this->currentline);
    }

    
}
