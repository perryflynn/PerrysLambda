<?php

$rows = explode("\n", file_get_contents(__DIR__."/access.txt"));
$result = array();

foreach($rows as $row)
{
    $match = array();
    if(preg_match('/^(?P<ip>[^\s]+) - (?P<username>[^\s]+) \[(?P<timestamp>.*?)\] "(?P<uri>.*?)" (?P<statuscode>[0-9]+) (?P<bytes>[0-9]+) "(?P<referer>.*?)" "(?P<agent>.*?)"$/', $row, $match)===1)
    {
        $temp = array();
        foreach($match as $key => $value)
        {
            if(!is_numeric($key)) $temp[$key] = $value;
        }
        $result [] = $temp;
    }
}

$i=0;
$users = array("userfoo", "userbar", "userfoobar", "userbarfoo");
foreach($result as &$row)
{
    $row['ip'] = substr($row['ip'], 0, strrpos($row['ip'], '.')+1)."xxx";
    if($row['username']!="-")
    {
        $row['username'] = $users[mt_rand(0, count($users)-1)];
    }
}
unset($row);

echo json_encode($result);
