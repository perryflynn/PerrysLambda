<?php

namespace PerrysLambda\IO;

class DirectoryIteratorNoDots extends \FilterIterator
{

    public function __construct($path)
    {
        parent::__construct(new DirectoryIterator($path));
    }

    public function accept()
    {
        $c = basename($this->getInnerIterator()->current());
        return ($c!="." && $c!="..");
    }

}
