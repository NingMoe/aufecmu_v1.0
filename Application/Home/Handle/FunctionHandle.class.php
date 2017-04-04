<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/1/23
 * Time: 20:44
 * 这里完成的功能：
 * 1、一卡通逻辑接口判定
 * 2、一卡通业务处理相关接口
 * 3、其他一卡通相关的功能函数
 */
namespace Home\Handle;

class FunctionHandle {
    private $url;
    private $yzmPath;
    private $cache;

    public function __construct(){
        $this->url=C("DEFAULT_URL_YKT");
        $this->curl=D("Curl","Logic");
        $this->yzmPath=C("DEFAULT_YZM");
        $this->cache=C("ABSOLUTE_CACHE");
    }

    //一卡通判定接口
    public function judge($studentid,$password,$yzm,$pattern) {
        if( $pattern==1 ) {
            $retCode=$this->autoEntre($studentid,$password);
        }
        else{
            $retCode=$this->personCurl($studentid,$password,$yzm);
        }
        return array(
            'yktCode'=>$retCode,
            'pattern'=>1
        );
    }

    public function loginInit() {
        srand((double)microtime()*100000);
        $random=rand(0,1000000);
        $yzmFileName=$this->yzmPath."$random.jpg";
        $cookieFile=$this->cache."$random.txt";
//        $ch = curl_init ();
//        curl_setopt ( $ch, CURLOPT_URL, $this->url."checkCode.action" );
//        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 1 );
//        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
//        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookieFile);
//        curl_setopt ( $ch, CURLOPT_USERAGENT, '"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)' );
//        curl_setopt ( $ch, CURLOPT_TIMEOUT, 5 );
//        $img=curl_exec ( $ch );
//        curl_close ( $ch );
        $setArr=array(
            CURLOPT_URL=>$this->url."checkCode.action",
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_COOKIEJAR=>$cookieFile,
            CURLOPT_USERAGENT=>'"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
            CURLOPT_TIMEOUT=>5
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $img=$this->curl->getCurlResult();
        $this->handleCookie($cookieFile);
        $this->imgWrite($yzmFileName,$img);
        return $yzmFileName;
    }

    /*
     * 返回状态码说明：
     * 1：成功！
     * 2：账号密码错误
     * 3：验证码错误
     * */
    public function personCurl($Student_Id,$Password,$yzm) {
        if(!$this->checkYzm($yzm)) {
            return 3;//验证码错误
        }
       // $ch=curl_init();
       // curl_setopt($ch,CURLOPT_URL,$this->url."login.action");
       // curl_setopt ($ch,CURLOPT_REFERER,$this->url);
       // curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
       // curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
       // curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
       // curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
       // curl_setopt($ch,CURLOPT_POST,1);
       // $post['loginMode'] = "express";
       // $post['username'] = $Student_Id;
       // $post['password'] = $Password;
       // $post['checkCode'] = $yzm;
       // curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
       // $content=curl_exec($ch);
       // curl_close ( $ch );
        
        $post['loginMode'] = "express";
        $post['username'] = $Student_Id;
        $post['password'] = $Password;
        $post['checkCode'] = $yzm;
        $setArr=array(
            CURLOPT_URL=>$this->url."login.action",
            CURLOPT_REFERER=>$this->url,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_COOKIE=>"JSESSIONID=".session("JSESSIONID"),
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$post
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content=$this->curl->getCurlResult();
        if(strpos($content,"login_blank")!=false){//检测是否登录成功
            return 1;
        }
        else{
            //账号密码错误
            return 2;
        }
    }

    public function checkYzm($yzm) {
//        $ch=curl_init();
//        curl_setopt($ch,CURLOPT_URL,$this->url."validateYzm.action");
//        curl_setopt ($ch,CURLOPT_REFERER,$this->url);
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
//        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($ch,CURLOPT_POST,1);
//        $post['checkCode'] = $yzm;
//        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
//        $content=curl_exec($ch);
//        curl_close ( $ch );
        $post['checkCode'] = $yzm;
        $setArr=array(
            CURLOPT_URL=>$this->url."validateYzm.action",
            CURLOPT_REFERER=>$this->url,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_COOKIE=>"JSESSIONID=".session("JSESSIONID"),
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$post
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content=$this->curl->getCurlResult();
        return !($content=="false");
    }

    /*
     * 自动拉取验证码两次
     * */
    public function autoEntre($zjh,$mm) {
        //获取验证码，尝试两次
        for($i=0;$i<2;++$i) {
            $res=$this->getYzmInfo();
//            file_put_contents("1.txt", json_encode($res).PHP_EOL,FILE_APPEND);
            if($res['status']==1){
                $retCode = $this->personCurl($zjh,$mm,$res['result']);
                if ($retCode == 3) {
                    //状态码为3，验证码错误，若次数未达到上限则跳过，否则返回状态码3
                    if( $i == 1) {
                        return 3;
                    }
                    else{
                        continue;
                    }
                }
                else{
                    return $retCode;
                }
            }
            else {
                if($i==1){
                    return -1;//两次验证码都未能识别成功,返回-2，要求前端页面返回图片验证码
                }
            }
        }
    }

    //获取验证码识别信息
    public function getYzmInfo() {
        srand((double)microtime()*1000000);
        for($i=0;$i<2;++$i){
            $extArr=array(
                'yzmUrl'=>$this->url."checkCode.action",
                'type'=>'11',
                'yzmType'=>'.jpg',
                'sysType'=>'2',
            );
            $res=json_decode(D("JudgeSign","Logic")->getContent($extArr),true);
//            file_put_contents("1.txt", json_encode($res).PHP_EOL,FILE_APPEND);
//            file_put_contents("1.txt", (!empty($res['result']) && strlen($res['result'])==4) ? "zhende" : "jiade".PHP_EOL,FILE_APPEND);
            if(!empty($res['result']) && strlen($res['result'])==4 ){
//                die("successful");
                //这里的result存在，并且验证码长度为4，说明验证码识别是成功的
                session("JSESSIONID",$res['cookie']['cookie1']);
                session("initTime",time()+300);
                //将结果放在cookie中，用户多次登录就不需要再次拉取
                setcookie("JSESSIONID",$res['cookie']['cookie1'],300);
                setcookie('initTime',time()+300,300);
                //返回验证码识别结果
                return array(
                    'status'=>1,
                    'result'=>$res['result']
                );
            }
        }
        return array(
            'status'=>-1,
        );
    }

    //获取一卡通用户基本信息
    public function getBasicInfo(){
//        $ch=curl_init();
//        curl_setopt($ch,CURLOPT_URL,$this->url."queryUserInfo.action");
//        curl_setopt($ch,CURLOPT_REFERER,$this->url);
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
//        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        $content=curl_exec($ch);
//        curl_close ( $ch );
        $setArr=array(
            CURLOPT_URL=>$this->url."queryUserInfo.action",
            CURLOPT_REFERER=>$this->url,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_COOKIE=>"JSESSIONID=".session("JSESSIONID"),
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content=$this->curl->getCurlResult();
        $content=explode("<label>",$content,4);
        $it=explode("</label>",$content[2],2);
        $data['Name']=trim($it[0]);
        $content=explode("<label>",$content[3],3);
        $it=explode("</label>",$content[1],2);
        $data['sex']=trim($it[0]);
        $content=explode('<label style="width:350px">',$content[2]);
        $it=explode("</label>",$content[1],3);
        $data['BankId']=trim($it[0]);
        $it=explode("<label>",$it[2],2);
        $it=explode("</label>",$it[1],2);
        $data['Student_Id']=trim($it[0]);
        $content=explode("<label>",$content[2]);
        $it=explode("</label>",$content[0],2);
        $data['CardId']=trim($it[0]);
        $it=explode("</label>",$content[2],2);
        $data['Class']=trim($it[0]);
        $content=explode('<label id="old" name="old" style="width:350px;display: block;">',$content[3]);
        $it=explode('&nbsp;&nbsp;',$content[1],2);
        $data['PhoneNumber']=trim($it[0]);
        if(!empty($data['Student_Id'])){
            return array(
                'studentid'=>$data['Student_Id'],
                'name'=>$data['Name'],
                'class'=>$data['Class']
            );
        }
        else{
            return null;
        }
    }

    public function save($money){
        $setArr=array(
            CURLOPT_URL=>$this->url."bankTransferAmount.action?amount=".$money,
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_HEADER=>0,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content=$this->curl->getCurlResult();
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $this->url."bankTransferAmount.action?amount=".$money);
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
//        $content = curl_exec($curl);
//        curl_close ( $curl );
        file_put_contents("2.txt",$content);
        if(strpos($content,'value = "转账成功"')!=false){
            return true;
        }
        else{
            return false;
        }
    }

    public function electricInit(){
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $this->url."utilityUnBindUserPowerPayInit.action");
//        curl_setopt($curl,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
//        $content=curl_exec($curl);
//        curl_close ( $curl );
        $setArr=array(
            CURLOPT_URL=>$this->url."utilityUnBindUserPowerPayInit.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content=$this->curl->getCurlResult();
        $content=explode('<input type = "hidden" name="accId" value ="',$content);
        $content=explode('" />',$content[1],2);
        return $content[0];
    }

    public function getElectricRoom($post) {
        $setArr=array(
            CURLOPT_URL=>$this->url."utilitBindXiaoQuData.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_HEADER=>0,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$post
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        return $this->curl->getCurlResult();
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $this->url."utilitBindXiaoQuData.action");
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
//        curl_setopt($curl,CURLOPT_POST,1);
//        //$post="dormId=1&dormName=东校南区&buildingId=55&floorId=84";
//        curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
//        return curl_exec($curl);
    }

    public function buyElectric($post){
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $this->url."utilityUnBindUserPowerPay.action");
//        curl_setopt($curl,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
//        curl_setopt($curl,CURLOPT_POST,1);
//        //$post="roomId=1054&dormId=1&accId=2000063190&payType=1&choosePayType=2&money=1";
//        curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
//        $content=curl_exec($curl);
//        curl_close ( $curl );
        $setArr=array(
            CURLOPT_URL=>$this->url."utilityUnBindUserPowerPay.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$post
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();

        if(strpos($content,'<font color="red">')!=false){
            return false;
        }
        else{
            return true;
        }
    }

    public function payment($choosePayType,$money){
        //https://ykt.aufe.edu.cn/payDrcomFee.action
        /*
         *  校园卡
            choosePayType:1
            money:5

            银行卡
            choosePayType:2
            money:5

            payDrcomFee.action

            测试记录：
            校园卡充值可使用<1元的值
            测试数据为
            0.5元，成功！
            1元，成功！
            5元，成功！
            银行卡充值不可使用分数
            测试数据为
            0.5元，失败！
            1.5元，失败！
            1元，成功！
            *
            *
            */
        //$cookie=session("JSESSIONID");
//        $ch=curl_init();
//        curl_setopt($ch,CURLOPT_URL,$this->url."payDrcomFee.action");
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
//        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($ch,CURLOPT_POST,1);
//        $post="choosePayType=$choosePayType&money=$money";
//        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
//        $content=curl_exec($ch);
//        //缴费成功！
//        curl_close ( $ch );
        $post="choosePayType=$choosePayType&money=$money";
        $setArr=array(
            CURLOPT_URL=>$this->url."payDrcomFee.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$post
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();
        $result=array();
        if(strpos($content,"缴费成功！")!=false){
            $result['status']=1;	//状态码
            $result['money']=$money;   //充值金额
            $result['choosePayType']=$choosePayType;    //充值方式
        }
        else{
            $result['status']=0;
        }
        $result['content']=$content;
        return $result;
    }

    /*
     * 获得网络热点余额
     * */
    public function getNetBalance(){
//        $ch=curl_init();
//        curl_setopt($ch,CURLOPT_URL,$this->url."drcomPayInit.action");
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);//跳过ssl证书验证
//        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        $content=curl_exec($ch);
//        curl_close ( $ch );
        $setArr=array(
            CURLOPT_URL=>$this->url."drcomPayInit.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();
        //<div class = "clear">
        $content=explode('<div class = "clear">',$content);//
        $content=explode('<label>当前网络计费余额：</label><label>',$content[3]);
        $content=explode('元',$content[1]);
        if($content[0]==""){
            return "未开通";
        }
        else{
            return $content[0]."元";
        }
    }

    public function account($groupName,$samOpenMoney,$password){
        //return true;
        /*
         * groupName:
         * 7: 5元100小时
         * 8: 10元200小时
         *
         * password:
         *
         * */
        //开户函数
//        $ch=curl_init();
//        curl_setopt($ch,CURLOPT_URL,$this->url."drcomOpenAccount.action");
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
//        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($ch,CURLOPT_POST,1);
//        $post="groupName=$groupName&samOpenMoney=$samOpenMoney&password=$password";
//        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
//        $content=curl_exec($ch);
//        curl_close ( $ch );
        $post="groupName=$groupName&samOpenMoney=$samOpenMoney&password=$password";
        $setArr=array(
            CURLOPT_URL=>$this->url."drcomOpenAccount.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$post
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();
        if(strpos($content,"热点网络计费 操作成功")!=false){
            return true;
        }
        else{
            return false;
        }
    }

    public function rePsd($oldPwd,$newPwd,$rePwd){
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $this->url."changePassword.action?oldPwd=$oldPwd&newPwd=$newPwd&rePwd=$rePwd");
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
//        $content = curl_exec($curl);
//        curl_close ( $curl );
        $setArr=array(
            CURLOPT_URL=>$this->url."changePassword.action?oldPwd=$oldPwd&newPwd=$newPwd&rePwd=$rePwd",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_HEADER=>0,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();
        if(strpos($content,'value = "密码修改成功"')!=false){
            return true;
        }
        else{
            return false;
        }
    }

    public function campusInit(){
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $this->url."cardLoseInit.action");
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
//        $content = curl_exec($curl);
//        curl_close ( $curl );
        $setArr=array(
            CURLOPT_URL=>$this->url."cardLoseInit.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_HEADER=>0,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();
        //<td>
        $content=explode("<td>",$content);
        $cardId=explode("</td>",$content[1]);
        $cardstatus=explode("</td>",$content[2]);
        $cardinfo=explode("</td>",$content[3]);
        $data=array(
            'cardId'=>trim($cardId[0]),
            'cardstatus'=>trim($cardstatus[0]),
            'cardinfo'=>trim($cardinfo[0])
        );
        return $data;
    }

    public function lose($cardID){
        $setArr=array(
            CURLOPT_URL=>$this->url."cardLose.action?cardId=".$cardID,
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_HEADER=>0,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        return  $this->curl->getCurlResult();


//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $this->url."cardLose.action?cardId=".$cardID);
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl, CURLOPT_COOKIE, "JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        return curl_exec($curl);
    }

    /*
     * 获取银行卡已经校园卡余额接口,同时返回当前使用状态
     * 返回格式为数组，下面是参数数值相关说明：
     * status：代表当前抓取状态
     * 1：成功
     * 2：不在银行服务时间段内
     * 3：银行功能不正常，不能抓出数据
     * 4：未能正确抓取，理论上应该是内部错误，应该记录日志并返回给开发人员
     *
     * isBank：代表银行当前是否可用
     * 1：可用
     * 2：不可用
     *
     * campus：校园卡余额
     *
     * bank：银行卡余额
     *
     * info：反馈信息
     * */
    public function getMoneyBalance(){
        $stu=$this->getAllMoney();
        if($stu['status']==1){
            $stu['isBank']=1;
            return $stu;
        }
        else if($stu['status']==-1){
            //这里说明没有抓取到银行卡余额,我们将判断是否为不允许圈存的阶段
            $d1="22:00:00";
            $d2="04:00:00";
            //若为真则表示当前时间在可以查询到银行卡余额的时间,此时表示银行卡丢失;
            $judge=date('H:i:s')>$d2&&date('H:i')<$d1?true:false;
            if(!$judge){
                //这个时间段就是没办法看到银行卡余额,无法圈存,无解
                $stu=array(
                    'campus'=>$this->getMoneycampus(),
                    'status'=>2,
                    'info'=>'不在银行服务时间段内'
                );
            }
            else{
                //银行功能出现故障，页面上给出相关的反馈即可
                $stu=array(
                    'campus'=>$this->getMoneycampus(),
                    'status'=>3
                );
            }
            $stu['isBank']=2;
            return $stu;
        }
        else{
            //说明无法抓取到有意义的信息，直接返回，这里应该记录到日志信息中并发送消息给开发人员
            return array(
                'campus'=>"",
                'status'=>4,
                'isBank'=>2,
                'info'=>'服务器繁忙，请稍后再试！'
            );
        }
    }

    /*
     * 记账函数
     * type：记录缴费类型
     * 这是一个字符串型变量，暂时有怎么几个选项：圈存、购电、购网等
     * money：记录缴费金额
     *
     * 写入数据库中isBank参数说明如下：（与系统内部记录有区别）
     * 1：银行卡完全可用
     * 2：银行卡完全不可用
     * 3：初始化可用，记录查询时不可用
     * 4，初始化不可用，记录查询可用，这里可能遇到了程序错误
     *
     * sxyk：执行数据操作前的校园卡余额
     * exyk：执行数据操作后的校园卡余额
     * syhk：执行数据操作前的银行卡余额
     * eyhk：执行数据操作后的银行卡余额
     *
     * isok：系统判定的是否成功
     * 1：成功
     * -1：失败
     *
     *
     * 返回值为int型
     * 1：成功
     * -1：操作失败，属于系统正常执行过程
     * -2：未能成功存储进入数据库，内部错误，这时候理论上应该记录状态信息并将信息记录进入log函数
     *
     * */
    public function record($type,$money){
        $recordArr=array();
        $res=$this->getMoneyBalance();
        $recordArr['type']=$type;
        $recordArr['money']=$money;
        $recordArr['sxyk']=session("campus");
        $recordArr['exyk']=$res["campus"];
        if(session("isBank")==1){
            //第一次获取银行卡余额成功
            if(session("isBank")==$res['isBank']) {
                $recordArr['syhk']=session("bank");
                $recordArr['eyhk']=$res["bank"];
                $recordArr['isBank']=1;
                $recordArr['isok']=( $money == number_format($recordArr['syhk']+$recordArr['sxyk']-$recordArr['eyhk']-$recordArr['exyk']) ) ? 1 : -1;
            }
            else{
                /*
                 * 第二次获取银行卡失败，eyhk写入信息与session中的bank相同
                 * 这里可能的情况如下所示
                 * */
                $recordArr['syhk']=session("bank");
                if($res['status']==2){
                    $recordArr['eyhk']="不在银行卡余额可查时间范围内";
                }
                else if($res['status']==3){
                    $recordArr['eyhk']="银行系统故障";
                }
                $recordArr['isBank']=3;
                $recordArr['isok']=-1;
            }
        }
        else{
            //第一次获取银行卡失败
            $recordArr['syhk']=session("bank");
            if(session("isBank")==$res['isBank']){
                //第二次获取银行卡失败，eyhk写入信息与session中的bank相同（对应晚上十点之后到第二天早上不能使用银行功能的时间）
                $recordArr['eyhk']=session("bank");
                $recordArr['isBank']=2;
                $recordArr['isok']=($money== number_format($recordArr['sxyk']-$recordArr['exyk'])) ? 1 : -1;
            }
            else{
                //第二次获取银行卡成功，写入银行卡余额，可能遇到了错误
                $recordArr['eyhk']=$res["bank"];
                $recordArr['isBank']=4;
                $recordArr['isok']=-1;
            }
        }
        $studentid=session("studentid");
        $insertArr=array(
            'studentid'=>$studentid,
            'date'=>date("Y-m-d G:i:s"),
            'payinfo'=>json_encode($recordArr)
        );
        if(!D("Record")->addNew($insertArr)){
            return -2;
        }
        if($recordArr['isok']==1){
            return 1;
        }
        else{
            return -1;//操作失败
        }
    }

    /*
     * 本函数在页面拥有正确cookie的情况下能够抓取到银行卡余额和校园卡余额
     * 返回值为数组，对其中的参数进行说明：
     * status：
     * 1：成功
     * 2：银行卡抓取成功，校园卡抓取失败，一般情况下不可能
     * -1：查询银行卡余额失败，可能为不在可查时间段内，也可能为银行系统出现故障
     *
     * campus：校园卡余额
     * bank：银行卡余额
     * */
    private function getAllMoney(){
//        $ch=curl_init();
//        curl_setopt($ch,CURLOPT_URL,$this->url."queryBankCardBalance.action");
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
//        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        $content=curl_exec($ch);
//        curl_close ( $ch );
        $setArr=array(
            CURLOPT_URL=>$this->url."queryBankCardBalance.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();

//        return $content;
        if(strpos($content,"出错了！")==false){
            $content=explode("&nbsp;",$content);
            $campus=explode("<label>校园卡余额</label><label>",$content[1]);
            $campus=explode("</body>",$campus[1]);//校园卡余额
            $bank=explode("<label>银行卡余额</label><label>",$content[0]);
            $bank=explode("</body>",$bank[1]);//银行卡余额
            $balance=array(
                'campus'=>$campus[0],
                'bank'=>$bank[0],
                'status'=>1
            );
            if(!empty($campus[0])){
                return $balance;
            }
            else{
                return array(
                    'status'=>2
                );
            }
        }
        else{
            //这里说明无法查询到银行卡余额
            $balance=array(
                'status'=>-1
            );
            return $balance;
        }
    }

    //查询校园卡余额接口，直接返回校园卡余额
    public function getMoneycampus(){
//        $ch=curl_init();
//        curl_setopt($ch,CURLOPT_URL,$this->url."queryUserBalances.action");
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
//        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        $content=curl_exec($ch);
//        curl_close ( $ch );
        $setArr=array(
            CURLOPT_URL=>$this->url."queryUserBalances.action",
            CURLOPT_COOKIE=>'JSESSIONID='.session("JSESSIONID"),
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>false,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        $content = $this->curl->getCurlResult();
        $content=explode("元",$content,2);
        $content=explode("<label>",$content[0]);
        return trim($content[2]);
    }


    //处理cookie头文件
    private function handleCookie($cookieFile) {
        $content = file_get_contents($cookieFile) ;
        $str=explode("JSESSIONID", $content);
        session("JSESSIONID",trim($str[1]));
        session("initTime",time()+300);
        //将结果放在cookie中，用户多次登录就不需要再次拉取
        setcookie("JSESSIONID",trim($str[1]),300);
        setcookie('initTime',time()+300,300);
        unlink($cookieFile);//删除cookieFile
    }

    //写入图片
    private function imgWrite($filename,$img) {
        $fp = fopen($filename,"wb");
        fwrite($fp,$img);
        fclose($fp);
    }


}

