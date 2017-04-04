<?php
/**
 * Created by PhpStorm.
 * User: zhengwei
 * Date: 3/3/17
 * Time: 11:26 AM
 */

namespace Home\Logic;
class YktLogic {

    private $judge;
    private $isa;
    public function __construct() {
        $this->judge=D("JudgeSign","Logic");
        $this->isa=D("Isa");
    }

    /*
     * 该函数为一卡通自动登录函数，用于自动进入一卡通使用
     * */
    public function operateBefore () {
//        file_put_contents("2.txt", "there");
        //该种情况可以将用户连续两次打开的时间大大减少
        if(isset($_COOKIE['JSESSIONID']) && isset($_COOKIE['initTime'])) {
            session("JSESSIONID",$_COOKIE['JSESSIONID']);
            session('initTime',$_COOKIE['initTime']);
            return array('sysCode'=>1,'yktCode'=>1);
        }
        $resInfo = D("Init","Logic")->initData();
        if($resInfo['retCode'] == "1") {
            $yktPassword=$this->decryptForPsd("",$resInfo['hash_key']);
            if(!empty($yktPassword)) {
                $codeStatus=D("Function","Handle")->autoEntre( $resInfo['studentid'], $yktPassword );
                if($codeStatus == 1) {
                    //记录用户的cookie并设置超时时间为 5mins
                    setcookie("JSESSIONID",session("JSESSIONID"),300);
                    setcookie('initTime',time()+300,300);
                    return array('sysCode'=>1,'yktCode'=>1);
                }
                else {
                    return array('sysCode'=>1,'yktCode'=>$codeStatus);
                }
            }
            else {
                //这里未能正确换取接口部分的密码，错误码2
                return array('sysCode'=>1,'yktCode'=>2);
            }
        }
        else {
            return array('sysCode'=>-1);
        }
    }

    /*
     * 该函数要求当前用户必须登录，加密函数
     * */
    public function encryptForHashKey($openid,$password) {
        $extArr=array(
            'type'=>"initPsd",
        );
        $postData=array(
            'openid'=>"$openid",
            'password'=>$password
        );
        $res=json_decode($this->judge->getContent($extArr,$postData,2) , true);
        return $res['hashKey'];
    }

    /*
     * 该函数要求当前用户必须登录，解密函数
     * */
    public function decryptForPsd($openid=null,$hashKey=null) {
        if(empty($hashKey)) {
            $res=$this->isa->getInfoByArr(array("openid"=> empty($openid) ? session("openid") : $openid));
            $hashKey=$res['hash_key'];
        }
        $extArr=array(
            'type'=>"getPsd",
        );
        $postData=array(
            'openid'=>"o16hwwb9HjRJ9uxDHWqd4FoHdeFI",
            'hashKey'=>$hashKey
        );
        $res=json_decode($this->judge->getContent($extArr,$postData,2) , true);
        return $res['decodePsd'];
    }

    /*
     * 该函数要求当前用户必须登录，重新加密更新一卡通账号密码
     * 这里允许自动更新密码，若选择自动更新密码将返回boolean值（可用于用户修改密码）
     * */
    public function reEncryptForUpHashKey($openid,$password,$isAuto=false) {
        $extArr=array(
            'type'=>"upPsd",
        );
        $postData=array(
            'openid'=>"$openid",
            'password'=>$password
        );
        $res=json_decode($this->judge->getContent($extArr,$postData,2) , true);
        if($isAuto === false) {
            return $res['hashKey'];
        }
        else {
            return D("Isa")->update($openid,array('hash_key'=>$res['hashKey']));
        }
    }

    /*
     * 一卡通自动更新
     * */
    public function autoUp(array $inputArr = array()) {
        if(!empty($inputArr['studentid']) && !empty($inputArr['hash_key']) && !empty($inputArr['code']) && !empty($inputArr['openid']) ) {
            $extArr=array();
            $postData=$inputArr;
        }
        else {
            $openid=empty($inputArr['openid']) ? session("openid") : $inputArr['openid'];
//            file_put_contents("1.txt","info:".$openid.PHP_EOL,FILE_APPEND);
            $res=D("Isa")->getInfoByArr(array("openid"=>"$openid"));
            if( !empty($res['studentid']) && !empty($res['hash_key']) && !empty($res['code']) && !empty($res['openid']) ) {
                $extArr=array();
                $postData=array(
                    'openid'=>$res['openid'],
                    'studentid'=>$res['studentid'],
                    'hashKey'=>$res['hash_key'],
                    'code'=>$res['code']
                );
//                $res=json_decode($this->judge->getContent($extArr,$postData,3) , true);
            }
            else {
                return array(
                    'retCode'=>404,
                    'info'=>"not send any data,please check inner code;",
                );
            }
        }
//        file_put_contents("1.txt","yktLogic--autoUp--postData:".json_encode($postData).PHP_EOL,FILE_APPEND);
        $res=$this->judge->getContent($extArr,$postData,3);
        return $res;
    }

    /*
     * 用于任意获取到一卡通余额情况下的自动更新函数
     * */
    public function moneyTogether($campusMoney) {
        $campusMoney=trim($campusMoney);
        if(is_numeric($campusMoney)) {
            $res=D("Isa")->getInfoByArr(array("openid"=>session("openid")));
            if( !empty($res['code']) ) {
                D("WxVip","Logic")->setUserActive($res['code'],$res['studentid'],$campusMoney);
                return array(
                    "status"=>1,
                    "info"=>"send data successful!"
                );
            }
            else {
                return array(
                    "status"=>-3,
                    "info"=>"缺少参数"
                );
            }
        }
        else {
            return array(
                "status"=>-4,
                "info"=>"not number!"
            );
        }
    }


}