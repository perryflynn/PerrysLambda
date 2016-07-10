<?php

namespace PerrysLambda\IO;

class LineIterator implements \Iterator
{

    protected $file;
    protected $index;
    protected $currentline;


    public function __construct(File $file)
    {
        if(!$file->isFile())
        {
            throw new CsvParseException("Could not open file");
        }

        $this->index = 0;
        $this->currentline = null;
        $this->file = fopen($file, 'r');
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

    protected function nextLine()
    {
        $temp = fgets($this->file);

        if(is_string($temp))
        {
            return $temp;
        }
        else
        {
            return null;
        }
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
        if(is_string($this->currentline))
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
        return is_string($this->currentline);
    }


}
