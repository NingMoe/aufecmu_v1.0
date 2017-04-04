<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/27
 * Time: 22:01
 * 类库核心工作的鉴定权限，
 */
namespace Home\Logic;
class InitLogic {

    private $wxScope;
    private $isa;

    public function __construct() {
        $this->wxScope=D("WxScope","Logic");
        $this->isa=D("Isa");
    }

    /*
     * 接收参数$judgeCode，
     * 1：只判定登录状态
     * 默认：获取登陆状态下的相关信息
     *
     * 用于获取用户的基本信息,返回代用retCode参数的数组，
     * 1：正确并获取到全部信息
     * 2：该用户当前处于退出登录状态
     * -1：未能找到openid，说明非法登录
     * -2：未能在数据库中找到这条数据，可能是新用户
     * */
    public function initData($judgeCode) {
        if(session("?openid")) {
            $openid=session("openid");
            if( $this->isa->isExist(array(
                'openid'=>"$openid",
                'status'=>1
            )) ) {
                if($judgeCode == 1) {
                    return array(
                        'retCode'=>1,
                    );
                }
                else {
                    $res=$this->isa->getInfo($openid,1);
                    return array(
                        'retCode'=>1,
                        'studentid'=>$res['studentid'],
                        'password'=>$res['password'],
                        'yktPassword'=>$this->decryptPassword($res['ykt_password']),
                        'headimgurl'=>$res['headimgurl'],
                        'name'=>$res['name'],
                        'cname'=>$res['cname'],
                    );
                }
            }
            else {
                $res=$this->isa->getInfoByArr(array(
                    'openid'=>"$openid"
                ));
                if(!empty($res)) {
                    return array(
                        'status'=>$res['status'],
                        'retCode'=>2,
                    );
                }
                else {
                    return array(
                        'retCode'=>-2,
                    );
                }
            }
        }
        else {
            return array(
                'retCode'=>-1,
            );
        }
    }

    /*
     * 判定函数：
     * 接收一个int型参数：
     * 1、微信接口逻辑判定
     * 使用微信静默授权，使用服务号openid 机制
     * 此处的判定只需要调用验证accessToken是否有效即可
     *
     * 2、一卡通业务业务判定
     *
     * 3、一卡通业务执行前的判定
     *
     * 4、拉取非静默授权数据
     *
     *
     *
     * 返回值：
     * retCode：用于表示用户的状态码
     *  1：正确
     *  2：未知错误
     * -1：微信鉴权出现错误
     * -2：业务逻辑执行前判定出现错误
     * -3：一卡通业务出现出错
     * -4：非静默授权出现错误
     * -5：未能正确获取授权所需参数
     *
     * yktCode:用于表示一卡通接口状态的状态码，其他状态码详情请参考FunctionHandle中autoEntre函数说明，以下为补充说明：
     * 0：数据库中未能查询到相关的信息
     *
     *
     * */
    public function check($judgeCode) {
        if($judgeCode == 1) {
            //判定微信逻辑接口
            return array(
                'retCode'=>$this->judgeWeixin() ? 1 : -1
            );
        }
        else if ($judgeCode == 2) {
            //一卡通业务业务判定
            if( $this->judgeYkt() ) {
                return array(
                    'retCode'=>1
                );
            }
            else {
                return array(
                    'retCode'=>-3,
                    'yktCode'=>session("?yktCode") ? session("yktCode") : "",
                );
            }
        }
        else if ($judgeCode == 3) {
            //一卡通业务执行前的逻辑判定，执行3时不需要调用其他
            if(!$this->judgeWeixin()) {
                return array(
                    'retCode'=>-1
                );
            }
            else {
                if($this->judgeYkt()) {
                    return array(
                        'retCode'=>1
                    );
                }
                else {
                    return array(
                        'retCode'=>-2
                    );
                }
            }
        }
        else if($judgeCode == 4) {
            //非静默授权调用
            return $this->unSilenceScope();
        }
        else {
            //不应该执行的地方
            return array(
                'retCode'=>2
            );
        }
    }

    //非静默授权，获取非静默授权相关信息
    private function unSilenceScope() {
        if( session("?code") && time() <= session("codeTime")) {
            $code=session("code");
        }
        else {
            $code="";
        }
        $res = $this->wxScope->getUnionid($code);
        if($res['errcode'] == 0 || empty($res['errcode'])) {
            // if($res['errcode'] == 0) {
            //     file_put_contents("1.txt","00000");
            // }
            // else if(empty($res['errcode'])) {
            //     file_put_contents("2.txt","empty");
            // }
            session("codeTime",0);//设置当前的codeTime过期，当前逻辑我们只需要设置改值为0即可
            session("unionid",$res['unionid']);
            return array('retCode'=>1)+$res;
        }
        else {
            session("weberrorcoed",$res['errcoed']);
            session("weberrormsg",$res['errMsg']);
            session("unionid",$res['unionid']);
            return array('retCode'=>-4)+$res;
        }
    }

