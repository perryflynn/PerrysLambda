<?php

namespace PerrysLambda\IO;

use PerrysLambda\Serializer;
use PerrysLambda\Exception\InvalidTypeException;


class DirectoryConverter extends \PerrysLambda\Converter
{

    const ITEMCLASS = '\PerrysLambda\IO\File';

    protected $itemtype;
    protected $path;
    protected $root;


    public static function fromPath(File $path)
    {
        $conv = new static();
        $conv->setRoot($path);
        $conv->setPath($path);
        return $conv;
    }

    public function __construct()
    {
        parent::__construct();
        $this->itemtype = self::ITEMCLASS;

        $serializer = function(&$row, &$key)
        {
            if(is_a($row, $this->itemtype))
            {
                $row = $row->toString();
            }
            return true;
        };

        $deserializer = function(&$row, &$key)
        {
            if(!is_a($row, $this->itemtype))
            {
                $temp = $this->itemtype;
                $row = new $temp($row, $this->root);
            }
            return true;
        };

        $this->setRowConverter(new Serializer($serializer, $deserializer));
    }

    public function setPath(File $path)
    {
        if(!$path->isExists())
        {
            throw new IOException("Does not exist");
        }

        if(!$path->isDir())
        {
            throw new IOException("Not a dir");
        }

        $this->path = $path;

        if($this->root===null)
        {
            $this->root = $this->path;
        }

        $this->setIteratorSource(new DirectoryIteratorNoDots($this->path->toString()));
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setRoot(File $path)
    {
        if(!$path->isExists())
        {
            throw new IOException("Does not exist");
        }

        if(!$path->isDir())
        {
            throw new IOException("Not a dir");
        }

        if(!$path->isDir())
        {
            throw new InvalidTypeException("Not a directory");
        }

        $this->root = $path;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setItemtype($type=null)
    {
        if(is_null($type))
        {
            $type = self::ITEMCLASS;
        }

        if(!class_exists($type))
        {
            throw new InvalidTypeException("Type not exist");
        }

        if($type!=self::ITEMCLASS && !is_subclass_of($type, self::ITEMCLASS))
        {
            throw new InvalidTypeException("Type must be a subtype of ".self::ITEMCLASS);
        }

        $this->itemtype = $type;
    }

}
