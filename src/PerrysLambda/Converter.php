<?php

namespace PerrysLambda;

use PerrysLambda\Exception\SerializerException;

class Converter implements IConverter
{

    const ARRAYBASE='\PerrysLambda\ArrayBase';

    /**
     * Data iterator
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Start import at given index
     * @var int
     */
    protected $iteratorstartindex;

    /**
     * End import at given index
     * @var int
     */
    protected $iteratorendindex;

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

    /**
     *Default values for one single row
     * @var array
     */
    protected $defaults;



    public function __construct()
    {
        $this->iterator = null;
        $this->fieldconverters = array();
        $this->rowconverter = null;
        $this->iteratorstartindex = 0;
        $this->iteratorendindex = -1;
        $this->defaults = null;
    }

    public function newInstance()
    {
        $temp = clone $this;
        $temp->setIteratorSource(null);
        return $temp;
    }

    public function setDefaults($defaults)
    {
        if(is_array($defaults))
        {
            $this->defaults = $defaults;
        }
        else
        {
            $this->defaults = null;
        }
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

    public function setIteratorSource(\Iterator $iterator=null, $start=0, $end=-1)
    {
        $this->iteratorstartindex = (int)$start;
        $this->iteratorendindex = (int)$end;
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
            $i = 0;

            foreach($this->iterator as $key => $row)
            {
                if($this->iteratorendindex>=0 && $i>$this->iteratorendindex)
                {
                    break;
                }
                elseif($i>=$this->iteratorstartindex)
                {
                    if($row!==null && $key!==null)
                    {
                        $collection->set($key, $row);
                    }
                }

                $i++;
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
            $rowstate = $ser($row, $key);

            if(!is_bool($rowstate))
            {
                throw new SerializerException("Callable must return true for success and false for failure");
            }
            elseif($rowstate===false)
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
            $fieldstate = $ser($field, $fieldkey);

            if(!is_bool($fieldstate))
            {
                throw new SerializerException("Callable must return true for success and false for failure");
            }
            elseif($fieldstate===false)
            {
                return false;
            }
        }

        return true;
    }

    public function deserializeRow(&$row, &$key)
    {
        if($this->rowconverter instanceof ISerializer)
        {
            $deser = $this->rowconverter->getDeserializer();
            $rowstate = $deser($row, $key);

            if(!is_bool($rowstate))
            {
                throw new SerializerException("Callable must return true for success and false for failure");
            }
            elseif($rowstate===false)
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

        if(is_array($this->defaults))
        {
            if(is_subclass_of($row, self::ARRAYBASE))
            {
                $row->applyDefaults($this->defaults);
            }
            else
            {
                throw new Exception\InvalidValueException("To apply defaults the row must be a subclass of ".self::ARRAYBASE);
            }
        }

        return true;
    }

    public function deserializeField(&$field, &$fieldkey)
    {
        if(array_key_exists($fieldkey, $this->fieldconverters))
        {
            $deser = $this->fieldconverters[$fieldkey]->getDeserializer();
            $fieldstate = $deser($field, $fieldkey);

            if(!is_bool($fieldstate))
            {
                throw new SerializerException("Callable must return true for success and false for failure");
            }
            elseif($fieldstate===false)
            {
                return false;
            }
        }

        return true;
    }

}
