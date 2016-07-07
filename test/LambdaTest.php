<?php

class LambdaTest extends PHPUnit_Framework_TestCase
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
        $this->assertSame('1.2.3.4.5.6.7.8.9', $basic->joinString(function($v) { return $v; }, '.'));
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


    public function testReferences()
    {
        $test = new \PerrysLambda\ObjectArray();

        $test->a = 12;
        $this->assertSame(12, $test->a);

        $test->a++;
        $this->assertSame(13, $test->a);
        $this->assertSame(13, $test('a'));

        $a = $test->a;
        $a++;
        $this->assertSame(13, $test->a);

        $a = &$test->a;
        $a++;
        $this->assertSame(14, $test->a);

        $test['b'] = 42;
        $this->assertSame(42, $test['b']);
        $this->assertSame(42, $test->b);
        $this->assertSame(42, $test('b'));

        $test['b']++;
        $this->assertSame(43, $test['b']);

        $b = $test['b'];
        $b++;
        $this->assertSame(43, $test['b']);

        $b = &$test['b'];
        $b++;
        $this->assertSame(44, $test['b']);

        $test->c = 34;
        $test->d = 45;
        $test->e = 56;
        $test->f = 67;

        $test->each(function($v) { $v++; });
        $this->assertSame('14.44.34.45.56.67', $test->joinString(function($v) { return $v; }, '.'));

        $test->each(function(&$v) { $v++; });
        $this->assertSame('15.45.35.46.57.68', $test->joinString(function($v) { return $v; }, '.'));

        foreach($test->generator() as $item) { $item++; } unset($item);
        $this->assertSame('15.45.35.46.57.68', $test->joinString(function($v) { return $v; }, '.'));

        foreach($test->generator() as &$item) { $item++; } unset($item);
        $this->assertSame('16.46.36.47.58.69', $test->joinString(function($v) { return $v; }, '.'));

        $temp = $test->getAt(0);
        $this->assertSame(16, $temp);
        $temp++;
        $this->assertSame(16, $test->getAt(0));

        $temp = &$test->getAt(0);
        $temp++;
        $this->assertSame(17, $test->getAt(0));
    }


    public function testRemoving()
    {
        // lambda functions
        $testdata = array(
            array('a' => 'foo', 'b'=>'bar', 'c'=>'foobar', 'd'=>'barfoo'),
            array('a' => 'foo2', 'b'=>'bar2', 'c'=>'foobar2', 'd'=>'barfoo2'),
            array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
            array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
        );

        $list = \PerrysLambda\ArrayList::asObjectArray($testdata);

        $second = $list->getAt(1);
        $this->assertSame('foo2', $second->a);
        $this->assertSame(1, $list->indexOfValue($second));
        $list->removeValue($second);
        $this->assertSame(-1, $list->indexOfValue($second));

        $next = $list->getAt(1);
        $this->assertSame('1', $next->a);
        $this->assertSame(1, $list->indexOfValue($next));
        $list->removeValue($next);
        $this->assertSame(-1, $list->indexOfValue($next));

        $more = $list->getAt(1);
        $this->assertSame(1, $list->indexOfValue($more));
        $list->removeKey(3);
        $this->assertSame(-1, $list->indexOfValue($more));

        $list->removeAt(0);
        $this->assertSame(0, $list->length());

        // array access functions
        $unsetlist = \PerrysLambda\ArrayList::asObjectArray($testdata);
        $item = $unsetlist->first();

        $this->assertSame(true, $item->exists('a'));
        $this->assertSame(true, isset($item->a));
        // $this->assertSame(true, array_key_exists('a', $item)); not supported by arrayaccess
        $this->assertSame('foo', $item->a);

        unset($item['a']);
        $this->assertSame(false, $item->exists('a'));

        $this->assertSame(4, $unsetlist->length());
        unset($unsetlist[0]);
        $this->assertSame(3, $unsetlist->length());

    }


    public function testAutoconverting()
    {
        $testdata = array(
            array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
        );

        $list = \PerrysLambda\ArrayList::asObjectArray($testdata);

        $this->assertSame(true, $list->first() instanceof \PerrysLambda\ObjectArray);
        $this->assertSame($testdata[0], $list->first()->toArray());
        $this->assertSame('1', $list->first()->a);

        $temp = array('hihi' => 'haha', 'foo' => 'bar');
        $list->add($temp);

        $this->assertSame(true, $list->last() instanceof \PerrysLambda\ObjectArray);
        $this->assertSame($temp, $list->last()->toArray());
        $this->assertSame('haha', $list->last()->hihi);
    }


    public function testSerializeDeserialize()
    {
        $testdata = array(
            array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
            array('e' => '1', 'f'=>'2', 'g'=>'3', 'h'=>'4'),
        );

        $list = \PerrysLambda\ArrayList::asObjectArray($testdata);

        $this->assertEquals($testdata, $list->serialize());
    }


    public function testGroupbyAndDistinct()
    {
        $testdata = array(
            array('a' => 'foo', 'b'=>'bar', 'c'=>'foobar', 'd'=>'barfoo'),
            array('a' => 'foo2', 'b'=>'bar2', 'c'=>'foobar2', 'd'=>'barfoo2'),
            array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
            array('a' => '1', 'b'=>'2', 'c'=>'3.5', 'd'=>'4'),
        );

        $expecteddistinct = array(
            array('a' => 'foo', 'b'=>'bar', 'c'=>'foobar', 'd'=>'barfoo'),
            array('a' => 'foo2', 'b'=>'bar2', 'c'=>'foobar2', 'd'=>'barfoo2'),
            array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
        );

        $list = \PerrysLambda\ArrayList::asObjectArray($testdata);

        $distinct = $list->distinct(function($v) { return $v->a; });
        $this->assertEquals($expecteddistinct, $distinct->serialize());

        $groupby = $list->groupBy(function($v) { return $v->a; });
        $this->assertSame(1, $groupby['foo']->length());
        $this->assertSame(1, $groupby['foo2']->length());
        $this->assertSame(2, $groupby['1']->length());
    }

    public function testDiffrence()
    {
        $test1 = array(
            array("a" => "grün", "rot", "blau"),
            array("b" => "grün", "gelb", "rot"),
        );

        $test2 = array(
            array("a" => "blau", "rot", "blau"),
            array("c" => "grün", "gelb", "rot"),
        );

        $expected = array(
            array("a" => "grün", "rot", "blau"),
            array("b" => "grün", "gelb", "rot"),
        );

        $list1 = \PerrysLambda\ArrayList::asObjectArray($test1);
        $list2 = \PerrysLambda\ArrayList::asObjectArray($test2);

        var_dump($list1->intersect($list2)->serialize());

        $this->assertEquals($expected, $list1->intersect($list2)->serialize());
    }


}
