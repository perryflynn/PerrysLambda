<?php

namespace PerrysLambda;

class Serializer implements ISerializer
{

    protected $serializer;
    protected $deserializer;

    public function __construct(callable $serializer, callable $deserializer)
    {
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
    }

    /**
     * Get serializer
     * @return callable
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get deserializer
     * @return callable
     */
    public function getDeserializer()
    {
        return $this->deserializer;
    }

}
