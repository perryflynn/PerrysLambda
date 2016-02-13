<?php

// Classloader
spl_autoload_register(function($class)
{
    $file = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR.str_replace("\\", DIRECTORY_SEPARATOR, $class).".php";
    if(is_file($file))
    {
        require_once($file);
    }
});

class Stopwatch
{
    private $start;
    private $stop;
    public function start() { $this->start = microtime(true); $this->stop=null; return $this; }
    public function stop() { $this->stop=microtime(true); return $this; }
    public function result() { return number_format(($this->stop-$this->start), 3)."s"; }
}

class L
{
    public static $lastmem=0;

    public static function line()
    {
        $mem = memory_get_usage();
        $diffmem = memory_get_usage()-self::$lastmem;
        self::$lastmem = $mem;
        $args = func_get_args();
        echo implode(" ", $args)." [".number_format(($mem/1024), 2)."KB / ".number_format(($diffmem/1024), 2)."KB]\n";
    }

    public static function vd($var)
    {
        ob_start();
        var_dump($var);
        $c = ob_get_contents();
        ob_end_clean();
        echo str_replace(array("\n", "\r"), array("", ""), $c)."\n";
    }

    public static function vdl($var)
    {
        echo "\n";
        self::vd($var);
        echo "\n";
    }
}
