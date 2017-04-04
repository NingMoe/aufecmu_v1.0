<?php
/**
 * 开放平台校验类
 */
namespace Home\Logic;
class JudgeSignLogic {

    private $apply_id;
    private $apply_secret;
    private $url;
    public function __construct($url){

    }

    /*
     * 基本接口，获取验证码识别结果
     * */
    public function initFirst() {
        $this->apply_id='testid';
        $this->apply_secret='testsercet';
        $this->url='http://notice.woai662.net/info/yzmocr/index.php';
    }

    /*
     * 兼容获取密码接口
     * */
    public function initSecond() {
        $this->apply_id='ykt';
        $this->apply_secret='justDoIt';
        $this->url='http://cmuapi.woai662.com/cmuapi/yktPsd.php';
    }

    /*
    * 兼容自动更新校园卡余额
    * */
    public function initThird() {
        $this->apply_id='ykt';
        $this->apply_secret='justDoIt';
        $this->url='http://cmuapi.woai662.com/cmuapi/yktAutoApi.php';
    }

    //自定义函数
    public function getYktYzm() {
        $extArr=array(
            'yzmUrl'=>"https://ykt.aufe.edu.cn/checkCode.action",
            'type'=>'11',
            'yzmType'=>'.jpg',
            'sysType'=>'2',
        );
        return json_decode($this->getContent($extArr),true);
    }

    /*
     * 将计算好的签名发送到指定的url
     * 这里只提供post方式的接口文档
     * */
    public function getContent($extArr,array $postData=array(),$model=1){
        if($model == 1) {
            $this->initFirst();
        }
        else if($model ==2) {
            $this->initSecond();
        }
        else if($model ==3) {
            $this->initThird();
        }
        $param_array=$this->getSign($extArr);
        $str='?';
        $i=0;
        foreach( $param_array as $key=> $value){
            if($i<count($param_array)-1){
                $str=$str.$key."=".$value."&";
                ++$i;
            }
            else{
                $str=$str.$key."=".$value;
            }
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url.$str);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 4);
        if(count($postData) > 0) {
            curl_setopt($curl, CURLOPT_POST,1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$postData);
        }
        $content=curl_exec($curl);
        curl_close($curl);
        return $content;
    }

    /*
     * 计算签名
     * */
    private function getSign($extArr){
        $time=time()."";
        $noncestr=$this->createNonceStr();
        $param_array=array(
            'apply_id'=>$this->apply_id,
            'timestamp'=>$time,
            'nonce_str'=>$noncestr,
        )+$extArr;
        $param_array['sign']=$this->_cal_sign($param_array);

        return $param_array;
    }

    /*
	 * 创建10位随机字符串
	 * */
    private function createNonceStr($length = 10) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

	/**
	 * 计算签名
	 * @param array $param_array
	 */
	private function _cal_sign($param_array) {
		$names = array_keys($param_array);
		sort($names, SORT_STRING);
        
		$item_array = array();
		foreach ($names as $name) {
			$item_array[] = "{$name}={$param_array[$name]}";
		}
		$str = implode('&', $item_array) . '&key=' . $this->apply_secret;
		return strtoupper(md5($str));
	}
}