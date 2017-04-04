<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/3/19
 * Time: 21:16
 */
namespace Home\Logic;

class XjLogic {

    /*
     * 初始化抓取学籍信息
     * */
    public function init() {
        $url="https://account.chsi.com.cn/passport/login";
        $cookie='JSESSIONID=0017283DD340863EA414274BE9FFCC40; __utma=65168252.721632942.1489931066.1489931066.1489931219.2; __utmc=65168252; __utmz=65168252.1489931219.2.2.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); __utmt=1; __utma=39553075.1926505127.1489930269.1489930269.1489932983.2; __utmb=39553075.14.10.1489932983; __utmc=39553075; __utmz=39553075.1489930269.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)';
        return $this->curlHttpRequest($url,$cookie,true);
    }

    public function loginXx() {
        $url="https://account.chsi.com.cn/passport/login;jsessionid=CA6CEC9B265D337AD41056B91B5ECECD?service=https%3A%2F%2Fmy.chsi.com.cn%2Farchive%2Fj_spring_cas_security_check";
        $postData=array(
            'lt'=>'_c31791C8C-624F-373C-F2F0-50062D7B8633_k4F3DD9FE-B1C6-DFF2-61F4-0244D3A62218',
            'username'=>'342401199606144279',
            'password'=>'weiwei66291',
            '_eventId'=>'submit',
            'submit'=>'%E7%99%BB%C2%A0%C2%A0%E5%BD%95',
        );
        return $this->curlHttpRequest($url,null,false,$postData);
    }

    /*
    Cookie:
    JSESSIONID=94854E98B33428E9E96279261A288634;
    __utma=65168252.1224168203.1489913169.1489913169.1489913169.1;
    __utmc=65168252;
    __utmz=65168252.1489913169.1.1.utmcsr=baidu|utmccn=(organic)|utmcmd=organic;
    __utmt=1;
    __utma=39553075.1869402811.1489913180.1489913180.1489926463.2;
    __utmb=39553075.20.10.1489926463; __utmc=39553075;
     __utmz=39553075.1489926463.2.2.utmcsr=my.chsi.com.cn|utmccn=(referral)|utmcmd=referral|utmcct=/archive/index.jsp
     * */
    public function curlHttpRequest($url,$cookie = null,$skipssl = false ,$postDate = "") {
        $header=array(
            'Content-Security-Policy'=>'default-src \'self\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn;script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn ssl.google-analytics.com;img-src \'self\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn ssl.google-analytics.com stats.g.doubleclick.net;style-src \'self\' \'unsafe-inline\' \'unsafe-eval\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn fonts.googleapis.com;font-src \'self\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn;child-src \'self\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn;media-src \'self\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn chsi-v.oss-cn-beijing.aliyuncs.com;object-src \'self\' *.chsi.com.cn *.chei.com.cn *.chdi.com.cn',
            'Content-Encoding'=>'gzip',
            'Pragma'=>'no-cache',
            'Server'=>'nginx',
            'Strict-Transport-Security'=>'max-age=31536000',
            'Transfer-Encoding'=>'chunked',
            'X-Content-Type-Options'=>'nosniff',
            'X-Frame-Options'=>'deny',
            'X-Powered-By'=>'Chsi',
            'X-XSS-Protection'=>'mode=block',
        );
        $ch = curl_init($url);
        if(! empty($cookie)) {
            curl_setopt($ch , CURLOPT_COOKIE , $cookie);
        }
        if( $skipssl) {
            curl_setopt($ch , CURLOPT_SSL_VERIFYPEER , false);
            curl_setopt($ch , CURLOPT_SSL_VERIFYHOST , 0);
        }
        if( ! empty($postDate)) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($ch ,CURLOPT_POST ,1);
            curl_setopt($ch ,CURLOPT_POSTFIELDS ,$postDate );
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER,1);
        curl_setopt($ch, CURLOPT_REFERER , "https://my.chsi.com.cn/archive/index.jsp");
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        curl_close($ch);
        return $tmpInfo;
    }

}



