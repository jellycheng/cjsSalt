<?php
date_default_timezone_set("Asia/Shanghai");
require __DIR__ . '/../vendor/autoload.php';

$host = '10.59.80.11';
$port = '8887';
$user = 'saltapi';
$pwd = 'saltapi';
$obj = new \CjsSalt\CallSaltApi($host, $port);
echo PHP_EOL;
$token = $obj->getToken($user, $pwd);
echo $token . PHP_EOL;
$param = [
    'fun'=>'test.ping',
    //'tgt'=>'aaz-dev002',
    //'args'=>'',
    'token'=>$token
];
$resData = $obj->callRunApi4Token($param);
var_export($resData);
echo  PHP_EOL;
