<?php
namespace Home\Logic;
/*
 * curl逻辑层
 * */
class CurlLogic{
    
    private $yzmPath;
    private $cache;
    private $url;

    public function __construct() {
        $this->yzmPath=C("DEFAULT_YZM");
        $this->cache=C("ABSOLUTE_CACHE");
        $this->url=C("DEFAULT_URL");
    }

    public function loginInit(){
        srand((double)microtime()*100000);
        $random=rand(0,1000000);
        $yzmFileName=$this->yzmPath."$random.jpg";
        $cookieFile=$this->cache."$random.txt";
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $this->url."checkCode.action" );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 1 );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt ( $ch, CURLOPT_USERAGENT, '"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)' );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 5 );
        $img=curl_exec ( $ch );
        curl_close ( $ch );
        $this->handleCookie($cookieFile);
        $this->imgWrite($yzmFileName,$img);
        return $yzmFileName;
    }
    
    public function personCurl($Student_Id,$Password,$yzm) {
        if(!$this->checkYzm($yzm)) {
            return 3;//验证码错误
        }
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$this->url."login.action");
        curl_setopt ($ch,CURLOPT_REFERER,$this->url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
        curl_setopt($ch,CURLOPT_POST,1);
        $post['loginMode'] = "express";
        $post['username'] = $Student_Id;
        $post['password'] = $Password;
        $post['checkCode'] = $yzm;
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
        $content=curl_exec($ch);
        curl_close ( $ch );
        if(strpos($content,"login_blank")!=false){//检测是否登录成功
            return 1;
        }
        else{
            //判断是否为账号密码错误
            return 2;
        }
    }

    public function checkYzm($yzm) {
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$this->url."validateYzm.action");
        curl_setopt ($ch,CURLOPT_REFERER,$this->url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
        curl_setopt($ch,CURLOPT_POST,1);
        $post['checkCode'] = $yzm;
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
        $content=curl_exec($ch);
        curl_close ( $ch );
        return !($content=="false");
    }

    /*
     * 自动拉取验证码两次
     * */
    public function autoEntre($zjh,$mm) {
        //获取验证码，尝试两次
        for($i=0;$i<2;++$i) {
            $res=$this->getYzmInfo();
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
            $random =rand(0,1000000);
            $extArr=array(
                'yzmUrl'=>$this->url."checkCode.action",
                'type'=>'11',
                'yzmType'=>'.jpg',
                'sysType'=>'2',
            );
            $res=json_decode(D("JudgeSign","Logic")->getContent($extArr),true);
            if(!empty($res['result']) && strlen($res['result'])==4 ){
                //这里的result存在，并且验证码长度为4，说明验证码识别是成功的
                session("JSESSIONID",$res['cookie']['cookie1']);
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
        $url=C("DEFAULT_URL");
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url."queryUserInfo.action");
        curl_setopt($ch,CURLOPT_REFERER,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_COOKIE,"JSESSIONID=".session("JSESSIONID"));
        $content=curl_exec($ch);
        curl_close ( $ch );
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

    //处理cookie头文件
    private function handleCookie($cookieFile) {
        $content = file_get_contents($cookieFile) ;
        $str=explode("JSESSIONID", $content);
        session("JSESSIONID",trim($str[1]));
        unlink($cookieFile);//删除cookieFile
    }

    //写入图片
    private function imgWrite($filename,$img) {
        $fp = fopen($filename,"wb");
        fwrite($fp,$img);
        fclose($fp);
    }
    
}

?>