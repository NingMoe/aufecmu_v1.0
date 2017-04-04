<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/10
 * Time: 15:44
 */
namespace Home\Logic;

class CurlLogic{

    private $setArr;

    public function __construct(){
        //默认配置项
        $this->setArr=array(
            CURLOPT_RETURNTRANSFER=>1,//默认返回抓取页面的信息，如不需要可自行设置为关闭
            CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36',
           	CURLOPT_ENCODING =>'gzip,deflate',
            CURLOPT_TIMEOUT=>10
        );
    }

    public function reInit(){
        unset($this->setArr);//清除所有已经设置的Curl常量
        $this->setArr=array(
            CURLOPT_RETURNTRANSFER=>1,//默认返回抓取页面的信息，如不需要可自行设置为关闭
            CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36',
            CURLOPT_ENCODING =>'gzip,deflate',
            CURLOPT_TIMEOUT=>10
        );
    }

    //设置curl的相应选项(采集公用函数)
    public function setOpt($outOpt){
    	//这里实际测试环境为php5.6，使用array_merge()函数将出现错误
        $this->setArr=$this->setArr+$outOpt;
    }

    //核心运行代码
    public function getCurlResult(){
        $ch=curl_init();
        curl_setopt_array($ch,$this->setArr);
        $content=curl_exec($ch);
        curl_close($ch);
        return $content;
    }
    
    //测试代码
    public function getSetArr(){
    	return $this->setArr;
    }

    //开启curl多线程采集之路
    //---------------------------------------------------------------------------------------------------------------------------------------------------------
    private function getCurlObject(){
        $ch=curl_init();
        curl_setopt_array($ch,$this->setArr);
        return $ch;
    }
}




