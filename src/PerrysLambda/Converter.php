<?php

namespace PerrysLambda;

class Converter implements IConverter
{
    
    const ARRAYBASE='\PerrysLambda\ArrayBase';

    /**
     * Data iterator
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Converter callables for fields
     * @var \PerrysLambda\ISerializer[]
     */
    protected $fieldconverters;

    /**
     * Converter callables for a single row
     * @var \PerrysLambda\ISerializer
     */
    protected $rowconverter;



    public function __construct()
    {
        $this->iterator = null;
        $this->fieldconverters = array();
        $this->rowconverter = null;
    }

    public function newInstance()
    {
        $temp = clone $this;
        $temp->setIteratorSource(null);
        return $temp;
    }

    public function setArraySource(array $data=null)
    {
        if($data===null)
        {
            $this->iterator = null;
        }
        else
        {
            $this->iterator = new \ArrayIterator($data);
        }
    }

    public function setIteratorSource(\Iterator $iterator=null)
    {
        $this->iterator = $iterator;
    }

    public function setRowConverter(ISerializer $ser)
    {
        $this->rowconverter = $ser;
    }

    public function setFieldConverters(array $converters)
    {
        foreach($converters as $name => $ser)
        {
            $this->setFieldConverter($name, $ser);
        }
    }

    public function setFieldConverter($fieldname, ISerializer $ser)
    {
        if(is_scalar($fieldname))
        {
            $this->fieldconverters[$fieldname] = $ser;
        }
    }

    public function importInto(ArrayBase $collection)
    {
        if($this->iterator instanceof \Iterator)
        {
            foreach($this->iterator as $key => $row)
            {
                $tempkey = $key;
                $temprow = $row;

                $this->deserializeRow($temprow, $tempkey);
                $collection->set($tempkey, $temprow);
            }
        }
    }

    public function exportFromAsGenerator(ArrayBase $collection)
    {
        foreach($collection as $index => $row)
        {
            $tempindex = $index;
            $temprow = $row;

            $this->serializeRow($temprow, $tempindex);
            yield $tempindex => $temprow;
        }
    }

    public function exportFromAsArray(ArrayBase $collection)
    {
        $result = array();
        foreach($this->exportFromAsGenerator($collection) as $index => $row)
        {
            $result[$index] = $row;
        }
        return $result;
    }

    public function serializeRow(&$row, &$key)
    {
        if($this->rowconverter instanceof ISerializer)
        {
            $ser = $this->rowconverter->getSerializer();
            if($ser($row, $key)===false)
            {
                return false;
            }
        }
        
        $fieldkeys = null;
        if(is_array($row))
        {
            $fieldkeys = array_keys($row);
        }
        elseif(is_subclass_of($row, self::ARRAYBASE))
        {
            $fieldkeys = $row->getNames();
        }
        else
        {
            $fieldkeys = array();
        }
        
        foreach($fieldkeys as $fieldkey)
        {
            $tempkey = $fieldkey;
            $tempvalue = $row[$fieldkey];
            
            $result = $this->serializeField($tempvalue, $tempkey);
            
            if($fieldkey!=$tempkey)
            {
                unset($row[$fieldkey]);
            }
            
            $row[$tempkey] = $tempvalue;
            
            if($result===false)
            {
                return false;
            }
        }
        

        return true;
    }

    public function serializeField(&$field, &$fieldkey)
    {
        if(array_key_exists($fieldkey, $this->fieldconverters))
        {
            $ser = $this->fieldconverters[$fieldkey]->getSerializer();
            if($ser($field, $fieldkey)===false)
            {
                return false;
            }
        }
    }

    public function deserializeRow(&$row, &$key)
    {
        if($this->rowconverter instanceof ISerializer)
        {
            $deser = $this->rowconverter->getDeserializer();
            if($deser($row, $key)===false)
            {
                return false;
            }
        }
        
        $fieldkeys = null;
        if(is_array($row))
        {
            $fieldkeys = array_keys($row);
        }
        elseif(is_subclass_of($row, self::ARRAYBASE))
        {
            $fieldkeys = $row->getNames();
        }
        else
        {
            $fieldkeys = array();
        }
        
        foreach($fieldkeys as $fieldkey)
        {
            $tempkey = $fieldkey;
            $tempvalue = $row[$fieldkey];
            
            $result = $this->deserializeField($tempvalue, $tempkey);
            
            if($fieldkey!=$tempkey)
            {
                unset($row[$fieldkey]);
            }
            
            $row[$tempkey] = $tempvalue;
            
            if($result===false)
            {
                return false;
            }
        }

        return true;
    }

    public function deserializeField(&$field, &$fieldkey)
    {
        if(array_key_exists($fieldkey, $this->fieldconverters))
        {
            $deser = $this->fieldconverters[$fieldkey]->getDeserializer();
            if($deser($field, $fieldkey)===false)
            {
                return false;
            }
        }
    }

}
