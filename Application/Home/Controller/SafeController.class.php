<?php
namespace Home\Controller;
use Think\Controller;
/*
 * 本类用于一卡通的安全鉴定界面
 * safe类的作用范围：
 * 1、用于激活会员卡的老用户登陆一卡通账户
 * 2、用于用于老用户更改密码后的安全验证
 * 3、提供避开会员卡直接使用一卡通的直接登陆界面
 * 4、用于特殊情况下的验证码识别接口无法使用的问题
 * */
class SafeController extends Controller {
    /*
     * 一卡通安全路由函数，用于对safe控制器的全局路由控制
     * 1、处理静默授权，获取该用户的唯一标识openid；
     * 2、使用openid鉴定用户当前status：
     * 3、status为1：拉取用户基本信息（账号、密码、头像以及姓名、学号），自动登录一卡通系统，登录成功，存储权限标记，跳转进入具体的业务界面；status不为1：进入步骤4；
     * 4、跳转进入safe安全验证界面
     * */
    public function safeRoute() {
//        die("test1");
        bindInit();
        $init=D("Init","Logic");
        $retArr=$init->check(1);
//        file_put_contents("3.txt",json_encode($retArr));
        if($retArr['retCode']==1) {
            $stuArr=D("Ykt","Logic")->operateBefore();
            if($stuArr['sysCode']==1 && $stuArr['yktCode']==1) {
                //进入业务逻辑页面
                redirect(getUrlByOption(I("get.state")));
            }
            else if($stuArr['sysCode']==-1) {
                session("option",I("get.state"));
                redirect(U("Home/Safe/login","",false));
            }
        }
        redirect(U("Home/Safe/safe","",false));
    }

    public function login() {
        $retArr=D("Init","Logic")->check(1);
        if($retArr['retCode']==1) {
            $data['judge']=U("Safe/judge","",true);
            $data['getYzm']=U("Safe/getYzm","",true);
            $data['updateYzm']=U("Safe/updateYzm","",true);
            $data['deleteYzm']=U("Index/deleteYzm","",true);
            $data['share']=U("Person/share","",true);
            $this->assign($data);
            $this->display();
        }
        else {
            redirect(U("Home/Error/index?title=鉴权错误&content=请在微信端重新打开","",false));
        }
    }


    /*
     * 本函数为safe安全验证界面控制函数，默认该界面所有的用户都为老用户
     * check函数将使用基本的微信鉴定模式
     * */
    public function safe() {
        $init=D("Init","Logic");
        $retArr=$init->check(1);
        if($retArr['retCode']==1) {
            $resInfo = $init->initData();
            if($resInfo['retCode'] == 1) {
                $data['judge']=U("Safe/judge","",true);
                $data['getYzm']=U("Safe/getYzm","",true);
                $data['updateYzm']=U("Safe/updateYzm","",true);
                $data['deleteYzm']=U("Index/deleteYzm","",true);
                $data['share']=U("Person/share","",true);
                $data['name']=$resInfo["name"];
                $data['studentid']=$resInfo["studentid"];
                $data['headimgurl']=$resInfo["headimgurl"];
                $this->assign($data);
                $this->display();
            }
            else {
                redirect(U("Home/Safe/login","",false));
            }
        }
        else {
            redirect(U("Home/Error/index?title=鉴权错误&content=请在微信端重新打开","",false));
        }
    }

    /*
     * 获得验证码，机械识别
     * */
    public function getYzm() {
        $handle=D("Function","Handle");
        $info=$handle->getYzmInfo();
        if($info['status']==1){
            $ajax['yzm']=$info['result'];
            $ajax['pattern']=1;
        }
        else{
            //$ajax['yzm']=$handle->loginInit();    //由前台异步控制
            $ajax['pattern']=2;
        }
//        $ajax['pattern']=2;
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 获取验证码图片，不使用机械师被
     * */
    public function  updateYzm() {
        $ajax['yzm']=D("Function","Handle")->loginInit();
        $ajax['pattern']=2;
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 这里是安全验证界面检测函数
     * 接受传递参数值
     * studentid：学号
     * password：密码
     * yzm：根据该参数是否存在判断是否使用自动验证码识别
     * option：该参数为url路由导向
     *
     * ajax返回的状态码：
     *  1：正确
     *  2：账号密码错误
     *  3：验证码错误
     * -1：证件号不存在
     * -2：信息未填写完全
     * -3：请使用微信端登录
     * -4：请关闭微信后重启该页面
     * -5：注册登录失败
     * -6：缺少参数，注册失败，提示页面超时，要求用户关闭页面重新启动
     * */
    public function judge() {
        $retArr=D("Init","Logic")->check(1);
        if($retArr['retCode']==-1) {
            $this->ajaxReturn(array('info'=>'请使用微信端登陆'),"json");
        }
        $curl=D("Function","Handle");
        $studentid=I("studentid",false);
        $password=I("password",false);
        $yzm=I("yzm",false);
        $option=session("?option") ? session("option") : I("option",false);
        if($yzm==false || $yzm<0) {
            $retCode=$curl->autoEntre($studentid,$password);
        }
        else {
            $retCode=$curl->personCurl($studentid,$password,$yzm);
        }
        if($retCode==1) {
            //需要执行相关得的逻辑代码
            if(D("User","Service")->setYktLogin(session("openid"),$studentid,$password)) {
                session("option",null);
                $ajax['url']=getUrlByOption($option);
                $ajax['status']=1;
            }
            else {
                $ajax['status']=-5;
                $ajax['info']="内部错误，建议查看网络后重试！";
            }
        }
        else if($retCode==2) {
            //账号密码错误
            $ajax['status']=2;
            $ajax['info']="账号密码错误！";
        }
        else {
            //验证码错误，要求前端页面显示验证码
            $ajax['status']=3;
            $ajax['info']="验证码错误！";
        }
        $this->ajaxReturn($ajax,"JSON");
    }

    /*
     * 用户自动登录函数
     * */
//    public function userAutoLogin() {
//        $resInfo = D("Init","Login")->initData();
//        if( $resInfo['retCode'] == 1 && ! empty($resInfo['yktPassword']) ) {
//            if(D("Ykt","Logic")->operateBefore($resInfo['studentid'],$resInfo['yktPassword'])) {
//                //进入业务逻辑页面
//                redirect(getUrlByOption(I("get.state")));
//            }
//        }
//        else {
//
//        }
//    }

}


?>