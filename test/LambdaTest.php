<?php

class Test extends PHPUnit_Framework_TestCase
{

    public function testScalar()
    {
        $s = new \PerrysLambda\ScalarProperty("Zähn € zahme Ziegen zögen zwei Zentner Zücker zum Zoö!");
        $this->assertSame($s->startsWith("Zä"), true);
    }

    public function testLambda()
    {
        $basic = new \PerrysLambda\ArrayList(array(1,2,3,4,5,6,7,8,9));

        // basics
        $this->assertEquals(1, $basic->first());
        $this->assertEquals(9, $basic->last());
        $this->assertEquals(2, $basic->skip(1)->first());
        $this->assertEquals(45, $basic->sum(function($v) { return $v; }));
        $this->assertEquals(1, $basic->min(function($v) { return $v; }));
        $this->assertEquals(9, $basic->max(function($v) { return $v; }));
        $this->assertEquals(5, $basic->avg(function($v) { return $v; }));
        $this->assertEquals('1.2.3.4.5.6.7.8.9', $basic->join(function($v) { return $v; }, '.'));
        $this->assertEquals(5, $basic->skip(1)->take(2)->sum(function($v) { return $v; }));
        $this->assertEquals(2, $basic->skip(1)->take(1)->single());

        // sorting
        $sorted = $basic
            ->order(function($v) { return ($v>=5 ? 1 : 0); })
            ->thenByDesc(function($v) { return $v; })
            ->toList();

        $this->assertEquals(4, $sorted->first());
        $this->assertEquals(5, $sorted->last());
        $this->assertEquals(9, $sorted[4]);
    }

}
