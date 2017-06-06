<?php

use PerrysLambda\ScalarProperty;
use PerrysLambda\StringProperty;
use PerrysLambda\ArrayList;
use PerrysLambda\ObjectArray;
use PerrysLambda\Converter\ObjectArrayListConverter;
use PerrysLambda\Property;

class LambdaTest extends PHPUnit_Framework_TestCase
{

    public function testProperty()
    {
        $prop = new Property("foo");
        $this->assertEquals($prop, $prop->newInstance("foo"));
        $this->assertSame("foo", $prop->getData());
    }

    public function testScalar()
    {
        $s = new ScalarProperty("Zähn € zahme Ziegen  zögen zwei Zentner Zücker zum Zoö!", 'UTF-8');

        $this->assertSame(true, $s->startsWith("Zä"));
        $this->assertSame(false, $s->startsWith("zä"));
        $this->assertSame(true, $s->startsWithI("zä"));
        $this->assertSame(true, $s->endsWith(' Zoö!'));
        $this->assertSame(false, $s->endsWith(' zoö!'));
        $this->assertSame(true, $s->endsWithI(' zoö!'));
        $this->assertSame(55, $s->length());
        $this->assertSame(3, count($s->split('ö')));
        $this->assertSame(' Zoö!', $s->substr(-5)->toString());
        $this->assertSame(1, $s->indexOf('ä'));
        $this->assertSame(-1, $s->indexOf('Ä'));
        $this->assertSame(1, $s->indexOfI('Ä'));
        $this->assertSame(54, $s->lastIndexOf('!'));
        $this->assertSame(51, $s->lastIndexOfI('zoö!'));
        $this->assertSame(false, $s->contains('asdf'));
        $this->assertSame(true, $s->contains('zwei'));
        $this->assertSame(false, $s->contains('ZWEI'));
        $this->assertSame(true, $s->containsI('ZWEI'));
        $this->assertSame(false, $s->isNullOrWhitespace());

        $split = $s->split(' ');
        $this->assertSame(11, $split->length());
        $split = $split->where(function($v) { return !$v->isNullOrWhitespace(); });
        $this->assertSame(10, $split->length());
        $this->assertSame(true, $split->first() instanceof \PerrysLambda\StringProperty);

        $str = new \PerrysLambda\StringProperty("");
        $this->assertSame(true, $str->isEmpty());
        $this->assertSame(false, $str->isNull());
        $this->assertSame(true, $str->isNullOrEmpty());
        $this->assertSame(true, $str->isNullOrWhitespace());

        $str->setData(null);
        $this->assertSame(false, $str->isEmpty());
        $this->assertSame(true, $str->isNull());
        $this->assertSame(true, $str->isNullOrEmpty());
        $this->assertSame(true, $str->isNullOrWhitespace());

        $str->setData("        ");
        $this->assertSame(true, $str->isNullOrWhitespace());
        $this->assertSame(true, $str->trim()->isEmpty());

        $str->setData("     a       ");
        $this->assertSame(true, $str->startsWith(" "));
        $this->assertSame(true, $str->ltrim()->startsWith("a"));
        $this->assertSame(8, $str->ltrim()->length());
        $this->assertSame(true, $str->ltrim()->endsWith(' '));
        $this->assertSame(true, $str->rtrim()->endsWith("a"));
        $this->assertSame(6, $str->rtrim()->length());
        $this->assertSame(true, $str->rtrim()->startsWith(' '));
        $this->assertNotSame('a', $str->trim()->toUpper()->toString());
        $this->assertSame('A', $str->trim()->toUpper()->toString());

        $str->setData("       A    ");
        $this->assertNotSame('A', $str->trim()->toLower()->toString());
        $this->assertSame('a', $str->trim()->toLower()->toString());

        $str->setData('12');
        $this->assertSame('   12', $str->padLeft(5)->toString());
        $this->assertSame('00012', $str->padLeft(5, '0')->toString());
        $this->assertSame('12   ', $str->padRight(5)->toString());
        $this->assertSame('12000', $str->padRight(5, '0')->toString());
        $this->assertSame(' 12  ', $str->padBoth(5)->toString());
        $this->assertSame('01200', $str->padBoth(5, '0')->toString());

        $integer = new ScalarProperty('4211');
        $this->assertSame(4211, $integer->toInt());
        $this->assertSame(4211, $integer->toNumeric());

        $float = new ScalarProperty('50.4');
        $realfloat = new ScalarProperty(50.4);
        $null = new ScalarProperty(null);
        $bool = new ScalarProperty(true);

        $this->assertSame(true, $float->isNumeric());
        $this->assertSame(true, $float->isString());
        $this->assertSame(50.4, $float->toNumeric());
        $this->assertSame(50.4, $float->toFloat());
        $this->assertSame(false, $float->isFloat());
        $this->assertSame(false, $float->isInt());
        $this->assertSame(true, $realfloat->isFloat());
        $this->assertSame(true, $realfloat->isNumeric());
        $this->assertSame(false, $realfloat->isInt());
        $this->assertSame(false, $realfloat->isString());
        $this->assertSame(false, $realfloat->isNull());
        $this->assertSame(true, $null->isNull());
        $this->assertSame(false, $null->isBool());
        $this->assertSame(true, $bool->isBool());
        $this->assertSame(true, $float->toBool());

        $string = new ScalarProperty(50.3);
        $this->assertSame('50.3', $string->toString());

        $this->assertSame(true, (new StringProperty('foo'))->equals("foo"));
        $this->assertSame(true, (new StringProperty('foo'))->equals(new StringProperty('foo')));
        $this->assertSame(false, (new StringProperty('foo'))->equals("bar"));
        $this->assertSame(false, (new StringProperty('foo'))->equals(new StringProperty('bar')));

        $params = new StringProperty("1|2|3|45|321");
        $this->assertSame(true, $params->isMatch('/^[0-9\|]+$/'));
        $this->assertSame(false, $params->isMatch('asdf'));

        $this->assertSame(null, $params->match('/foo/'));

        $match = $params->match('/^([0-9]+)\|(?P<sec>[0-9]+)\|([0-9]+)/');
        $this->assertSame('2', $match->{2});
        $this->assertSame('2', $match->sec);
        $this->assertSame('1|2|3', $match[0]);

        $this->assertSame(null, $params->matchAll('/foo/'));

        $matches = $params->matchAll('/\|?(?P<number>[0-9]+)\|?/');
        $this->assertEquals(array('1', '2', '3', '45', '321'), $matches->select('number')->toArray());
    }


