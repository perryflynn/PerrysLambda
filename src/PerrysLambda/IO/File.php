<?php

namespace PerrysLambda\IO;

class File extends \PerrysLambda\StringProperty
{

   protected $rootdir;

   public function __construct($file, File $dir=null)
   {
      $this->rootdir = $dir;
      if(is_null($this->rootdir))
      {
         $this->rootdir = $this;
      }

      parent::__construct($file);
   }

   /**
    * Get root dir
    * @return \PerrysLambda\IO\File
    */
   public function getRootDirectory()
   {
      return $this->rootdir;
   }

   /**
    * Set root dir
    * @param \PerrysLambda\IO\File $d
    */
   public function setRootDirectory(File $d)
   {
      $this->rootdir = $d;
   }

   /**
    * Real path
    * @return \PerrysLambda\IO\File
    * @throws NotFoundException
    */
   public function getReal()
   {
      if(!$this->isExists())
      {
         throw new NotFoundException("File not exist");
      }

      $temp = realpath($this->toString());

      if($this->isDir())
      {
         $temp = DIRECTORY_SEPARATOR.trim($temp, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
      }
      else
      {
         $temp = DIRECTORY_SEPARATOR.ltrim($temp, DIRECTORY_SEPARATOR);
      }

      return new static($temp, $this->getRootDirectory());
   }

   /**
    * Check file or folder exists
    * @return boolean
    */
   public function isExists()
   {
      return file_exists($this->toString());
   }

   /**
    * Check file or folder is readable
    * @return boolean
    */
   public function isReadable()
   {
      return is_readable($this->toString());
   }

   /**
    * Check file or folder is executable
    * @return boolean
    */
   public function isExecutable()
   {
      return is_executable($this->toString());
   }

   /**
    * Check file or folder is writable
    * @return type
    */
   public function isWritable()
   {
      return is_writable($this->toString());
   }

   /**
    * Check is dir
    * @return boolean
    */
   public function isDir()
   {
      return is_dir($this->toString());
   }

   /**
    * Check directory is listable
    * @return boolean
    */
   public function isListable()
   {
      return $this->isExecutable() && $this->isReadable();
   }

   /**
    * Open new Directory instance
    * @return \PerrysLambda\IO\Directory
    */
   public function openDir()
   {
      return new Directory($this, null, $this->getRootDirectory());
   }

   /**
    * Check is file
    * @return boolean
    */
   public function isFile()
   {
      return is_file($this->toString());
   }

   /**
    * Extract filename from full path
    * @return string
    */
   public function getBasename()
   {
      return basename($this->toString());
   }

   /**
    * Get directory path from full path
    * @return string
    */
   public function getDirname()
   {
      return dirname($this->toString());
   }

   /**
    * Check current file is child of given file
    * @param \PerrysLambda\IO\File $d
    * @return boolean
    */
   public function isChildOf(File $d)
   {
      return $this->getReal()->startsWith($d->getReal()->toString());
   }

   /**
    * Get folder of this file
    * @return \PerrysLambda\IO\File
    */
   public function getFolder()
   {
      if($this->isDir())
      {
         return $this;
      }
      else
      {
         return new static($this->getDirname(), $this->getRootDirectory());
      }
   }

   /**
    * Get Parent
    * @return \PerrysLambda\IO\File
    */
   public function getParentFolder()
   {
      return (new static($this->getReal()->toString()."../", $this->getRootDirectory()))->getReal();
   }

   /**
    * Path relative to given root object
    * @return string
    */
   public function getRootRelativePath()
   {
      return $this->getReal()->substr($this->getRootDirectory()->getReal()->length());
   }

}
