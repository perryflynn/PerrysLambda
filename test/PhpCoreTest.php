<?php

class PhpCoreTest extends PHPUnit_Framework_TestCase
{
    
    public function testObjectComparsion()
    {
        $foo = new \stdClass();
        $bar = new \stdClass();
        $this->assertEquals($foo, $bar);
        
        $foo->test=1;
        $this->assertNotEquals($foo, $bar);
        
        $bar->test=1;
        $this->assertEquals($foo, $bar);
    }

}