    public function testEmpty()
    {
        $basic = new ArrayList();
        $this->assertSame(0, $basic->length());
    }


    public function testLambda()
    {
        $basicdata = array(1,2,3,4,5,6,7,8,9);
        $basic = new ArrayList($basicdata);
        $all = new ArrayList(array(1, 1, 1, 1, 1));
        $named = new ObjectArray(array('foo'=>'bar', 'foo2'=>'bar2', 'foobar'=>'barfoo'));
        $empty = new ObjectArray();

        // basics
        $this->assertSame(1, $basic->first());
        $this->assertSame(1, $basic->firstOrDefault(42));
        $this->assertSame(42, $empty->firstOrDefault(42));
        $this->assertSame(9, $basic->last());
        $this->assertSame(9, $basic->lastOrDefault(42));
        $this->assertSame(42, $empty->lastOrDefault(42));
        $this->assertSame(2, $basic->skip(1)->first());
        $this->assertSame(45, $basic->sum(function($v) { return $v; }));
        $this->assertSame(1, $basic->min(function($v) { return $v; }));
        $this->assertSame(9, $basic->max(function($v) { return $v; }));
        $this->assertSame(5, $basic->avg(function($v) { return $v; }));
        $this->assertSame('1.2.3.4.5.6.7.8.9', $basic->joinString(function($v) { return $v; }, '.'));
        $this->assertSame(5, $basic->skip(1)->take(2)->sum(function($v) { return $v; }));
        $this->assertSame(2, $basic->skip(1)->take(1)->single());
        $this->assertSame(2, $basic->skip(1)->take(1)->singleOrDefault(42));
        $this->assertSame(42, $basic->singleOrDefault(42));
        $this->assertEquals(array(8,9), $basic->take(-2)->toArray());
        $this->assertSame($named->getNames(), $named->getKeys());

        // take failovers
        $this->assertSame('3.4.5.6.7.8.9', $basic->skip(2)->take(999)->joinString(function($v) { return $v; }, '.'));
        $this->assertSame('2.3.4.5.6.7.8.9', $basic->skip(1)->take(-999)->joinString(function($v) { return $v; }, '.'));

        // basics as string
        $this->assertSame(45, $basic->sum());
        $this->assertSame(1, $basic->min());
        $this->assertSame(9, $basic->max());
        $this->assertSame(5, $basic->avg());
        $this->assertSame('1.2.3.4.5.6.7.8.9', $basic->joinString(null, '.'));

        // any / all
        $this->assertSame(true, $basic->any(function($v) { return $v===1; }));
        $this->assertSame(false, $basic->all(function($v) { return $v===1; }));
        $this->assertSame(true, $all->all(function($v) { return $v===1; }));
        $this->assertSame(true, $all->any(function($v) { return $v===1; }));
        $this->assertSame(false, $all->any(function($v) { return $v===2; }));

        // wherefirst
        $this->assertSame(5, $basic->whereFirst(function($v) { return $v>4; }));
        $this->assertSame(5, $basic->whereFirstOrDefault(function($v) { return $v>4; }, 42));
        $this->assertSame(42, $basic->whereFirstOrDefault(function($v) { return $v>99; }, 42));

        // countable interface
        $this->assertSame(9, count($basic));
        $this->assertSame(9, $basic->count());
        $this->assertSame(9, $basic->length());

        // sorting
        // array(1,2,3,4,5,6,7,8,9)
        // array(4,3,2,1,9,8,7,6,5)

        $sorted = $basic
            ->order(function($v) { return ($v>=5 ? 1 : 0); })
            ->thenByDesc(function($v) { return $v; })
            ->toList();

        $this->assertEquals(array(4,3,2,1,9,8,7,6,5), $sorted->toArray());

        $dsorted = $basic
            ->orderDesc(function($v) { return ($v>=5 ? 1 : 0); })
            ->thenBy(function($v) { return $v; })
            ->toList();

        $this->assertEquals(array(5,6,7,8,9,1,2,3,4), $dsorted->toArray());

        // Find key
        $this->assertSame(1, $named->indexOfKey('foo2'));
        $this->assertSame(-1, $named->indexOfKey('4211'));

        // serialize without converter
        $this->assertEquals($basicdata, $basic->serialize());

        foreach($basic->serializeGenerator() as $key => $row)
        {
            $this->assertEquals($basicdata[$key], $row);
        }

        // string conversion
        $expected = 'a:3:{s:3:"foo";s:3:"bar";s:4:"foo2";s:4:"bar2";s:6:"foobar";s:6:"barfoo";}';
        $this->assertSame($expected, $named->toString());
        $this->assertSame($expected, "".$named);
    }


