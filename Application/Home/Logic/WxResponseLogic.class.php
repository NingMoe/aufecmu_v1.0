<?php
namespace Home\Logic;
//gh_2f97120599f5
class WxResponseLogic{
    private $token;

    public function __construct() {
        $this->token = "yktToken";
    }

    public function valid() {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function chatBack(){
        if (isset($_GET['echostr']))
        {
            $this->valid();
        }
        else {
            $postDate = $GLOBALS["HTTP_RAW_POST_DATA"];
            $object= simplexml_load_string($postDate,"SimpleXMLElement",LIBXML_NOCDATA);
            $arr['openid']=$object->FromUserName;
            $arr['mediaid']=$object->ToUserName;
            $arr['MsgType']=$object->MsgType;
            $arr['Event']=$object->Event;
            if($object->Event == "user_get_card") {
                $arr['code']= $object->UserCardCode ;
                $arr['cardId']= $object->CardId ;
            }
            return $arr;
        }
    }

    public function chatBackForInterface(){
        if (isset($_GET['echostr']))
        {
            $this->valid();
        }
        else {
            $postDate = $GLOBALS["HTTP_RAW_POST_DATA"];
            $object= simplexml_load_string($postDate,"SimpleXMLElement",LIBXML_NOCDATA);
            return $object;
        }
    }

    public function getBasicInfo($openId) {
        //尝试两次，自动获取微信基本信息，失败则直接返回空
        $access_token=file_get_contents("http://121.42.57.23/wxJssdk/JssdkInterface.php?type=access_token_web");
        $info=$this->getInfo($openId,$access_token);
        if(!empty($info['openid']) && !empty($info['unionid'])) {
            return $info;
        }
        else if($info['errcode']==40001) {
            //只有在accesstoken错误的时候我们才会更新
            $access_token=file_get_contents("http://121.42.57.23/wxJssdk/JssdkInterface.php?type=update_access_token");
            $info=$this->getInfo($openId,$access_token);
            if(!empty($info['openid']) && !empty($info['unionid'])){
                return $info;
            }
            else{
                return null;//获取基本信息失败，直接返回空
            }
        }
        else{
            return null;
        }
    }

    private function getInfo($openId,$access_token){
        //https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=o16hwwb9HjRJ9uxDHWqd4FoHdeFI&lang=zh_CN
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openId&lang=zh_CN";
        $res = file_get_contents($url); //获取文件内容或获取网络请求的内容
        $result = json_decode($res, true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
        return $result;
    }

    public function backLogin($openId,$publicId){
        $Title="请先登录一卡通系统";
        $Description="点击安全登录掌上一卡通系统>>>";
        $openid=authcode($openId, "ENCODE", C("OWN_KEY"));
        $pu=authcode($publicId, "ENCODE", C("OWN_KEY"));
        $scope='snsapi_userinfo';
        $url=urlencode(U("Index/bindUser?userid=$openid&pubid=$pu","",true,true));
        $retUrl=sprintf(C("userinfoUrl"),$url);
//        $retUrl="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=$url&response_type=code&scope=$scope&state=#wechat_redirect";
        $replyXml = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>1</ArticleCount>
								<Articles>
								<item>
									<Title><![CDATA[%s]]></Title>
									<Description><![CDATA[%s]]></Description>
									<Url><![CDATA[%s]]></Url>
								</item>
								</Articles>
								</xml>";
        $resultStr = sprintf($replyXml,$openId,$publicId,time(),$Title,$Description,$retUrl);
        echo $resultStr;die;
    }

    public function backPerson($openId,$publicId,$Title="欢迎使用掌上一卡通系统") {
        $state="";
        $url=urlencode(U("Person/functionSelect","",true,true));
        $url1=urlencode(U("Save/save","",true,true));
        $url2=urlencode(U("Safe/safe?option=2","",true,true));
        $url3=urlencode(U("Safe/safe?option=3","",true,true));
        $url4=urlencode(U("Safe/safe?option=4","",true,true));
        $url5=urlencode(U("Safe/safe?option=5","",true,true));

        //$url6=urlencode(U("Safe/safe?option=6","",true,true));
        $retUrl=sprintf(C("baseUrl"),$url);
        $retUrl1=sprintf(C("baseUrl"),$url1);
        $retUrl2=sprintf(C("baseUrl"),$url2);
        $retUrl3=sprintf(C("baseUrl"),$url3);
        $retUrl4=sprintf(C("baseUrl"),$url4);
        $retUrl5=sprintf(C("baseUrl"),$url5);
//        $retUrl1="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=$url1&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
//        $retUrl2="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=$url2&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
//        $retUrl3="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=$url3&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
//        $retUrl4="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=$url4&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
//        $retUrl5="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=$url5&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
        $retUrl6="https://jinshuju.net/f/OIGp7T";
        $PicUrl_1="http://wx.ancai4399.com/ykt/Public/ykticon/saveMoney.png";
        $PicUrl_2="http://wx.ancai4399.com/ykt/Public/ykticon/electric.png";
        $PicUrl_3="http://wx.ancai4399.com/ykt/Public/ykticon/netPay.png";
        $PicUrl_4="http://wx.ancai4399.com/ykt/Public/ykticon/password.png";
        $PicUrl_5="http://wx.ancai4399.com/ykt/Public/ykticon/lose.png";
        $PicUrl_6="http://wx.ancai4399.com/ykt/Public/ykticon/response.png";
        $Title1="饭卡圈存";
        $Title2="寝室购电";
        $Title3="热点缴费";
        $Title4="密码修改";
        $Title5="一卡通挂失";
        $Title6="使用反馈";
        $replyXml = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>7</ArticleCount>
								<Articles>
									<item>
										<Title><![CDATA[%s]]></Title>
										<Url><![CDATA[%s]]></Url>
									</item>
									<item>
										<Title><![CDATA[%s]]></Title>
										<PicUrl><![CDATA[%s]]></PicUrl>
										<Url><![CDATA[%s]]></Url>
									</item>
									<item>
										<Title><![CDATA[%s]]></Title>
										<PicUrl><![CDATA[%s]]></PicUrl>
										<Url><![CDATA[%s]]></Url>
									</item>
									<item>
										<Title><![CDATA[%s]]></Title>
										<PicUrl><![CDATA[%s]]></PicUrl>
										<Url><![CDATA[%s]]></Url>
									</item>
									<item>
										<Title><![CDATA[%s]]></Title>
										<PicUrl><![CDATA[%s]]></PicUrl>
										<Url><![CDATA[%s]]></Url>
									</item>
									<item>
										<Title><![CDATA[%s]]></Title>
										<PicUrl><![CDATA[%s]]></PicUrl>
										<Url><![CDATA[%s]]></Url>
									</item>
									<item>
										<Title><![CDATA[%s]]></Title>
										<PicUrl><![CDATA[%s]]></PicUrl>
										<Url><![CDATA[%s]]></Url>
									</item>
								</Articles>
								</xml>";
        $resultStr = sprintf($replyXml,$openId,$publicId,time(),$Title,$retUrl,$Title1,$PicUrl_1,$retUrl1,$Title2,$PicUrl_2,$retUrl2,$Title3,$PicUrl_3,$retUrl3,$Title4,$PicUrl_4,$retUrl4,$Title5,$PicUrl_5,$retUrl5,$Title6,$PicUrl_6,$retUrl6);
        echo $resultStr;die;
    }

    public function back($openId,$publicId,$info){
        $replyXml = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[text]]></MsgType>
								<Content><![CDATA[%s]]></Content>
								</xml>";
        $resultStr = sprintf($replyXml,$openId,$publicId,time(),$info);
        echo $resultStr;die;
    }

    public function test($openId,$publicId,$info){
        $replyXml = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[text]]></MsgType>
								<Content><![CDATA[%s]]></Content>
								</xml>";
        $resultStr = sprintf($replyXml,$openId,$publicId,time(),$info);
        echo $resultStr;die;
    }

    public function error($openId,$publicId){
        $replyXml = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[text]]></MsgType>
								<Content><![CDATA[%s]]></Content>
								</xml>";
        $resultStr = sprintf($replyXml,$openId,$publicId,time(),"请重新点击菜单,若多次失败请联系管理人员!");
        echo $resultStr;die;
    }

    public function getTitle($unionid){
        $isaInfo=D("Isa")->getInfoByAll(array('unionid'=>"$unionid",'status'=>1));
        $stuModel=D("Student");
        $studentid=$isaInfo['studentid'];
        if($stuModel->selectAll(array('studentid'=>$studentid))){
            $info=$stuModel->getInfo($studentid);
            if(! empty($info)){
                return "当前用户:".$studentid."(".$info['name'].")";
            }
        }
        return "欢迎使用掌上一卡通系统";
    }

}


?>