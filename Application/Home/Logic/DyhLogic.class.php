<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/12/30
 * Time: 10:09
 * doing things：本类库用于处理用于与公众号发生的交互行为
 * //40003  openid错误，用户未关注
//40001  accesstoken错误
 */

namespace Home\Logic;
class DyhLogic{
    private $accessToken ;
    private $vipuser;

    public function __construct() {
        $this->accessToken=file_get_contents("http://ancai4399.com/jssdk_server/JssdkInterface.php?type=access_token_web");
        $this->vipuser=D("Vipuser");
    }

    public function upAccessToken() {
        $this->accessToken=file_get_contents("http://ancai4399.com/jssdk_server/JssdkInterface.php?type=update_access_token");
    }

    //用户关注事件
    public function userSubscribe($openid) {
        $userInfo=json_decode($this->getUserInfo($openid),true);
        //首先检测accessToken是否正确
        if( $userInfo['errcode'] == "40001") {
            $this->upAccessToken();
            $userInfo=json_decode($this->getUserInfo($openid),true);
        }
        return $this->updateInfo($openid,array(
            "unionid"=>$userInfo['unionid'],
            "subscribe"=>1,
        ));
    }

    //用户取关事件

    //用户领取会员卡事件
    public function userGetCard($openid ,$code ,$cardid) {
        $userInfo=json_decode($this->getUserInfo($openid),true);
        //首先检测accessToken是否正确
        if( $userInfo['errcode'] == "40001") {
            $this->upAccessToken();
            $userInfo=json_decode($this->getUserInfo($openid),true);
        }
        $unionid=empty($userInfo['unionid']) ? null : $userInfo['unionid'];
        return $this->addInfo($openid,$code,$cardid,$unionid);
    }

    //用户转增会员卡事件，删除用户数据库对应的记录信息
    public function userGiftCard($openid, $code) {
        return $this->vipuser->deleteInfo(array(
            'openid'=>"$openid",
            'code'=>"$code",
        ));
    }


    //获取用户基本信息
    public function getUserInfo($openid) {
        $requestUrl="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$this->accessToken&openid=$openid&lang=zh_CN";
        // return $this->curlHttpRequest($requestUrl,null,false);
        $res = $this->curlHttpRequest($requestUrl,null,false);
        //accessToken过期，过期
        if($res['errcode'] == "40001") {
            $this->upAccessToken();
            $requestUrl="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->accessToken}&openid=$openid&lang=zh_CN";
            $res = $this->curlHttpRequest($requestUrl,null,false);
        }
        return $res;
    }

    //向数据库添加会员卡信息
    public function addInfo($openid, $code, $cardid, $unionid) {
        $dataArr=array(
            'openid'=>"$openid",
            'code'=>"$code",
            'cardid'=>"$cardid",
        );
        if( !empty( $unionid) ) {
            $dataArr=$dataArr+array(
                    'unionid'=>"$unionid"
                );
        }
        if(! $this->vipuser->isExist($dataArr) ) {
            return $this->vipuser->addNew($dataArr);
        }
        else {
            unset($dataArr['openid']);
            return $this->vipuser->update($openid,$dataArr);
        }
    }

    //更新数据库会员卡信息
    public function updateInfo ($openid,$upArr) {
        if( $this->vipuser->isExist($openid) ) {
            return $this->vipuser->update($openid,$upArr);
        }
        else {
            //不存在在该用户，将用户添加为新的记录
            return $this->vipuser->addNew(array(
                    'openid'=>"$openid"
                )+$upArr);
        }
    }

    //https数据处理函数
    public function curlHttpRequest($url,$cookie = null,$skipssl = false ,$postDate = "") {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
        if(! empty($cookie)) {
            curl_setopt($ch , CURLOPT_COOKIE , $cookie);
        }
        if( $skipssl) {
            curl_setopt($ch , CURLOPT_SSL_VERIFYPEER , false);
            curl_setopt($ch , CURLOPT_SSL_VERIFYHOST , 0);
        }
        if( ! empty($postDate)) {
            curl_setopt($ch ,CURLOPT_POST ,1);
            curl_setopt($ch ,CURLOPT_POSTFIELDS ,$postDate );
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        curl_close($ch);
        return $tmpInfo;
    }

}

