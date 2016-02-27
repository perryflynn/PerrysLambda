<?php

class Test extends PHPUnit_Framework_TestCase
{

    public function testScalar()
    {
        $s = new \PerrysLambda\ScalarProperty("Zähn € zahme Ziegen zögen zwei Zentner Zücker zum Zoö!", 'UTF-8');

        $this->assertSame(true, $s->startsWith("Zä"));
        $this->assertSame(false, $s->startsWith("zä"));
        $this->assertSame(true, $s->startsWithI("zä"));
        $this->assertSame(true, $s->endsWith(' Zoö!'));
        $this->assertSame(false, $s->endsWith(' zoö!'));
        $this->assertSame(true, $s->endsWithI(' zoö!'));
        $this->assertSame(54, $s->length());
        $this->assertSame(3, count($s->split('ö')));
        $this->assertSame(' Zoö!', $s->substr(-5));
        $this->assertSame(1, $s->indexOf('ä'));
        $this->assertSame(-1, $s->indexOf('Ä'));
        $this->assertSame(1, $s->indexOfI('Ä'));
        $this->assertSame(53, $s->lastIndexOf('!'));
        $this->assertSame(false, $s->contains('asdf'));
        $this->assertSame(true, $s->contains('zwei'));
        $this->assertSame(false, $s->contains('ZWEI'));
        $this->assertSame(true, $s->containsI('ZWEI'));

        $integer = new PerrysLambda\ScalarProperty('4211');
        $this->assertSame(4211, $integer->toInt());
        $this->assertSame(4211, $integer->toNumeric());

        $float = new PerrysLambda\ScalarProperty('50.4');
        $this->assertSame(50.4, $float->toNumeric());
        $this->assertSame(50.4, $float->toFloat());

        $string = new PerrysLambda\ScalarProperty(50.3);
        $this->assertSame('50.3', $string->toString());
    }

    public function testLambda()
    {
        $basic = new \PerrysLambda\ArrayList(array(1,2,3,4,5,6,7,8,9));
        $all = new \PerrysLambda\ArrayList(array(1, 1, 1, 1, 1));

        // basics
        $this->assertSame(1, $basic->first());
        $this->assertSame(9, $basic->last());
        $this->assertSame(2, $basic->skip(1)->first());
        $this->assertSame(45, $basic->sum(function($v) { return $v; }));
        $this->assertSame(1, $basic->min(function($v) { return $v; }));
        $this->assertSame(9, $basic->max(function($v) { return $v; }));
        $this->assertSame(5, $basic->avg(function($v) { return $v; }));
        $this->assertSame('1.2.3.4.5.6.7.8.9', $basic->join(function($v) { return $v; }, '.'));
        $this->assertSame(5, $basic->skip(1)->take(2)->sum(function($v) { return $v; }));
        $this->assertSame(2, $basic->skip(1)->take(1)->single());

        // any / all
        $this->assertSame(true, $basic->any(function($v) { return $v===1; }));
        $this->assertSame(false, $basic->all(function($v) { return $v===1; }));
        $this->assertSame(true, $all->all(function($v) { return $v===1; }));
        $this->assertSame(true, $all->any(function($v) { return $v===1; }));
        $this->assertSame(false, $all->any(function($v) { return $v===2; }));

        // sorting
        $sorted = $basic
            ->order(function($v) { return ($v>=5 ? 1 : 0); })
            ->thenByDesc(function($v) { return $v; })
            ->toList();

        $this->assertSame(4, $sorted->first());
        $this->assertSame(5, $sorted->last());
        $this->assertSame(9, $sorted[4]);
    }

}
