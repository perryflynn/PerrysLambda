<?php

namespace PerrysLambda\IO;

class Directory extends \PerrysLambda\ArrayList
{

   protected $path;
   protected $root;

   public function __construct(File $path, $items=null, File $root=null)
   {
      $this->path = $path;
      $this->root = $root;

      if(is_null($this->root))
      {
         $this->root = $this->path;
      }

      $this->path->setRootDirectory($this->getRoot());

      if(is_array($items))
      {
         parent::__construct($items, '\PerrysLambda\IO\File');
      }
      else
      {
         if(!$this->path->isDir())
         {
            throw new NotFoundException("Path does not exist or is not a directory");
         }

         if(!$this->path->isListable())
         {
            throw new AccessException("Cannot list directory content");
         }

         $data = new DirectoryIteratorNoDots($this->path->toString());
         parent::__construct($data, '\PerrysLambda\IO\File');
      }
   }

   protected function newInstance()
   {
      $class = $this->getClassName();
      return new $class($this->path, array(), $this->root);
   }

   protected function newItemInstance()
   {
      $i = parent::newItemInstance();
      $i->setRootDirectory($this->getRoot());
      return $i;
   }

   protected function convertDataField($field)
   {
      $i = parent::convertDataField($field);
      $i->setRootDirectory($this->getRoot());
      return $i;
   }

   /**
    * The path
    * @return \PerrysLambda\IO\File
    */
   public function getPath()
   {
      return $this->path;
   }

   /**
    * The root
    * @return \PerrysLambda\IO\File
    */
   public function getRoot()
   {
      return $this->root;
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


}
