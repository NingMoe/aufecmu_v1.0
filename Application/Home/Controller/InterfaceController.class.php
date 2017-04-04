<?php
/**
 * Created by PhpStorm.
 * User: ancai4399
 * Date: 2016/12/17
 * Time: 17:25
 */
namespace Home\Controller;
use Think\Controller;

class InterfaceController extends Controller{
    /*
     SA团队服务号自定义URL导航接口部分 user_gifting_card
    */
    public function index() {
        $wxResponse = D("WxResponse","Logic");
        $Dyh=D("Dyh","Logic");
        $info=$wxResponse->chatBackForInterface();
        if( $info->MsgType == "event" && $info->Event == "user_get_card") {
            //用户领卡行为，这里需要将用户的会员卡号和相关信息记录下来
            $Dyh->userGetCard($info->FromUserName,$info->UserCardCode,$info->CardId);
             echo "";die;
            $wxResponse->test($info->FromUserName,$info->ToUserName,"用户领取会员卡，OldUserCardCode：".$info->OldUserCardCode."   UserCardCode：".$info->UserCardCode."  对应的cardId为：".$info->CardId);
        }
        else if($info->MsgType == "event" && $info->Event == "user_gifting_card") {
            //转赠事件推送
            //转增事件获得，应删除相应人员的会员卡记录
            $Dyh->userGiftCard($info->FromUserName,$info->UserCardCode);
            // file_put_contents("test.txt", "转增会员卡code：".$info->UserCardCode."  对应的cardId为：".$info->CardId);
            // $wxResponse->test($info->FromUserName,$info->ToUserName,"转增会员卡code：".$info->UserCardCode."  对应的cardId为：".$info->CardId);
        }
        else if($info->MsgType == "event" && $info->Event == "user_view_card"){
            $resStr=D("Ykt","Logic")->autoUp(array('openid'=>$info->FromUserName));//更新该用户的校园卡余额
//            file_put_contents("1.txt","InterfaController--index--info:".$info->FromUserName."----".$resStr.PHP_EOL,FILE_APPEND);
             echo "";die;
//            $wxResponse->test($info->FromUserName,$info->ToUserName,"进入会员卡，卡号为：".$info->UserCardCode."  对应的cardId为：".$info->CardId."otherInfo:".($resStr));
        }
        else if($info->MsgType == "event" && $info->Event == "subscribe") {
            $Dyh->userSubscribe($info->FromUserName);
            $wxResponse->test($info->FromUserName,$info->ToUserName,"我是SA团队的弟弟SB，感谢你的关注~");
        }
        else if($info->MsgType == "image") {
            $url=U("Home/Person/index","",false,true);
            $wxResponse->test($info->FromUserName,$info->ToUserName,"<a href='$url'>安财校友卡欢迎你的加入!</a>");
        }
        else{
            $wxResponse->test($info->FromUserName,$info->ToUserName,"我是SA团队的弟弟SB~");
            die;
            if( $info->Content == "test" ) {
                $test=$Dyh->userSubscribe($info->FromUserName);
                $wxResponse->test($info->FromUserName,$info->ToUserName,"我是SA团队的弟弟SB，感谢你的关注~".$test);
            }
            else {
                $wxResponse->test($info->FromUserName,$info->ToUserName,"我是SA团队的弟弟SB~");
            }
        }
    }

    /*
     * 教务实名认证接口，需完成全局跳转任务，交由页面js完成
     * */
    public function authJw() {
        bindInit();
        // dump(D("Init","Logic")->check(4));
        $data['getYzm']=U("Home/Index/getYzm","",false);
        $data['updateYzm']=U("Home/Index/updateYzm","",false);
        $data['deleteYzm']=U("Home/Index/deleteYzm","",false);
        $data['judge']=U("Home/Interface/authJwInterface","",false);
        $data['share']=U("Home/Person/share","",false);
        $data['defaultUrl']=U("Home/Person/index","",false);
        $data['pattern']=C("pattern"); //获取验证码识别默认规则
        $this->assign($data);
        $this->display();
    }

    public function authJwInterface() {
        $studentid=I("post.studentid");
        $password=I("post.password");
        $yzm=I("post.yzm");
        $pattern=I("post.pattern");
        $curl=D("info1","Handle");
        $stucode=$curl->entranceJw($studentid,$password,$yzm);
        if($stucode==1) {
            $res=D("Init","Logic")->check(4);
            if( $res['retCode']==1 ) {
                if(D("User","Service")->setActive(session("openid"),$studentid,$password)) {
                    $ajax['status']=1;
                    $ajax['m_access_token']="successful";
                    $ajax['headimgurl']=$res['headimgurl'];
                    $ajax['cname']=$res['nickname'];
                }
                else {
                    $ajax['status']=-7;
                    $ajax['info']="内部错误，建议查看网络后重试！";
                }
                // $ajax['status']=1;
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
                    $res=D("Init","Logic")->check(4);
                    if( $res['retCode']==1 ) {
                        if(D("User","Service")->setActive(session("openid"),$studentid,$password,$res['headimgurl'],$res['nickname'])) {
                            $ajax['status']=1;
                            $ajax['m_access_token']="successful";
                            $ajax['headimgurl']=$res['headimgurl'];
                            $ajax['cname']=$res['nickname'];
                        }
                        else {
                            $ajax['status']=-7;
                            $ajax['info']="内部错误，建议查看网络后重试！";
                        }
                        // $ajax['status']=1;
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

    public function interfaceForAutoUpdateCampusMoney() {

    }

}