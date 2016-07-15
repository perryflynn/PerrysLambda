<?php

use PerrysLambda\IO\Directory;
use PerrysLambda\IO\File;


class IOTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \PerrysLambda\IO\IOException
     */
    public function testAntiDirectory()
    {
        Directory::fromPath(new File("kljashfkjfhkafhahkdf"));
    }

    public function testDirectory()
    {
        $dir = Directory::fromPath(new File(__DIR__."/../"));
        $test = $dir->getByBasename("test");

        $this->assertSame(true, $test instanceof File);
        $this->assertSame(true, $test->isDir());
        $this->assertSame(false, $test->isFile());
        $this->assertSame('test'.DIRECTORY_SEPARATOR, $test->getRootRelativePath());
        $this->assertSame('test', $test->getBasename());

        $testdir = $test->openDir();

        $this->assertSame(true, $testdir instanceof Directory);
        $this->assertSame(true, $testdir->getPath()->isChildOf($dir->getPath()));
        $this->assertSame(false, $dir->getPath()->isChildOf($testdir->getPath()));
    }

}
