<?php

namespace PerrysLambda;

class DirectoryIterator implements \Iterator
{

    protected $resource;
    protected $current;
    protected $index;

    public function __construct($path)
    {
        $this->resource = opendir($path);
    }

    public function current()
    {
        return $this->current;
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->current = readdir($this->resource);
        $this->index++;
    }

    public function rewind()
    {
        throw new \Exception("Unsupported");
    }

    public function valid()
    {
        return $this->current!==false;
    }

}