    public function testLambdaByString()
    {
        // lambda functions
        $testdata = array(
            array('a' => 2, 'b'=>'foo', 'c'=>'foobar', 'd'=>'barfoo'),
            array('a' => 3, 'b'=>'bar', 'c'=>'foobar2', 'd'=>'barfoo2'),
            array('a' => 4, 'b'=>'foo', 'c'=>'3', 'd'=>'4'),
            array('a' => 5, 'b'=>'foo', 'c'=>'3', 'd'=>'4'),
        );

        $list = ArrayList::asObjectArray($testdata);

        $this->assertSame(14, $list->sum('a'));
        $this->assertSame(2, $list->min('a'));
        $this->assertSame(5, $list->max('a'));
        $this->assertSame(3.5, $list->avg('a'));

        $this->assertSame(3, $list->groupBy('b')->foo->length());
        $this->assertSame(1, $list->groupBy('b')->bar->length());
        $this->assertSame(2, $list->groupBy('b')->length());

        $this->assertSame(2, $list->distinct('b')->length());

        $this->assertEquals(array(2, 3, 4, 5), $list->select('a')->toArray());
        $this->assertSame('2,3,4,5', $list->joinString('a', ','));

        $ordertest = $list->order('b')->thenBy('a')->toList()->select('a')->toArray();
        $this->assertSame(array(3, 2, 4, 5), $ordertest);

        $ordertestdesc = $list->orderDesc('b')->thenByDesc('a')->toList()->select('a')->toArray();
        $this->assertSame(array(5, 4, 2, 3), $ordertestdesc);

        // Method call
        $expected = 'a:4:{s:1:"a";i:3;s:1:"b";s:3:"bar";s:1:"c";s:7:"foobar2";s:1:"d";s:7:"barfoo2";}';
        $this->assertSame($expected, $list->select('toString')->getAt(1));
    }


    public function testSelectMany()
    {
        $data = array(
            array('a'=>'foo', 'b'=>array(1,2,3)),
            array('a'=>'bar', 'b'=>array(3,4,5)),
            array('a'=>'barfoo', 'b'=>6),
            array('a'=>'foobar', 'b'=>array(7,8,9)),
        );

        $list = ArrayList::asObjectArray($data);

        $this->assertSame(array(1,2,3,3,4,5,6,7,8,9), $list->selectMany('b')->toArray());
    }


