<?php
namespace Home\Service;

class JssdkService {
	private $appId;
	private $path;

	public function __construct( ) {
		$this->appId = "wxa7f2fcac677b05d3";
		$this->path="./Public/jsapi_ticket_admin.json";
	}
	
	public function getSignPackage($url=null) {
		$jsapiTicket = $this->getJsApiTicket();
		if(empty($url)){
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		}
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		$signature = sha1($string);
		$signPackage = array(
				"appId"     => $this->appId,
				"nonceStr"  => $nonceStr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
				"String" => $string,
				"jsapiTicket"=>$jsapiTicket
		);		
		return $signPackage;
	}
	
	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	private function getJsApiTicket() {
		// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(file_get_contents($this->path ));
		if ($data->expire_time < time()) {
			$accessToken = $this->getAccessToken();
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
			$res=file_get_contents($url);
			$result=json_decode($res,true);
			if($result['errcode']==40001){
				//如果accesstoken过期,重要求服务端更新accesstoken
				$accessToken = $this->updateAccessToken();
				$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
				$res=file_get_contents($url);
				$result=json_decode($res,true);
			}
			$ticket = $result['ticket'];
			if ($ticket) {
				$data->expire_time = time()+7000;
				$data->jsapi_ticket = $ticket;
				$fp = fopen($this->path , "w");
				fwrite($fp, json_encode($data));
				fclose($fp);
				chmod($this->path ,0755);
			}
		} else {
			$ticket = $data->jsapi_ticket;
		}

		return $ticket;
	}

	private function getAccessToken() {
		return file_get_contents("http://121.42.57.23/wxJssdk/JssdkInterface.php?type=access_token_web");
	}

	private function updateAccessToken() {
		return file_get_contents("http://ancai4399.com/jssdk_server/JssdkInterface.php?type=update_access_token");
	}
}

?>