    //微信逻辑判定
    private function judgeWeixin() {
        $code=session("code");
        $time=time();
        if( !session("?code") || time() > session("codeTime") ) {
            $wxOutTime=session("wxOutTime");
            if($time < $wxOutTime && $time+10 >= $wxOutTime ) {
                return true;//若满足十秒钟内的通过时间且session时间是在正常的时间内的，直接返回true
            }
            //未能获取到微信授权界面的code，验证当前session中的accessToken和openid是否正确
            if($this->wxScope->judgeAccessToken(session("access_token"),session("openid"))) {
                return true;
            }
            else {
                session("access_token",null);
                session("openid",null);
                return false;
            }
        }
        else{
            $res=$this->wxScope->getToken($code);
            if( !empty($res['openid']) && !empty($res['access_token']) ) {
//                setcookie("code","",time()-1); //设置 code 过期
                session("codeTime",0);//设置当前的codeTime过期，当前逻辑我们只需要设置改值为0即可
                session("wxOutTime",$time+10);//十秒钟缓冲时间，十秒内不需要再次验证，默认是正确且成功的
                return true;
            }
            else {
                //code码错误，返回假
                session("weberrorcoed",$res['errcoed']);
                session("weberrormsg",$res['errmsg']);
                return false;
            }
        }
    }

    /*
     * 系统交互层建鉴定用户的状态码
     * */

    /*
     * 一卡通业务环境判定，安全环境约束如下
     * 1、JSESSIONID存在
     * 2、InitTime在规定时间内
     * */
    private function judgeYkt() {
        session("yktCode",null);
        if(session("?JSESSIONID") && time() < session("initTime") ) {
            return true;
        }
        else {
            return false;
            // 未设置相关的JSESSIONID或者对应的权限过期了，自动拉取用户账号密码登录并调用autoEntre自动登录一卡通业务。
        }
    }

    /*
     * 获取当前用户的状态码
     * 返回值注释：
     * -1：未能查询到该用户
     *  1：该用户处于激活状态
     *  2：该用户处于注销状态
     * */
    public function getStatus() {
        $openid=session("openid");
        $res=$this->isa->getInfoByArr(array(
            'openid'=>"$openid"
        ));
        if( !empty($res) ) {
            return $res['status'];
        }
        else{
            return -1;
        }
    }



    //解码函数
    private function decryptPassword($password) {
        return $password;
    }

    /*
     * 订阅号接口，获取用户状态值,
     * 接收参数：openid和mediaid
     * 返回值
     * 返回数组
     * status：表示用户当前状态，只有；两个值
     * 1或者2，其中1代表用户处于登录状态，2代表用户处于费登录状态，要求用户重新登录
     * info：（status==1时）学号(姓名)
     * */
//    public function getInfoForDyh($openid,$mediaid) {
//        $sql="select b.status,c.studentid,c.name from `ykt_interface` as a,`ykt_isa` as b,`ykt_student` as c
//        where a.openid='$openid' and a.mediaid='$mediaid' and a.unionid=b.unionid and b.studentid=c.studentid limit 1";
//        $info=M()->query($sql);
//        if($info[0]['status'] == 1) {
//            return array(
//                'status'=>1,
//                'info'=>$info[0]['studentid']."(".$info[0]['name'].")"
//            );
//        }
//        else {
//            return array(
//                'status'=>2
//            );
//        }
//    }

    /*
     * 一卡通核心功能处，这里价将用于网页静默授权后获取用户用户基本信息
     * 返回数组：
     * errCode：仅仅用于判断逻辑是否正确
     *  1：正确情况
     * -1：错误，code码错误，未能正确换取相关信息
     * -2：错误，accessToken验证失败
     * -3：错误，数据库sql查询内部错误，未能查询到指定的结果
     * 以下参数将在 errCode > 0 时显示
     * studentid：该用户学号
     * name：姓名
     * status：用户状态码
     * headimgurl：用户头像（每一次用户bind或触发安全验证时更新）
     * */
//    public function  getInfoForService() {
//        $code=I("get.code");
//        if( empty($code) || $code == session("oldCode")) {
//            //未能获取到微信授权界面的code，验证当前session中的accessToken和openid是否正确
//            $openid=session("openid");
//            if( !$this->wxScope->judgeAccessToken(session("access_token"),session("openid")) ) {
//                return array(
//                    'errCode'=>-2,
//                );
//            }
//        }
//        else {
//            $res=$this->wxScope->getToken($code);
//            if( !empty($res['openid']) && !empty($res['access_token']) ) {
//                //记录该用户的accessToken和openid到session中，返回真.
//                $openid=$res["openid"];
////                session("openid",$res['openid']);
////                session("access_token",$res['access_token']);
//            }
//            else {
//                //code码错误，返回假
//                return array(
//                    'errCode'=>-1
//                );
//            }
//        }
//        $sql="select a.status,a.headimgurl,b.studentid,b.name from ykt_isa as a,ykt_student as b
//        where a.openid='$openid' and a.studentid=b.studentid";
//        $res=M()->query($sql);
//        if( !empty($res[0]['studentid']) ){
//            session("oldCode",$code);
//            return array(
//                "errCode"=>1,
//                "studentid"=>$res[0]['studentid'],
//                "name"=>$res[0]['name'],
//                "status"=>$res[0]['status'],
//                "headimgurl"=>$res[0]['headimgurl'],
//            );
//        }
//        else{
//            return array(
//                'errCode'=>-3
//            );
//        }
//
//    }


}

