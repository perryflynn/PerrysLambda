<?php

namespace PerrysLambda\IO;

class Directory extends \PerrysLambda\ArrayList
{

    /**
     * Directory from path
     * @param \PerrysLambda\IO\File $path
     * @return \PerrysLambda\IO\Directory
     */
    public static function fromPath(File $path)
    {
        return new static(DirectoryConverter::fromPath($path));
    }

    /**
     * Constructor
     * @param \PerrysLambda\IO\DirectoryConverter $dirconv
     */
    public function __construct(DirectoryConverter $dirconv)
    {
        $dirconv->setItemtype($this->getFileClassType());
        parent::__construct($dirconv);
    }

    /**
     * Class type of file items
     * @return string
     */
    public function getFileClassType()
    {
        return DirectoryConverter::ITEMCLASS;
    }

    /**
     * The path
     * @return \PerrysLambda\IO\File
     */
    public function getPath()
    {
        return $this->__converter->getPath();
    }

    /**
     * The root
     * @return \PerrysLambda\IO\File
     */
    public function getRoot()
    {
        return $this->__converter->getRoot();
    }

    /**
     * Get all directories
     * @return \PerrysLambda\IO\Directory
     */
    public function getDirectories()
    {
        return $this->where(function(File $f) { return ($f->isDir()===true); });
    }

    /**
     * Get all files
     * @return \PerrysLambda\IO\Directory
     */
    public function getFiles()
    {
        return $this->where(function(File $f) { return ($f->isFile()===true); });
    }

    /**
     * Get single file by basename
     * @param string $name
     * @return \PerrysLambda\IO\File
     */
    public function getByBasename($name)
    {
        return $this->where(function(File $f) use($name) { return $f->getBasename()==$name; })->single();
    }

    /**
     * Get single file by basename
     * @param string $name
     * @return \PerrysLambda\IO\File
     */
    public function getByBasenameOrDefault($name, $default=null)
    {
        return $this->where(function(File $f) use($name)
        {
            return $f->getBasename() == $name;
        })->singleOrDefault($default);
    }


}
