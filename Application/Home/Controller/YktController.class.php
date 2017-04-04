<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/1/23
 * Time: 9:40
 */
namespace Home\Controller;
use Think\Controller;

/*
 * 参数status全局说明
 * 1：成功
 * 2：不在银行卡余额可查时间范围内
 * 3：系统内部判定当前情况属于银行故障
 * 4：银行服务暂不可用，当前情况属于银行故障或内部错误
 * 5：未使用微信端登录
 * -1：内部错误
 * -2：cookie超时
 * -3：缺少参数
 * -4：内部执行某种具体的动作时失败，例如：圈存失败
 * */

class YktController extends Controller {
    //圈存界面+其他功能中心，默认主界面
    public function index() {
        $this->assign($data);
        $this->display();
    }

    //购电界面
    public function electric() {
        $this->assign($data);
        $this->display();
    }

    //购网界面
    public function netPay() {
        $this->assign($data);
        $this->display();
    }

    //网络开户界面
    public function netAccount() {
        $this->assign($data);
        $this->display();
    }

    //修改密码界面
    public function password() {
        $this->assign($data);
        $this->display();
    }

    //挂失界面
    public function campus() {
        $this->assign($data);
        $this->display();
    }

    /*
     * 一卡通全局接口函数
     * 函数返回json数组，下面是对参数的说明：
     * status：参考最上方全局说明
     * info：对当前状态的文字说明
     * url：除去圈存外的其他请求都会返回一个成功界面的URL用于前端界面的跳转
     *
     * 接收参数fnOption（字符串型）说明：
     * save：圈存
     * electric：购电
     * electricRoom：购电房间号
     * netPay：购网
     * netAccount：网络开户
     * password：修改密码
     * campus：挂失
     *
     * */
    public function ajaxFunction (){
        $retArr=D("Init","Logic")->check(3);
        if($retArr['retCode']==-1){
            $this->ajaxReturn(array('status'=>5,'info'=>'请使用微信端重新登陆'),"json");
        }
        else if($retArr['retCode'] == -2) {
            if(!D("Ykt","Logic")->operateBefore()) {
                //执行错误，返回cookie超时让前端处理（重新拉取信息）
                $this->ajaxReturn(array('status'=>-2,'info'=>'cookie超时，请重新登录！'),"json");
            }
        }
        if(!isset($_POST['fnOption']) && !isset($_GET['fnOption']) ) {
            $this->ajaxReturn(array('status'=>-3,'info'=>'缺少参数'),"json");
        }
        $fnOption=(I("post.fnOption",true) === true) ? I("get.fnOption") : I("post.fnOption");
        $function=D("Function","Handle");
        switch ($fnOption) {
            case "save":
                if(!isset($_POST['money'])) {
                    $ajax=array('status'=>-3,'info'=>'缺少参数');
                }
                else {
                    $money=I("post.money");
                    if($function->save($money)) {
                        $ajax=array(
                            'status'=>1,
                            'info'=>"圈存成功",
                            'money'=>$money
                        );
                    }
                    else {
                        $ajax['status']=-4;
                        $ajax['info']="圈存失败";
                    }
                }
                break;
            case "electric":
                if( !isset($_POST["roomId"])  || !isset($_POST["dormId"])  || !isset($_POST["dormName"]) || !isset($_POST["buildName"]) || !isset($_POST["floorName"])
                || !isset($_POST["roomName"])  || !isset($_POST["choosePayType"]) || !isset($_POST["money"]) || !isset($_POST["electricinfo"])) {
                    $ajax=array('status'=>-3,'info'=>'缺少参数');
                }
                else {
                    $roomId=I("post.roomId");
                    $dormId=I("post.dormId");
                    $dormName=I("post.dormName");
                    $buildName=I("post.buildName");
                    $floorName=I("post.floorName");
                    $roomName=I("post.roomName");
                    $accId=$function->electricInit();
                    $choosePayType=I("post.choosePayType");
                    $money=I("post.money");
                    $info=I("post.electricinfo");
                    $post="roomId=$roomId&dormId=$dormId&dormName=$dormName&buildName=$buildName&floorName=$floorName&roomName=$roomName&accId=$accId&payType=1&choosePayType=$choosePayType&money=$money";
                    if($function->buyElectric($post)) {
                        $ajax=array(
                            'status'=>1,
                            'info'=>"购电成功",
                            'money'=>$money,
                            'url'=>U("Home/Success/index?title=购电成功&content=您本次操作成功购电{$money}元！","",false)
                        );
                    }
                    else {
                        $ajax['status']=-4;
                        $ajax['info']="购电失败";
//                        $ajax['info1']=$post;
                    }
                }
                break;
            case "electricRoom":
                if( ! isset($_POST['dormId']) || ! isset($_POST['dormName']) || ! isset($_POST['buildingId']) || ! isset($_POST['floorId'])) {
                    $ajax=array('status'=>-3,'info'=>'缺少参数');
                }
                else {
                    $dormId=I("post.dormId");
                    $dormName=I("post.dormName");
                    $buildingId=I("post.buildingId");
                    $floorId=I("post.floorId");
                    $post="dormId=$dormId&dormName=$dormName&buildingId=$buildingId&floorId=$floorId";
                    $electric=$function->getElectricRoom($post);
                    if(!empty($electric)){
                        $ajax['electric']=$electric;
                        $ajax['info']="success";
                        $ajax['status']=1;
                    }
                    else{
                        $ajax['status']=-4;
                        $ajax['info']="查询房间失败！";
                    }
                }
                break;
            case "netPay":
                if(! isset($_POST['choosePayType']) ||! isset($_POST['money'])) {
                    $ajax=array('status'=>-3,'info'=>'缺少参数');
                }
                else {
                    $choosePayType=I("post.choosePayType");
                    $money=I("post.money");
                    $res=$function->payment($choosePayType,$money);
                    if($res['status']==1){
                        $ajax=array(
                            'status'=>1,
                            'info'=>"购网成功",
                            'money'=>$money,
                            'url'=>U("Home/Success/index?title=购网成功&content=您本次操作成功购网{$money}元！","",false)
                        );
                    }
                    else{
                        $ajax['status']=-4;
                        $ajax['info']="购网失败";
                    }
                }
                break;
            case "netAccount":
                if(! isset($_POST['groupName']) ||! isset($_POST['password']) ||! isset($_POST['money'])){
                    $ajax=array('status'=>-3,'info'=>'缺少参数');
                }
                else{
                    $groupName=I("post.groupName");
                    $password=I("post.password");
                    $money=I("post.money");
                    if($function->account($groupName,$money,$password)){
                        $ajax['status']=1;
                        $ajax['url']=U("Success/index?title=开户成功&content=您刚刚成功开通校园网,您开通的是{$money}元套餐","",false);
                    }
                    else{
                        $ajax['status']=-4;
                        $ajax['info']="开户失败";
                    }
                }
                break;
            case "netBalance":
                $this->ajaxReturn(array('netBalance'=>$function->getNetBalance()),"JSON");
                break;
            case "password":
                if(!isset($_POST['oldPwd']) || !isset($_POST['newPwd']) || !isset($_POST['rePwd'])){
                    $ajax=array('status'=>-3,'info'=>'缺少参数');
                }
                else {
                    $oldPwd=I("post.oldPwd");
                    $newPwd=I("post.newPwd");
                    $rePwd=I("post.rePwd");
                    $content=$function->rePsd($oldPwd,$newPwd,$rePwd);
                    if($content){
                        //调用重加密类库重新刷新hash_key
                        D("Ykt","Logic")->reEncryptForUpHashKey($openid,$newPwd,true);
                        $ajax['status']=1;
                        $ajax['url']=U("Home/Success/index?title=修改成功&content=您刚刚成功修改一卡通密码");
                    }
                    else{
                        $ajax['status']=-4;
                        $ajax['info']="修改密码失败";
                    }
                }
                break;
            case "campus":
                if(!isset($_POST['cardId'])){
                    $ajax=array('status'=>-3,'info'=>'缺少参数');
                }
                else {
                    $cardID=I("post.cardId");
                    $res=$function->lose($cardID);
                    $ajax['status']=1;
                    $ajax['url']=U("Success/index?title=挂失成功&content=您刚刚已成功挂失一卡通");
                }
                break;
            case "campusInit":
                $ajax['campusInfo']=$function->campusInit();
                break;
            default :
                $ajax['status']=-5;
                $ajax['info']="出现故障，请重试";
                $ajax['infotest']=$fnOption;
                break;
        }
        $this->ajaxReturn($ajax,"json");
    }

