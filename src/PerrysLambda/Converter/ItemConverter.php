<?php

namespace PerrysLambda\Converter;

use PerrysLambda\ISerializer;
use PerrysLambda\Exception\SerializerException;
use PerrysLambda\IItemConverter;
use PerrysLambda\ArrayBase;

class ItemConverter implements IItemConverter
{

    const ARRAYBASE='\PerrysLambda\ArrayBase';

    protected $itemserializer;
    protected $fieldserializers;

    /**
     *Default values for one single row
     * @var array
     */
    protected $defaults;


    public function __construct()
    {
        $this->itemserializer = null;
        $this->fieldserializers = array();
    }

    public function setSerializer(ISerializer $serializer)
    {
        $this->itemserializer = $serializer;
    }

    public function getSerializer()
    {
        return $this->itemserializer;
    }

    public function isSerializerExist()
    {
        return $this->itemserializer instanceof ISerializer;
    }

    public function setFieldSerializer($fieldname, ISerializer $serializer)
    {
        $this->fieldserializers[$fieldname] = $serializer;
    }

    public function getFieldSerializer($fieldname)
    {
        if(array_key_exists($fieldname, $this->fieldserializers))
        {
            return $this->fieldserializers[$fieldname];
        }
        return null;
    }

    public function setFieldSerializers(array $serializers)
    {
        foreach($serializers as $fieldname => $serializer)
        {
            if(!($serializer instanceof ISerializer))
            {
                throw new SerializerException("There is no ISerializer instance in ".$fieldname);
            }
            $this->setFieldSerializer($fieldname, $serializer);
        }
    }

    public function isFieldSerializerExist($fieldname)
    {
        return array_key_exists($fieldname, $this->fieldserializers) &&
                $this->fieldserializers[$fieldname] instanceof ISerializer;
    }

    public function getFieldSerializers()
    {
        return $this->fieldserializers;
    }

    public function newInstance()
    {
        $class = get_called_class();
        $instance = new $class();
        $instance->setFieldSerializers($this->getFieldSerializers());

        if($this->isSerializerExist())
        {
            $instance->setSerializer($this->getSerializer());
        }

        return $instance;
    }

    public function setDefaults(array $defaults=null)
    {
        $this->defaults = $defaults;
    }

    public function deserializeAll(&$listitem, &$listitemkey)
    {
        $itemresult = $this->deserialize($listitem, $listitemkey);
        $fieldresult = $this->deserializeFields($listitem, $listitemkey);
        return $itemresult && $fieldresult;
    }

    public function deserializeFields(&$listitem, &$listitemkey)
    {
        $itemresult = true;
        if(is_array($listitem) || $listitem instanceof \ArrayAccess)
        {
            $result = array();
            foreach($listitem as $key => $value)
            {
                $tempkey = $key;
                $tempval = $value;
                if($this->deserializeField($tempval, $tempkey)===true)
                {
                    $result[$tempkey] = $tempval;
                }
                else
                {
                    $result[$key] = $value;
                    $itemresult = false;
                }
            }

            if($listitem instanceof ArrayBase)
            {
                $listitem->setData($result);
            }
            else
            {
                $listitem = $result;
            }
        }
        return $itemresult;
    }

    public function serializeAll(&$listitem, &$listitemkey)
    {
        $fieldresult = $this->serializeFields($listitem, $listitemkey);
        $itemresult = $this->serialize($listitem, $listitemkey);
        return $itemresult && $fieldresult;
    }

    public function serializeFields(&$listitem, &$listitemkey)
    {
        $itemresult = true;
        if(is_array($listitem) || $listitem instanceof \ArrayAccess)
        {
            $result = array();
            foreach($listitem as $key => $value)
            {
                $tempkey = $key;
                $tempval = $value;
                if($this->serializeField($tempval, $tempkey)===true)
                {
                    $result[$tempkey] = $tempval;
                }
                else
                {
                    $result[$key] = $value;
                    $itemresult = false;
                }
            }

            if($listitem instanceof ArrayBase)
            {
                $listitem->setData($result);
            }
            else
            {
                $listitem = $result;
            }
        }
        return $itemresult;
    }

    public function deserialize(&$row, &$key)
    {
        if($this->isSerializerExist())
        {
            $deser = $this->getSerializer()->getDeserializer();
            $result = $deser($row, $key, $this);

            if($result!==true && $result!==false)
            {
                throw new SerializerException("Serializer must return a bool for success indication");
            }

            if(is_subclass_of($row, self::ARRAYBASE))
            {
                $row->setConverter($this);
                if(is_array($this->defaults))
                {
                    $row->applyDefaults($this->defaults);
                }
            }

            return $result;
        }
        return true;
    }

    public function serialize(&$row, &$key)
    {
        if($this->isSerializerExist())
        {
            $ser = $this->getSerializer()->getSerializer();
            $result = $ser($row, $key, $this);

            if($result!==true && $result!==false)
            {
                throw new SerializerException("Serializer must return a bool for success indication");
            }

            return $result;
        }
        return true;
    }

    public function deserializeField(&$row, &$key)
    {
        if($this->isFieldSerializerExist($key))
        {
            $deser = $this->getFieldSerializer($key)->getDeserializer();
            $result = $deser($row, $key, $this);

            if($result!==true && $result!==false)
            {
                throw new SerializerException("Serializer must return a bool for success indication");
            }

            return $result;
        }
        return true;
    }

    public function serializeField(&$row, &$key)
    {
        if($this->isFieldSerializerExist($key))
        {
            $ser = $this->getFieldSerializer($key)->getSerializer();
            $result = $ser($row, $key, $this);

            if($result!==true && $result!==false)
            {
                throw new SerializerException("Serializer must return a bool for success indication");
            }

            return $result;
        }
        return true;
    }

}
