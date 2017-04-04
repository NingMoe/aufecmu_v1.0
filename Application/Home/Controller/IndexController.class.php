<?php
/*
 * author:zhengwei
 *
 * */


namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {

    /*
     * 会员卡激活接口，实现服务号openid与用户的绑定
     * 利用自带session实现自我跳转
     * 目的：解决微信会员卡自带参数无法正确带入微信静默授权的问题
     *
     * 验证码识别机制默认使用机械识别
     * 将code放到cookie中，设置code的超时时间为4.5mins（标准code时效为5mins）
     * */
    public function index() {
        bindInit();
        if(!isset($_GET['code'])) {
            // die("teest1");
            redirect( sprintf(C("baseUrl"),urlencode("http://wx.aufe.vip/aufecmu/index.php") ,""));
        }
        else {
            $data['getYzm']=U("Home/Index/getYzm","",false);
            $data['updateYzm']=U("Home/Index/updateYzm","",false);
            $data['deleteYzm']=U("Home/Index/deleteYzm","",false);
            $data['judge']=U("Home/Index/judge","",false);
            $data['share']=U("Home/Person/share","",false);
            $data['pattern']=C("pattern"); //获取验证码识别默认规则
            $this->assign($data);
            $this->display();
        }
    }


    /*
     * A：教务验证；
     * B：检测用户状态，设置用户为登录
     * （1）、用户为未开卡用户，激活当前用户的会员卡；
     * （2）、用户已经开卡，设置用户当前状态为登陆状态；
     * C：push模板消息；
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
        $studentid=I("post.studentid");
        $password=I("post.password");
        $yzm=I("post.yzm");
        $pattern=I("post.pattern");
        $curl=D("info1","Handle");
        $stucode=$curl->entranceJw($studentid,$password,$yzm);
        if($stucode==1) {
            //因为judge之前页面为bind页面，绑定页面不验证用户状态，所以这里需要验证当前用户是否处于正确的状态
            $res=D("Init","Logic")->check(1);
            if( $res['retCode']==1 ) {
                if( isset($_COOKIE['encrypt_code']) ) {
                    if(D("User","Service")->setActive(session("openid"),$studentid,$password)) {
                        if(D("WxVipCard","Logic")->setUserActiveInJwc($_COOKIE['encrypt_code'],$studentid)) {
                            $ajax['status']=1;
                            $redirectUri=urlencode(U("Home/Person/index","",false));
                            $ajax['url']=U("Success/index?title=激活成功&content=恭喜你已经成功激活了该会员卡&redirectUri=$redirectUri");
                        }
                        else {
                            $ajax['status']=1;
                            $ajax['url']=U("Error/index?title=激活失败&des=请联系管理员味增，错误状态码为：".$res['errcode']);
                        }
                    }
                    else {
                        $ajax['status']=-7;
                        $ajax['info']="内部错误，建议查看网络后重试！";
                    }
                }
                else {
                    $ajax['status']=-6;
                    $ajax['info']="页面超时，建议关闭微信后重启该页面！";
                }
            }
            else {
                $ajax['status']=-3;
                $ajax['info']="请使用微信端登陆！".session("weberrorcoed");
            }
        }
        else if($stucode==2) {
            //密码错误
            $ajax['status']=2;
            $ajax['info']="密码错误，请重新输入！";
        }
        else if($stucode==3) {
            if($pattern==1){
                //这里我们使用验证码自动抓取更新
                $stuCode=$curl->autoEntre($studentid,$password);
                if($stuCode==1){
                    //因为judge之前页面为bind页面，绑定页面不验证用户状态，所以这里需要验证当前用户是否处于正确的状态
                    $res=D("Init","Logic")->check(1);
                    if( $res['retCode']==1 ) {
                        if( isset($_COOKIE['encrypt_code']) ) {
                            if(D("User","Service")->setActive(session("openid"),$studentid,$password)) {
                                if(D("WxVipCard","Logic")->setUserActiveInJwc($_COOKIE['encrypt_code'],$studentid)) {
                                    $ajax['status']=1;
                                    $ajax['url']=U("Success/index?title=激活成功&content=恭喜你已经成功激活了该会员卡");
                                }
                                else {
                                    $ajax['status']=1;
                                    $ajax['url']=U("Error/index?title=激活失败&des=请联系管理员味增，错误状态码为：".$res['errcode']);
                                }
                            }
                            else {
                                $ajax['status']=-7;
                                $ajax['info']="内部错误，建议查看网络后重试！";
                            }
                        }
                        else {
                            $ajax['status']=-6;
                            $ajax['info']="页面超时，建议关闭微信后重启该页面！";
                        }
                    }
                    else {
                        $ajax['status']=-3;
                        $ajax['info']="请使用微信端登陆！".session("weberrorcoed");
                    }
                }
                else{
                    $ajax['status']=$stuCode;
                }
            }
            else{
                $ajax['status']=3;
                $ajax['info']="验证码错误，请重新输入！";
            }
        }
        else if($stucode==-1){
            //学号不存在
            $ajax['status']=-1;
            $ajax['info']="该证件号不存在！喂，妖妖灵吗？对面派来了间谍，你们管吗？";
        }
        else{
            //非法入侵
            $ajax['status']=-2;
            $ajax['info']="信息输入错误，请重新输入！";
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 全局url路由，用于未知情况的全局导航，优化系统逻辑接口
     * 判定当前用户状态，无法主动调用，仅作为系统导航路由
     * */
    public function globalRoute() {
        $init=D("Init","Logic");
        $stuCode=$init->getStatus();
        if($stuCode == 1) {
            //登录状态，重定向到person界面
            redirect(U("Home/Person/functionSelect","",false),0,"都卡在这儿了，顺便给我们投个简历吧！");
        }
        else if($stuCode == 2 || $stuCode == -1) {
            //注销状态和未登录状态，重组url并跳转到bind，要求整体系统进行非静默授权，绑定系统
            $serviceUrl=U("Home/Index/bindUser","",false);
            redirect(sprintf(C("userinfoUrl"),urlencode($serviceUrl)),0,"都卡在这儿了，顺便给我们投个简历吧！");
        }
        else {
            //注销状态和未登录状态，判断当前状态是否可以重定向到绑定界面  ????????
            //重定向到错误界面
            die("错误界面！");
        }
    }

    /*
     * 获得验证码，机械识别
     * */
    public function getYzm() {
        $handle=D("Info1","Handle");
        $info=$handle->getYzmInfo();
        if($info['status']==1){
            $ajax['yzm']=$info['result'];
            $ajax['pattern']=1;
        }
        else{
            $ajax['yzm']=$handle->loginInit();
            $ajax['pattern']=2;
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 获取验证码图片，不使用机械师被
     * */
    public function  updateYzm() {
        $ajax['yzm']=D("Info1","Handle")->loginInit();
        $ajax['pattern']=2;
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 删除废弃的验证码
     * */
    public function deleteYzm() {
        $allcount=0;
        $deletecount=0;
        $dir=C("DEFAULT_YZM");
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $allcount++;
                $fullpath=$dir."/".$file;
                // 删除时长大于5s的所有文件，不能删除目录，避免速度过快删除自己
                if(time()-filemtime($fullpath)>5) {
                    $deletecount++;
                    unlink($fullpath);
                }
            }
        }
        closedir($dh);
//        echo "总数量：".$allcount;
//        echo "<br>删除验证码数量为：".$deletecount;
    }

    public function test() {
        $openid="o16hwwfcPRKulZ2YkuMia-PJUT9w";
        dump(D("WxVipCard","Logic")->kefuMsg($openid));
//        dump(json_decode(D("WxVipCard","Logic")->alterVipCard(),true));
    }


    /*
     * 逻辑优化，基于微信开放平台接入订阅号接口
     * http://mmbiz.qpic.cn/mmbiz_jpg/dHr5jWLaicW8ASbTJFKUCI8ibsyXhypQ26KtvsOFYXwIH8Mn2yicmUFFZ25o0P3uicS2pt56WwLibibgxY5lPpCQLTDw/0
     * */
    public function test1() {
//       die("封住了，别瞎几把搞");
        $WxVip= D("WxVipCard","Logic");
        $res=$WxVip->setUserActiveSy(431934826489,"20142917");
        dump($res);
        die(111);
        dump(json_decode($res,true));
        die;
        //公众号接入导航接口
        $wx=D("WxResponse","Logic");
        $info=$wx->chatBack();
        $res=D("Init","Logic")->getInfoForDyh($info['openid'],$info['mediaid']);
        if($res['status'] == 1) {
            //这里返回对应用户的学号姓名，多条图文并列
            $wx->backPerson($info['openid'],$info['mediaid'],$res['info']);
        }
        else {
            //这里返回请先绑定一卡通
//            $wx->back($info['openid'],$info['mediaid'],"请先绑定一卡通系统");
            $wx->backLogin($info['openid'],$info['mediaid']);
        }
    }

    /*
     * 签名验证算法校验函数
     * 调试存储信息
     * */
    public function testCheck() {
        die("封住了，别瞎几把搞");
//        $extArr=array(
//            'type'=>"upPsd",
//        );
//        $postData=array(
//            'openid'=>"o16hwwb9HjRJ9uxDHWqd4FoHdeFI",
//            'password'=>"662911"
//        );
//        $judge=D("JudgeSign","Logic");
//        $judge->reInit("ykt","justDoIt","http://cmuapi.woai662.com/cmuapi/yktPsd.php");
//
        dump(D("Ykt","Logic")->reEncryptForUpHashKey("o16hwwb9HjRJ9uxDHWqd4FoHdeFI",662911,true));
//        echo (($judge->getContent($extArr,$postData)));
    }

    /*
     * 调试接口获得信息
     * */
    public function testGetInfo() {
        die("封住了，别瞎几把搞");
//        $hashKey='$2y$10$cbXs2qAV1GYpDYbRALgqg.MJhVf0m7j\/J\/gebL9v5R3c8TQSiNmn.';
////        $hashKey='$2y$10$N1Ljkasnkd8uuOWHmEHUboOi\/88WkOyrJE3LqH.L8oCzO';
//        $extArr=array(
//            'type'=>"getPsd",
//        );
//        $postData=array(
//            'openid'=>"o16hwwb9HjRJ9uxDHWqd4FoHdeFI",
//            'hashKey'=>$hashKey
//        );
//        $judge=D("JudgeSign","Logic");
//        $judge->reInit("ykt","justDoIt","http://cmuapi.woai662.com/cmuapi/yktPsd.php");
//        dump(json_decode($judge->getContent($extArr,$postData)));
        dump(D("Ykt","Logic")->decryptForPsd("o16hwwb9HjRJ9uxDHWqd4FoHdeFI"));
    }

    /*
     * 会员卡自动更新函数
     * 发送post数据包到指定url并
     * */
    public function autoYktInfo() {
        die("封住了，别瞎几把搞");
        $openid="o16hwwb9HjRJ9uxDHWqd4FoHdeFI";//初始化信息为openid
        dump(D("Ykt","Logic")->autoUp(array("openid"=>$openid)));
    }



    //------------------------分隔线-------------------test
    public function yzm() {
        die("封住了，别瞎几把搞");
        $url="http://www.baidu.com";
        dump(sprintf(C("userinfoUrl"),$url));die;
        $curl=D("Curl","Logic");
        $data['yzmUrl']=$curl->loginInit();
        $data['url']=U("Home/Index/checkYzm");
//        $data['test']=$curl->checkYzm($yzm);
        $this->assign($data);
        $this->display();
    }

    public function checkYzm() {
        die("封住了，别瞎几把搞");
        $yzm=I("post.yzm");
        $curl=D("Curl","Logic");
        $data['check']=$curl->checkYzm($yzm);
        $data['JSESSIONID']=session("JSESSIONID");
        $data['yzm']=$yzm;
        $this->ajaxReturn($data,"JSON");
    }

    //用于清空登录成功后多余的变量
    public function destroyVar() {
        // die("封住了，别瞎几把搞");
        setcookie("dyhOpenid","",time()-1);
        setcookie("mediaid","",time()-1);
        setcookie("uid","" ,time()-1);
        setcookie("JSESSIONID","" ,time()-1);
        setcookie("initTime","" ,time()-1);
        session("JSESSIONID",null);
        session("initTime",null);
        echo "好了，销毁了！";
    }


}