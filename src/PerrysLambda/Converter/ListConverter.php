<?php

namespace PerrysLambda\Converter;

use PerrysLambda\ArrayBase;
use PerrysLambda\Exception\SerializerException;
use PerrysLambda\Exception\InvalidValueException;
use PerrysLambda\ISerializer;
use PerrysLambda\IItemConverter;
use PerrysLambda\IListConverter;
use PerrysLambda\Serializer\DummySerializer;


class ListConverter implements IListConverter
{

    protected static $dummyserializerinstance;

    const ARRAYBASE='\PerrysLambda\ArrayBase';

    /**
     * Serializer
     * @var \PerrysLambda\ISerializer
     */
    protected $serializer;

    /**
     * Field converter
     * @var \PerrysLambda\Converter\IFieldConverter
     */
    protected $itemconverter;

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


    public function __construct()
    {
        if(is_null(self::$dummyserializerinstance))
        {
            self::$dummyserializerinstance = new DummySerializer();
        }

        $this->serializer = self::$dummyserializerinstance;
        $this->iterator = null;
        $this->iteratorstartindex = 0;
        $this->iteratorendindex = -1;
    }

    /**
     * Set the serializer
     * @param \PerrysLambda\ISerializer $serializer
     */
    public function setSerializer(\PerrysLambda\ISerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Gets the serializer
     * @return \PerrysLambda\ISerializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Set the itemconverter
     * @param \PerryFlynn\IItemConverter $itemconverter
     */
    public function setItemConverter(IItemConverter $itemconverter)
    {
        $this->itemconverter = $itemconverter;
    }

    /**
     * Get the itemconverter
     * @return \PerrysLambda\IItemConverter
     */
    public function getItemConverter()
    {
        return $this->itemconverter;
    }

    public function isItemConverterExist()
    {
        return $this->itemconverter instanceof IItemConverter;
    }

    /**
     * Array as import source
     * @param array $data
     */
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

    public function setDefaults(array $defaults=null)
    {
        if($this->isItemConverterExist())
        {
            $this->getItemConverter()->setDefaults($defaults);
        }
    }

    /**
     * Iterator as import source
     * @param \Iterator $iterator
     * @param int $start
     * @param int $end
     */
    public function setIteratorSource(\Iterator $iterator=null, $start=0, $end=-1)
    {
        $this->iteratorstartindex = (int)$start;
        $this->iteratorendindex = (int)$end;
        $this->iterator = $iterator;
    }

    /**
     * Import into ArrayBase
     * @param ArrayBase $collection
     */
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
                    $tempkey = $key;
                    $tempvalue = $row;

                    /*
                    if($this->isItemConverterExist())
                    {
                        $this->getItemConverter()->deserializeAll($tempvalue, $tempkey);
                    }
                    */

                    if($tempvalue!==null && $tempkey!==null)
                    {
                        $collection->set($tempkey, $tempvalue);//, true);
                    }
                }
                $i++;
            }
        }
    }

    /**
     * Creates a new instance of this class
     * @return \PerrysLambda\Converter\ListConverter
     */
    public function newInstance()
    {
        $class = get_called_class();
        $instance = new $class();
        $instance->setSerializer($this->getSerializer());
        $instance->setItemConverter($this->getItemConverter()->newInstance());
        return $instance;
    }

    public function serialize(&$row, &$key)
    {
        if(!($this->serializer instanceof ISerializer))
        {
            throw new SerializerException("No serializer set");
        }

        $ser = $this->serializer->getSerializer();
        $result = $ser($row, $key);

        if($result!==true && $result!==false)
        {
            throw new SerializerException("Serializer must return a bool for success indication");
        }

        if($this->isItemConverterExist() && (is_array($row) || $row instanceof \ArrayAccess))
        {
            if($this->getItemConverter()->serializeAll($row, $key)===false)
            {
                $result = false;
            }
        }

        return $result;
    }

    public function toGenerator(ArrayBase $collection)
    {
        foreach($collection as $index => $row)
        {
            $tempindex = $index;
            $temprow = $row;

            if($temprow instanceof ArrayBase)
            {
                $temprow = $temprow->copy();
            }

            $this->serialize($temprow, $tempindex);
            yield $tempindex => $temprow;
        }
    }

    public function toArray(ArrayBase $collection)
    {
        $result = array();
        foreach($this->toGenerator($collection) as $index => $row)
        {
            $result[$index] = $row;
        }
        return $result;
    }


}
