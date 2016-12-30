<?php

namespace PerrysLambda\Converter;

use PerrysLambda\ISerializer;
use PerrysLambda\Exception\SerializerException;
use PerrysLambda\IFieldConverter;

class FieldConverter implements IFieldConverter
{
    
    protected $serializers;
    
    
    public function __construct()
    {
        $this->serializers = array();
    }
    
    public function setSerializer($fieldname, ISerializer $serializer)
    {
        $this->serializers[$fieldname] = $serializer;
    }
    
    public function getSerializer($fieldname)
    {
        if(array_key_exists($fieldname, $this->serializers))
        {
            return $this->serializers[$fieldname];
        }
        return null;
    }
    
    public function setSerializers(array $serializers)
    {
        foreach($serializers as $fieldname => $serializer)
        {
            if(!($serializer instanceof ISerializer))
            {
                throw new SerializerException("There is no ISerializer instance in ".$fieldname);
            }
            $this->setSerializer($fieldname, $serializer);
        }
    }
    
    public function getSerializers()
    {
        return $this->serializers;
    }

    public function newInstance() 
    {
        $class = get_called_class();
        $instance = new $class();
        $instance->setSerializers($this->serializers);
        return $instance;
    }

    public function deserialize(&$row, &$key) 
    {
        if(array_key_exists($key, $this->serializers) && $this->serializers[$key] instanceof ISerializer)
        {
            $deser = $this->serializers[$key]->getDeserializer();
            $result = $deser($row, $key);

            if($result!==true && $result!==false)
            {
                throw new SerializerException("Serializer must return a bool for success indication");
            }

            return $result;
        }
        return true;
    }

    public function serialize(&$row, &$key) 
    {
        if(array_key_exists($key, $this->serializers) && $this->serializers[$key] instanceof ISerializer)
        {
            $ser = $this->serializers[$key]->getSerializer();
            $result = $ser($row, $key);

            if($result!==true && $result!==false)
            {
                throw new SerializerException("Serializer must return a bool for success indication");
            }

            return $result;
        }
        return true;
    }

}
