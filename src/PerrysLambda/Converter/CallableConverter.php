<?php

namespace PerrysLambda\Converter;

class CallableConverter extends BasicConverter
{

    protected $function;

    public function __construct(callable $function)
    {
        $this->function = $function;
    }

    public function convert($in, \PerrysLambda\ArrayList $r)
    {
        return call_user_func($this->function, $in, $r);
    }

}
