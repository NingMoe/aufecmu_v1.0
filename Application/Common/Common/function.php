<?php
/*
 * authcode 修复版本
 * 解决原版的authcode函数代码可能会生成+、/、&这样的字符，
 * 导致通过URL传值取回时被转义，导致无法解密的问题
 * */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

    if($operation == 'DECODE') {
        $string = str_replace('[a]','+',$string);
        $string = str_replace('[b]','&',$string);
        $string = str_replace('[c]','/',$string);
    }
    $ckey_length = 4;
    $key = md5($key ? $key : C("OWN_KEY"));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {

            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        $ustr = $keyc.str_replace('=', '', base64_encode($result));
        $ustr = str_replace('+','[a]',$ustr);
        $ustr = str_replace('&','[b]',$ustr);
        $ustr = str_replace('/','[c]',$ustr);
        return $ustr;
    }
}

/*
 * 页面初始化函数,cookie超时时间为4.5mins
 * 兼容用户第一次激活会员卡
 * 设置当前用户的 card_id、encrypt_code、openid 到 cookie 中
 * */
function bindInit() {
    $code=I("get.code");
    if( !empty($code) && $code != session("code") ) {
//        setcookie("code",$code,time()+270);
        session("code",$code);
        session("codeTime",time()+270);
    }
    if( isset($_GET['card_id']) && isset($_GET['encrypt_code']) && isset($_GET['openid']) ) {
        setcookie("card_id",$_GET['card_id'],time()+270);
        setcookie("encrypt_code",$_GET['encrypt_code'],time()+270);
        session("openid",$_GET['openid']);//用户基于服务号的openid，设置在session中作为全局判定的openid
    }
    //公众号接入部分的兼容
    if( isset($_GET['userid']) && isset($_GET['pubid'])) {
        setcookie("dyhOpenid",authcode($_GET['userid'],"DECODE", C("OWN_KEY")),time()+270);
        setcookie("mediaid",authcode($_GET['pubid'],"DECODE", C("OWN_KEY")),time()+270);
    }
}

/*
 * 构建静默授权的url
 * */
function getJmUrl($redirect_uri) {
    $baseUrl="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=%s&response_type=code&scope=snsapi_base&state=#wechat_redirect";
    return sprintf($baseUrl,$redirect_uri);
}

/*
 * 传入option获得跳转url
 * */
function  getUrlByOption($option) {
    switch($option){
        case "save":
            return U("Home/Ykt/index","",false);
        case "electric":
            return U("Home/Ykt/electric","",false);
        case "netPay":
            return U("Home/Ykt/netPay","",false);
        case "password":
            return U("Home/Ykt/password","",false);
        case "campus":
            return U("Home/Ykt/campus","",false);
        default:
            return U("Home/Ykt/index","",false);
    }
}


function getIPaddress () {
    $IPaddress='';
    if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $IPaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $IPaddress = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $IPaddress = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){
            $IPaddress = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $IPaddress = getenv("HTTP_CLIENT_IP");
        } else {
            $IPaddress = getenv("REMOTE_ADDR");
        }
    }
    return ipton($IPaddress);
}


function ipton($ip) {
    $ip_arr=explode('.',$ip);//分隔ip段
    $ipstr="";
    foreach ($ip_arr as $value)
    {
        $iphex=dechex($value);//将每段ip转换成16进制
        if(strlen($iphex)<2)//255的16进制表示是ff，所以每段ip的16进制长度不会超过2
        {
            $iphex='0'.$iphex;//如果转换后的16进制数长度小于2，在其前面加一个0
            //没有长度为2，且第一位是0的16进制表示，这是为了在将数字转换成ip时，好处理
        }
        $ipstr.=$iphex;//将四段IP的16进制数连接起来，得到一个16进制字符串，长度为8
    }
    return hexdec($ipstr);//将16进制字符串转换成10进制，得到ip的数字表示
}

/*
 * 计算显示在主界面上的时间
 * 最多表示到前天，剩下的直接显示为对应的日期
 * */
function getTime($time) {
    $nowTime = time();
    $differ=$nowTime-$time;
    if($differ >= 0 && $differ < 60) {
        //一分钟内
        return "刚刚";
    }
    else if($differ >= 60 && $differ < 3600) {
        //一小时内
        return (int)($differ/60)." 分钟前";
    }
    else if($differ >= 3600 && $differ < 86400) {
        //当天
        return (int)($differ/3600)." 小时前";
    }
    else if($differ >= 86400 && $differ < 172800) {
        //昨天
        return "昨天 ".date("G:i",$time);
    }
    else if($differ >= 172800 && $differ < 259200) {
        //前天
        return "前天 ".date("G:i",$time);
    }
    else {
        //大前天开始全部使用日期
        return date("m-d",$time);
    }
}

?>