    public function personInfo() {
        $res=D("Init","Logic")->initData();
        if($res['retCode'] == 1) {
            $ajax=array(
                'status'=>1,
                'studentid'=>$res['studentid'],
                'name'=>$res['name']
            );
        }
        else {
            $ajax['status']=-4;
            $ajax['info']="查询基本信息失败";
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 获取校园卡余额、银行卡余额标准接口函数
     * 返回值为json数组，现对数组中参数进行说明：
     * status：参考最上方全局说明
     *
     * 当前函数应该执行的逻辑判定，设置session相关参数用于record设置：
     * isBank：用于记录反馈于前端的银行卡状态信息
     * bank：银行卡余额，若拉去失败则为失败的相关信息记录
     * campus：校园卡余额，若拉去失败则为失败的相关信息记录
     * */
    public function getMoneyBalance(){
        $retArr=D("Init","Logic")->check(3);
        if($retArr['retCode']==-1){
            $this->ajaxReturn(array('status'=>5,'info'=>'请使用微信端重新登陆'),"json");
        }
        else if($retArr['retCode'] == -2) {
            if(!D("Ykt","Logic")->operateBefore()) {
                //执行错误，返回cookie超时让前端处理（重新拉取信息）
                $this->ajaxReturn(array('status'=>-2,'info'=>'cookie超时，请重新登录！'),"json");
            }
        }
        $result=D("Function","Handle")->getMoneyBalance();
        if(empty($result['campus'])){
            $result['status']=4;
            $result['info']='银行系统暂不可用';
            $this->ajaxReturn($result,"JSON");
        }
        if($result['status']==1){
            session("isBank",1);
            session("bank",$result['bank']);
            session("campus",$result['campus']);
        }
        else if($result['status']==2){
            session("isBank",-1);
            session("bank","不在银行卡余额可查时间范围内");
            session("campus",$result['campus']);
        }
        else if($result['status']==3){
            session("isBank",-1);
            session("bank","系统内部判定当前情况属于银行故障");
            session("campus",$result['campus']);
            $result['info']='银行服务暂不可用';
        }
        else if($result['status']==4){
            session("isBank",-1);
            session("bank","当前情况属于银行故障或内部错误");
            session("campus",$result['campus']);
            $result['info']='银行服务暂不可用';
        }
        $this->ajaxReturn($result,"JSON");
    }

    /*
     * 获取该用户的校园卡余额
     * status：参考最上方全局说明
     * */
    public function getCampusMoney() {
        $retArr=D("Init","Logic")->check(3);
        if($retArr['retCode']==-1){
            $ajax=array('status'=>5,'info'=>'请使用微信端重新登陆');
            $this->ajaxReturn($ajax,'JSON');
        }
        else if($retArr['retCode'] == -2) {
            if(!D("Ykt","Logic")->operateBefore()) {
                //执行错误，返回cookie超时让前端处理（重新拉取信息）
                $ajax=array('status'=>-2,'info'=>'cookie超时，请重新登录！');
                $this->ajaxReturn($ajax,'JSON');
            }
        }
        $campusMoney=D("Function","Handle")->getMoneycampus();
        if(!empty($campusMoney)) {
            $ajax=array(
                'campus'=>$campusMoney,
                'status'=>1,
                'info'=>'successful'
            );
        }
        else {
            $ajax=array(
                'status'=>-1,
                'info'=>'内部错误'
            );
        }
        $this->ajaxReturn($ajax,'JSON');
    }

    /*
     * 用于任意情况下获取用户一卡通余额之后同步到会员卡界面
     * */
    public function moneyTogether() {
        $retArr=D("Init","Logic")->check(1);
        if($retArr['retCode']==-1) {
            $ajax=array('status'=>5,'info'=>'请使用微信端重新登陆');
            $this->ajaxReturn($ajax,'JSON');
        }
        if(!empty($_POST['campusMoney'])) {
            $ajax=D("Ykt","Logic")->moneyTogether(I("post.campusMoney"));
        }
        else {
            $ajax['status']=-5;
            $ajax['info']="出现故障，请重试";
        }
        $this->ajaxReturn($ajax,'JSON');
    }

}


