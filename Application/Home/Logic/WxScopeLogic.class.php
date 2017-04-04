<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/1
 * Time: 0:51
 * 判定用户是否在线
 */
namespace Home\Logic;
class WxScopeLogic{
    private $appId;
    private $appSecret;
//    private $logpath;

    public function __construct() {
        //服务号appid  和   appsecret
        $this->appId="wx5aba40d737e98b5d";
        $this->appSecret="2c00d0bc161bf2fab39d71d82294bdd0";
    }

    //记录用户的openid和accessToken用于识别验证
    // public function _getToken($code) {
    //     $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$code&grant_type=authorization_code";
    //     $res = json_decode(file_get_contents($url),true);
    //     session("openid",$res['openid']);
    //     session("access_token",$res['access_token']);
    //     /* 我们默认使用session保存code
    //     if( isset($_COOKIE['code'])) {
    //         setcookie("code","",time()-1);//注销code
    //     }*/
    //     return $res;
    // }

    public function getToken($code){
        if(! empty($code)){
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$code&grant_type=authorization_code";
            $result=json_decode(file_get_contents($url),true);
            session("re_token",$result['refresh_token']);
            session("openid",$result['openid']);
            session("access_token",$result['access_token']);
        }
        else{
            if(session("?re_token")){
                $refresh_token=session("re_token");
                $url="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$this->appId&grant_type=refresh_token&refresh_token=$refresh_token";
                $result=json_decode(file_get_contents($url),true);
                session("openid",$result['openid']);
                session("access_token",$result['access_token']);
            }
            else{
                $result=array(
                    'errcode'=>-5,
                    'errmsg'=>'refresToken换取access_token失败'
                );
            }
        }
        return $result;
    }

    //判定accessToken是否有效
    public function judgeAccessToken($access_token,$openid) {
        $url="https://api.weixin.qq.com/sns/auth?access_token=$access_token&openid=$openid";
        $result=json_decode(file_get_contents($url),true);
        // file_put_contents("1.txt", json_encode($result));
        // return $result;
        if($result['errcode']==0) {
            return true;
        }
        else{
            return false;
        }
    }

    //获取unionid
    public function getUnionid($code){
        $res=$this->getToken($code);
        if(! empty($res['access_token'])) {
            $access_token=$res['access_token'];
            $openid=$res['openid'];
            $url="https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
            $result=json_decode(file_get_contents($url),true);
            if(! empty($result['unionid'])) {
                return $result;//正确
            }
            else{
                //若失败，销毁session
                session("access_token",null);
                session("openid",null);
                return array(
                    'errcode'=>'-1',
                    'errMsg'=>'unionid 拉取失败,错误代码：'.$result['errcode'].'错误信息：'.$result['errmsg'],
                    'viewMsg'=>"亲!请返回对应公众号重新拉取信息!");
            }
        }
        else {
            return array(
                'errcode'=>'-2',
                'errMsg'=>'access_token 拉取失败,错误代码：'.$res['errcode'].'错误信息：'.$res['errmsg'],
                'viewMsg'=>"亲!请返回对应公众号重新拉取信息!");
        }
    }








    //------------------------
    //获取开发者接入微信公众号的accessTone
//    private function getAccessToken() {
//        return file_get_contents("http://121.42.57.23/wxJssdk/JssdkInterface.php?type=access_token_web");
//    }
//
//    //更新开发者接入微信公众号的accessTone
//    private function updateAccessToken() {
//        return file_get_contents("http://121.42.57.23/wxJssdk/JssdkInterface.php?type=update_access_token");
//    }
//
//    //通过accessToken获取用户基本信息
//    public function getBasicInfo($openid,$media){
//        if($media=="gh_2f97120599f5"){
//            $access_token=$this->getAccessToken();
//            $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
//            $res = file_get_contents($url);
//            $result=json_decode($res, true);
//            if( empty($result['errcode'])){
//                return $result;
//            }
//            else{
//                return $this->basicInfoFromUpdate($openid);
//            }
//        }
//        else{
//            return null;
//        }
//    }
//
//    //通过updateAccessToken获取用户基本信息
//    private function basicInfoFromUpdate($openid){
//        $access_token=$this->updateAccessToken();
//        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
//        $res = file_get_contents($url);
//        return json_decode($res, true);
//    }
//
//    //判断是否为已知公众号用户
//    public function getMediaId($openid){
//        $access_token=$this->getAccessToken();
//        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
//        $res = file_get_contents($url);
//        $result=json_decode($res, true);
//        if( empty($result['errcode'])){
//            return "gh_2f97120599f5";
//        }
//        else{
//            return "";
//        }
//    }
}
