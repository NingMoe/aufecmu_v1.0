<?php
namespace Home\Controller;
use Think\Controller;
//功能列表页面
class PersonController extends Controller{
    /*
     * 主业务界面，校友圈
     * 信息流中心：
     *
     * 判断当前是否经过微信授权
     *
     * */
    public function index() {
        $this->assign($data);
        $this->display();
    }

    public function detail() {
        if(isset($_GET['noteid'])) {
        	// dump(urldecode(I("get.noteid")));
            $noteid=urldecode(I("get.noteid"));
            $id=authcode($noteid,"DECODE", C("OWN_KEY"));
            // dump($id);die;
            if(!empty($id)) {
                $note=D("Note","Service");
                $data['commentInfo']=$note->getOneNote($id);
                $data['imgInfo']=empty($data['commentInfo']['imgInfo']) ? null : json_decode($data['commentInfo']['imgInfo'],true);
                $this->assign($data);
                $this->display();
            }
            else {
                redirect(U("Home/Error/index?title=错误&des=缺失信息，请返回！$noteid  $id"));
            }
        }
        else {
            redirect(U("Home/Error/index?title=错误&des=缺失信息，请返回！22"));
        }
    }

    public function editor() {
        // $data['sendNote']=U("Home/Note/sendNote","",false);
        $this->assign($data);
        $this->display();
    }

    /*
     * 全局路由函数，当用户触发登录事件时调用
     * 当非静默授权回调到这里的时候，检测当前用户的状态
     * 已登录老用户直接回调进入具体的业务逻辑页面（这里将该用户的所有有需要的信息全部保存下来）
     * 未登录用户或者新用户要求用户进行教务登陆
     * 最后跳转进入相关的逻辑页面或者执行相关业务逻辑
     * */
    public function globalRoute() {
        bindInit();
        $init=D("Init","Logic");
        $res=$init->check(4);
        if($res['retCode'] == "1") {
            $userInfo=$init->initData();
            if($userInfo['retCode'] == "1") {
                //成功，进入中转界面，先调用js设置当前登录态为已登录，然后抓取进入该界面时存储的业务逻辑数据并处理，剩下的工作全部由js完成（保存信息由这里完成）
                D("User","Service")->addToIsaForScope($res['openid'],$res['unionid'],$res['headimgurl'],$res['nickname']);
                $data['m_access_token']="successful";
                $data['headimgurl']=$userInfo['headimgurl'];
                $data['cname']=$userInfo['cname'];
                $data['errPageUrl']=U("Error/index?title=未知错误&des=请关闭页面并重新登陆");
                $this->assign($data);
                $this->display();
                // die();
            }
            else {
                redirect(U("Home/Interface/authJw","",false,true));
            }
        }
        else {
            redirect(U("Home/Error/index?title=错误&des=系统内部错误，请联系管理员"));
        }
        // dump($res);
        // dump($userInfo);
        
    }


    public function Logout() {
        //退出登录,设置当前用户为注销状态
        if(D("User","Service")->setLogout()){
            $ajax['info']="ok";
            $ajax['status']=1;
        }
        else{
            $ajax['status']=-1;
        }
        $this->ajaxReturn($ajax,"JSON");
    }

    /*
     * 全局微信分享ajax初始化接口
     * */
    public function share() {
        $url=urldecode(I("post.url"));
//        echo json_encode(D("Jssdk","Service")->getSignPackage($url));
    }

}


?>