    public function testScalarAccess()
    {
        $named = new ObjectArray(array('foo'=>'bar', 'foo2'=>'bar2', 'foobar'=>'barfoo'));

        $this->assertSame(true, $named->fooScalar instanceof ScalarProperty);
        $this->assertSame(true, $named->getScalar('foo') instanceof ScalarProperty);
        $this->assertSame('bar', $named->getScalarAt(0)->toString());
    }


    public function testReferences()
    {
        $test = new ObjectArray();

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

        $test[] = 999;
        $this->assertSame(999, $test->last());
        $this->assertSame(999, $test[0]);
        $this->assertSame(true, isset($test[0]));
    }


    public function testDefaults()
    {
        $test = new ArrayList(array(1, 2, 3, 4));
        $this->assertSame(99, $test->getAt(4, 99));
        $this->assertSame(99, $test->get(4, 99));
        $this->assertEquals(new ScalarProperty(99), $test->getScalarAt(4, 99));
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

        $list = ArrayList::asObjectArray($testdata);

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
        $unsetlist = ArrayList::asObjectArray($testdata);
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

        $list = ArrayList::asObjectArray($testdata);

        $this->assertSame(true, $list->first() instanceof ObjectArray);
        $this->assertSame($testdata[0], $list->first()->toArray());
        $this->assertSame('1', $list->first()->a);

        $temp = array('hihi' => 'haha', 'foo' => 'bar');
        $list->add($temp);

        $this->assertSame(true, $list->last() instanceof ObjectArray);
        $this->assertSame($temp, $list->last()->toArray());
        $this->assertSame('haha', $list->last()->hihi);
    }


