<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/21
 * Time: 18:42
 */
namespace Home\Service;

class UserService{

    private $interface;
    private $isa;
    private $student;
    private $mediaid;

    public function __construct() {
        $this->interface=D("Yinterface");
        $this->isa=D("Isa");
        $this->student=D("Student");
        $this->mediaid="gh_2c27c47368ce";
    }

    /*
     * 用户通过教务系统激活会员卡后设置相关状态信息,本函数将完成以下小项：
     * 1：更新isa表
     * 2：更新student表
     * */
    public function setActive($openid,$studentid,$password,$headimgurl=null,$cname=null) {
        $res=D("Vipuser")->getInfoByArr(array('openid'=>$openid));
        if( !empty($res) ) {
            $cardid=$res['cardid'];
            $code=$res['code'];
            $unionid=$res['unionid'];
        }
        else {
            $cardid=null;
            $code=null;
            $unionid=session("?unionid") ? session("unionid") : null;
        }
        $infoHandle=D("Info1","Handle");
        $personInfo=$infoHandle->infoHandle($infoHandle->getBasicInfo());
        if($studentid != $personInfo['studentid'] || empty($personInfo['sfz'])) {
            //抓取函数执行失败，再次尝试
            $personInfo=$infoHandle->infoHandle($infoHandle->getBasicInfo());
        }
        return $this->addToIsa($openid,$unionid,$studentid,$password,null,$cardid,$code,1,$headimgurl,$cname) && $this->addToStudent($studentid,$personInfo['name'],$personInfo['class'],$personInfo['sfz'],$personInfo['sex'],$personInfo['major'],$personInfo['college'],$personInfo['other']);
    }

    /*
     * 一卡通用户设置为login
     * 设置用户登录状态需要用户传入当前网页的
     * unionid：微信开放平台对应订阅号的unionid
     * openid：服务号openid
     *
     * 返回值 true or false
     * */
    public function setYktLogin($openid,$studentid,$yktPassword) {
        $cardid=null;
        $code=null;
        $unionid=null;
        $yktPassword=$this->encryptPassword($yktPassword);//加密 password
        if( isset($_COOKIE['dyhOpenid']) && isset($_COOKIE['mediaid']) ) {
            //订阅号接口进入
            $dyhOpenid=$_COOKIE['dyhOpenid'];
            $mediaid=$_COOKIE['mediaid'];
            //注销对应公众号openid和mediaid
            if( !$this->addToInterface($unionid,$dyhOpenid,$mediaid) ) {
                return -1;
            }
        }
        else {
            //普通网页授权接口进入，检测是否为会员卡入口,
            $res=D("Vipuser")->getInfoByArr(array('openid'=>$openid));
            if( !empty($res) ) {
                $cardid=$res['cardid'];
                $code=$res['code'];
                $unionid=$res['unionid'];
            }
        }
        $resInfo = D("Function","Handle")->getBasicInfo();
//        file_put_contents("1.txt",json_encode($resInfo));
        return $this->addToIsa($openid,$unionid,$studentid,null,$yktPassword,$cardid,$code) && $this->addToStudent($studentid,$resInfo['name'],$resInfo['class']);
    }

    /*
     * 添加interface
     * */
    public function addToInterface($unionid,$dyhOpenid,$mediaid) {
        if( ! $this->interface->isExist(array(
            'openid'=>$dyhOpenid,
            'mediaid'=>$mediaid,
        ))) {
            $data=array(
                'openid'=>$dyhOpenid,
                'mediaid'=>$mediaid,
                'unionid'=>$unionid
            );
            return $this->interface->addNew($data);
        }
        else {
            return true;
        }
    }

