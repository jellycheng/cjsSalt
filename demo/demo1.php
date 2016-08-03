date_default_timezone_set("Asia/Shanghai");
require __DIR__ . '/../vendor/autoload.php';

$host = '10.59.80.11';
$port = '8887';
$user = 'saltapi';
$pwd = 'saltapi';
$obj = new \CjsSalt\CallSaltApi($host, $port);
$data = $obj->login($user, $pwd);
var_export($data);
/**
array (
    'return' =>
        array (
            0 => array (
                'perms' =>  array ( 0 => '.*',   ),
                'start' => 1470212630.425153,
                'token' => '8110d66c5ebc216d997cb8cb721c0d4efa58b10f',
                'expire' => 1470255830.425154,
                'user' => 'saltapi',
                'eauth' => 'pam',
            ),
        ),
 )
 */
echo PHP_EOL;
$token = $obj->getToken($user, $pwd);
echo $token . PHP_EOL;
$token = $obj->getToken($user, $pwd);
echo $token . PHP_EOL;