    public function testSerializeDeserialize()
    {
        $testdata = array(
            array('a' => '1', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
            array('e' => '1', 'f'=>'2', 'g'=>'3', 'h'=>'4'),
        );

        $list = ArrayList::asObjectArray($testdata);
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

        $list = ArrayList::asObjectArray($testdata);

        $distinct = $list->distinct(function($v) { return $v->a; });
        $this->assertEquals($expecteddistinct, $distinct->serialize());

        $groupby = $list->groupBy(function($v) { return $v->a; });
        $this->assertSame(1, $groupby['foo']->length());
        $this->assertSame(1, $groupby['foo2']->length());
        $this->assertSame(2, $groupby['1']->length());
    }

    public function testIntersectExpect()
    {
        $test1 = array(
            array("a"=>"foo", "b"=>"bar", "c"=>"foobar"),
            array("a"=>"foo", "b"=>"bar", "c"=>"barfoo"),
        );

        $test2 = array(
            array("a"=>"foo", "b"=>"bar", "c"=>"foobar"),
            array("a"=>"helloworld", "b"=>"bar", "c"=>"barfoo"),
        );

        $expectedin = array(
            array("a"=>"foo", "b"=>"bar", "c"=>"foobar"),
        );

        $expectedex = array(
            array("a"=>"foo", "b"=>"bar", "c"=>"barfoo"),
            array("a"=>"helloworld", "b"=>"bar", "c"=>"barfoo"),
        );

        $list1 = ArrayList::asObjectArray($test1);
        $list2 = ArrayList::asObjectArray($test2);

        $this->assertEquals($expectedin, $list1->intersect($list2)->serialize());
        $this->assertEquals($expectedex, $list1->except($list2)->serialize());
    }

    public function testIntersectExpectSimple()
    {
        $test1 = array(1, 2, 3, 4, 5, 6, 7);
        $test2 = array(9, 8, 7, 6, 5, 4, 3);

        $list1 = new ArrayList($test1);
        $list2 = new ArrayList($test2);

        $expectedin = array(2=>3, 3=>4, 4=>5, 5=>6, 6=>7);
        $expectedex = array(1, 2, 9, 8);

        $this->assertEquals($expectedin, $list1->intersect($list2)->serialize());
        $this->assertEquals($expectedex, $list1->except($list2)->serialize());
    }

    public function testFieldConverter()
    {
        $data = array(
            array("date"=>"2016-07-08T08:12:20+0200", "amount"=>"42", "important"=>"true"),
            array("date"=>"2016-07-08T10:20:23+0000", "amount"=>"123.456", "important"=>"false"),
            array("date"=>"2016-07-08T10:22:25+0000", "amount"=>"123", "important"=>"asdf"),
        );

        $conv = new ObjectArrayListConverter();
        $conv->setArraySource($data);

        $conv->getItemConverter()->setFieldSerializer('date', \PerrysLambda\Serializer\DateTimeSerializer::fromIsoFormat(new \DateTimeZone("Europe/Berlin")));
        $conv->getItemConverter()->setFieldSerializers(array(
            'amount' => new \PerrysLambda\Serializer\NumberSerializer(),
            'important' => new \PerrysLambda\Serializer\BooleanSerializer(),
        ));

        $list = new ArrayList($conv);

        $this->assertSame(true, $list->first()->date instanceof \DateTime);
        $this->assertSame('2016-07-08T08:12:20+0200', $list[0]->date->format(\DateTime::ISO8601));

        $this->assertSame(42, $list->first()->amount);
        $this->assertSame(123.456, $list->getAt(1)->amount);

        $this->assertSame(true, $list->first()->important);
        $this->assertSame(false, $list->getAt(1)->important);
        $this->assertSame(false, $list->getAt(2)->important);

        $serialized = $list->serialize();

        $this->assertSame('2016-07-08T08:12:20+0200', $serialized[0]['date']);
        $this->assertSame($data[0]['amount'], $serialized[0]['amount']);
        $this->assertSame($data[0]['important'], $serialized[0]['important']);
        $this->assertSame("false", $serialized[2]['important']);

        $filterserialized = $list
            ->where(function($r) { return $r->amount===42; })
            ->serialize();

        $this->assertSame(true, is_array($filterserialized) && count($filterserialized)===1);
        $this->assertEquals($filterserialized[0], $data[0]);
        $this->assertSame('2016-07-08T08:12:20+0200', $filterserialized[0]['date']);

        $this->assertEquals($list->first()->serialize(), $data[0]);
    }

    public function testCustomTypeConverter()
    {
        $data = array(
            array("date"=>"2016-07-08T10:12:20+0400", "amount"=>"42", "important"=>"true"),
            array("date"=>"2016-07-08T10:20:23+0000", "amount"=>"123.456", "important"=>"false"),
            array("date"=>"2016-07-08T10:22:25+0000", "amount"=>"123", "important"=>"asdf"),
        );

        $list = ArrayList::asType('\PerrysLambda\ObjectArray', $data);

        $this->assertSame(true, $list->first() instanceof ObjectArray);
        $this->assertSame($data[1]['amount'], $list->getAt(1)->amount);
        $this->assertEquals($data, $list->serialize());

        foreach($list->serializeGenerator() as $key => $row)
        {
            $this->assertEquals($data[$key], $row);
        }

    }

    public function testStringList()
    {
        $list = ArrayList::asType('\PerrysLambda\ScalarProperty', array("foo", "bar", "foobar", "barfoo"));

        $this->assertSame(true, $list->first() instanceof ScalarProperty);
        $this->assertEquals(array("foo", "foobar"), $list->where(function($v) { return $v->startsWith('foo'); })->serialize());
    }

    public function testConverterDefaults()
    {
        $data = array(
            array("date"=>"2016-07-08T10:12:20+0400", "amount"=>"42", "important"=>"true", "foo"=>'fooo'),
            array("date"=>"2016-07-08T10:20:23+0000", "amount"=>"123.456", "important"=>"false"),
            array("date"=>"2016-07-08T10:22:25+0000", "amount"=>"123", "important"=>"asdf"),
        );

        $conv = new ObjectArrayListConverter();
        $conv->setArraySource($data);

        $conv->setDefaults(array(
            'foo' => 'bar',
        ));

        $list = new ObjectArray($conv);

        $this->assertSame('fooo', $list->first()->foo);
        $this->assertSame('bar', $list->getAt(1)->foo);
    }

    public function testListConverterEmpty()
    {
        $conv = new ObjectArrayListConverter();
        $conv->setArraySource();
        $list = new ObjectArray($conv);

        $this->assertSame(0, $list->count());
    }

    public function testSeekableIterator()
    {
        // lambda functions
        $testdata = array(
            array('a' => 'a', 'b'=>'bar', 'c'=>'foobar', 'd'=>'barfoo'),
            array('a' => 'b', 'b'=>'bar2', 'c'=>'foobar2', 'd'=>'barfoo2'),
            array('a' => 'c', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
            array('a' => 'd', 'b'=>'2', 'c'=>'3', 'd'=>'4'),
        );

        $list = ArrayList::asObjectArray($testdata);

        $i=0;
        $list->rewind();
        while($list->valid())
        {
            $this->assertSame($testdata[$i]['a'], $list->current()->a);
            $i++;
            $list->next();
        }

        $list->seek(2);
        $this->assertSame('c', $list->current()->a);
    }

}