    /*
     * 添加isa
     * */
    public function addToIsa($openid,$unionid,$studentid,$password=null,$ykt_password=null,$cardId=null,$code=null,$status=1,$headimgurl=null,$cname=null) {
        $wxUserInfo=(!empty($headimgurl) && !empty($cname)) ? array('headimgurl'=>$headimgurl,'nickname'=>$cname) : json_decode(D("WxVipCard","Logic")->getWxUserInfo($openid),true);
        if( ! $this->isa->isExist($openid) ) {
            //添加函数
            $data=array(
                'openid'=>$openid,
                'studentid'=>$studentid,
                'headimgurl'=>empty($wxUserInfo['errmsg']) ? $wxUserInfo['headimgurl'] : null,
                'cname'=>empty($wxUserInfo['errmsg']) ? $wxUserInfo['nickname'] : null,
                'addtime'=>time(),
                'uptime'=>time(),
                'status'=>$status
            );
            if( !empty($password) ) {
                $data['password']=$password;
            }
            if( !empty($unionid) ) {
                $data['unionid']=$unionid;
            }
            if( !empty($ykt_password) ) {
                $data['hash_key']= D("Ykt","Logic")->reEncryptForUpHashKey($openid,$ykt_password);
            }
            if( !empty($cardId) && !empty($code) ) {
                $data['cardid']=$cardId;
                $data['code']=$code;
            }
            return $this->isa->addNew($data);
        }
        else {
            //更新函数
            $upData=array(
                'headimgurl'=>empty($wxUserInfo['errmsg']) ? $wxUserInfo['headimgurl'] : null,
                'cname'=>empty($wxUserInfo['errmsg']) ? $wxUserInfo['nickname'] : null,
                'uptime'=>time(),
                'status'=>$status
            );
            if( !empty($password) ) {
                $upData['password']=$password;
            }
            if( !empty($ykt_password) ) {
                $upData['hash_key']=D("Ykt","Logic")->reEncryptForUpHashKey($openid,$ykt_password);
            }
            if( !empty($cardId) && !empty($code) ) {
                $upData['cardid']=$cardId;
                $upData['code']=$code;
            }
            return $this->isa->update($openid,$upData);
        }
    }

    /*
     * 特殊的添加信息到isa表，仅用于微信网页非静默授权使用
     * */
    public function addToIsaForScope($openid,$unionid,$headimgurl,$cname) {
        if( ! $this->isa->isExist($openid) ) {
            return $this->isa->addNew(array(
                'openid'=>$openid,
                'unionid'=>$unionid,
                'headimgurl'=>$headimgurl,
                'cname'=>$cname
            ));
        }
        else {
            return $this->isa->update($openid,array(
                'unionid'=>$unionid,
                'headimgurl'=>$headimgurl,
                'cname'=>$cname
            ));
        }
    }

    /*
     * 添加student
     * 重写
     * */
    public function addToStudent($studentid,$name,$class,$sfz=null,$sex=null,$major=null,$college=null,$other=null) {
        if( !empty($sfz) ) {
            //正确执行部分
            $data=array(
                'studentid'=>$studentid,
                'name'=>$name,
                'sfz'=>$sfz,
                'sex'=>$sex,
                'class'=>$class,
                'major'=>$major,
                'college'=>$college,
                'other'=>$other,
            );
        }
        else if( !empty($name) ) {
            //一卡通兼容部分，同样为正确执行部分
            $data=array(
                'studentid'=>$studentid,
                'name'=>$name,
                'class'=>$class
            );
        }
        else {
            //未能成功抓取到数据
            $data=array(
                'studentid'=>$studentid,
            );
        }
        if( ! $this->student->isExist($studentid)) {
            return $this->student->addNew($data);
        }
        else {
            if(count($data) != 1) {
                unset($data['studentid']);
            }
            return $this->student->update($studentid,$data);
        }
    }

    public function setLogout() {
        if( D("Init","Logic")->judge(1) ) {
            if($this->isa->update(session("openid"),array(
                'status'=>2
            ))) {
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    private function encryptPassword($password) {
        return $password;
    }

    private function decryptPassword($password) {
        return $password;
    }

}




?>