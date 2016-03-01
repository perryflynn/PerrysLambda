<?php

namespace PerrysLambda;

class DirectoryIterator implements \SeekableIterator
{

    protected $data;
    protected $path;
    protected $index;

    public function __construct($path)
    {
        $this->path = DIRECTORY_SEPARATOR.trim(realpath($path), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $this->data = scandir($this->path);
        $this->index = 0;
    }

    public function current()
    {
        return $this->path.$this->data[$this->index];
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->index++;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid()
    {
        return isset($this->data[$this->index]);
    }

    public function seek($position) 
    {
        $this->index = $position;
    }

}
