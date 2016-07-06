<?php

namespace PerrysLambda;

class Converter implements IConverter
{

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
            $deser = $this->rowconverter->getSerializer();
            if($deser($row, $key)===false)
            {
                return false;
            }
        }

        foreach($row as $fieldkey => $fielditem)
        {
            if($this->serializeField($fielditem, $fieldkey)===false)
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
            $deser = $this->fieldconverters[$fieldkey]->getSerializer();
            if($deser($field, $fieldkey)===false)
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

        foreach($row as $fieldkey => $fielditem)
        {
            if($this->deserializeField($fielditem, $fieldkey)===false)
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
