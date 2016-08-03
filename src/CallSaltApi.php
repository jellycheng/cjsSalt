<?php
namespace CjsSalt;
class CallSaltApi
{

    protected $host = 'localhost';

    protected $port = '8000';

    protected $user = '';

    protected $pwd = '';

    protected $eauth = 'pam';

    protected $token = '';
    protected $token_expire = ''; //token过期时间戳

    public function __construct($host = '', $port = '8000', $user = 'saltapi', $pwd = 'saltapi')
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pwd = $pwd;
    }

    public function login($user = null, $pwd = null, $eauth = 'pam')
    {
        $resData = [];
        if (is_null($user)) {
            $user = $this->user;
        }
        if ($user && preg_match("#[\'\"&<>]#i", $user)) {
            //帐号名不支持特殊字符
            return $resData;
        }
        if (is_null($pwd)) {
            $pwd = $this->pwd;
        }
        if ($pwd && preg_match("#[\'\"&<>]#i", $pwd)) {
            //密码不支持特殊字符
            return $resData;
        }
        if (!$eauth) {
            $eauth = $this->eauth;
        }
        $url = 'https://' . $this->host . ':' . $this->port . '/login';
        $field = sprintf('username=%s&password=%s&eauth=%s', $user, $pwd, $eauth);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);

        if (curl_errno($ch)) {
            //发生错误 上报 todo
            //echo curl_error($ch);
        }
        if ($res) {
            $resData = json_decode($res, true);
        }
        curl_close($ch);
        return $resData;
    }

    public function getToken($user = null, $pwd = null, $eauth = 'pam')
    {
        if($this->token) {
            return $this->token;
        }
        $token = '';
        $resData = $this->login($user, $pwd, $eauth);
        if(!empty($resData) && isset($resData['return'][0]['token'])) {
            $token = $resData['return'][0]['token'];
            $this->token_expire = $resData['return'][0]['expire'];
            //echo date('Y-m-d H:i:s', $resData['return'][0]['expire']) . PHP_EOL;
        }
        $this->token = $token;
        return $this->token;
    }

    public function getTokenExpire() {
        return $this->token_expire;
    }

    /**
     *
     * @param array $param = ['tgt'=>'', 'fun'=>'', 'args'=>'', 'user'=>'', 'pwd'=>'', 'eauth => 'pam']
     * @return mixed
     */
    public function callRunApi($param=[])
    {
        $resData = '';
        if (isset($param['host']) && $param['host']) {
            $host = $param['host'];
        } else {
            $host = $this->host;
        }
        if (isset($param['port']) && !is_null($param['port'])) {
            $port = $param['port'];
        } else {
            $port = $this->port;
        }
        if (isset($param['user']) && !is_null($param['user'])) {
            $user = $param['user'];
        } else {
            $user = $this->user;
        }
        if ($user && preg_match("#[\'\"&<>]#i", $user)) {
            //帐号名不支持特殊字符
            return $resData;
        }
        if (isset($param['pwd']) && !is_null($param['pwd'])) {
            $pwd = $param['pwd'];
        } else {
            $pwd = $this->pwd;
        }
        if ($pwd && preg_match("#[\'\"&<>]#i", $pwd)) {
            //密码不支持特殊字符
            return $resData;
        }
        if (isset($param['eauth']) && !empty($param['eauth'])) {
            $eauth = $param['eauth'];
        } else {
            $eauth = $this->eauth;
        }
        if (isset($param['tgt']) && !empty($param['tgt'])) {
            $tgt = $param['tgt'];
        } else {
            $tgt = '*';
        }
        $fun = isset($param['fun'])?$param['fun']:'';
        $args = isset($param['args'])?$param['args']:null;
        if($port) {
            $_url = 'https://' . $host . ':' . $port . '/run';
        } else {
            $_url = 'https://' . $host . '/run';
        }
        $ch = curl_init($_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,
                    CURLOPT_HTTPHEADER,
                    array(
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Type: application/json'
                        )
                    );
        $requestBody = [
                        'client'=>'local',
                        'tgt'=>$tgt,
                        'fun'=>$fun,
                        //'arg'=>$args,
                        'username'=>$user,
                        'password'=>$pwd,
                        'eauth'=>$eauth
                    ];
        if(!is_null($args)) {
            $requestBody['arg'] = $args;
        }
/**
        $postField = '{
            "client": "local",
            "tgt": "'.$tgt.'",
            "fun": "'.$fun.'",
            "arg": "'.$args.'",
            "username": "'.$user. '",
            "password": "'.$pwd.'",
            "eauth": "pam"
        }';
 */
        //$postField = http_build_query($requestBody);
        $postField = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
        //echo $postField;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
        $result = curl_exec($ch);
        /**
        if (curl_errno($ch)) {
            //发生错误 上报 todo
            echo curl_error($ch);
        }
         */
        $result = json_decode($result, true);
        if ($tgt == '*') {
            return $result['return'][0];
        }
        return $result['return'][0][$tgt];
    }

    /**
     * @param array $param
     * @return mixed
     */
    public function callRunApi4Token($param=[])
    {
        if (isset($param['host']) && $param['host']) {
            $host = $param['host'];
        } else {
            $host = $this->host;
        }
        if (isset($param['port']) && !is_null($param['port'])) {
            $port = $param['port'];
        } else {
            $port = $this->port;
        }
        if (isset($param['token']) && !is_null($param['token'])) {
            $token = $param['token'];
        } else {
            $token = $this->token;
        }
        if (isset($param['eauth']) && !empty($param['eauth'])) {
            $eauth = $param['eauth'];
        } else {
            $eauth = $this->eauth;
        }
        if (isset($param['tgt']) && !empty($param['tgt'])) {
            $tgt = $param['tgt'];
        } else {
            $tgt = '*';
        }
        $fun = isset($param['fun'])?$param['fun']:'';
        $args = isset($param['args'])?$param['args']:null;
        if($port) {
            $_url = 'https://' . $host . ':' . $port . '/';  //通过token请求，就不需要run地址了
        } else {
            $_url = 'https://' . $host . '/';
        }
        $ch = curl_init($_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Type: application/json',
                'X-Auth-Token: ' . $token
            )
        );
        $requestBody = [
            'client'=>'local',
            'tgt'=>$tgt,
            'fun'=>$fun,
            //'arg'=>$args,
            'eauth'=>$eauth
        ];
        if(!is_null($args)) {
            $requestBody['arg'] = $args;
        }
         //$postField = http_build_query($requestBody);
        $postField = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
        $result = curl_exec($ch);
        /**
        if (curl_errno($ch)) {
            //发生错误 上报 todo
            echo curl_error($ch);
        }
        */
        $result = json_decode($result, true);
        if ($tgt == '*') {
            return $result['return'][0];
        }
        return $result['return'][0][$tgt];
    }


}
