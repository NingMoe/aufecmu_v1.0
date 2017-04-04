<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/10
 * Time: 21:45
 */
namespace Home\Controller;
use Think\Controller;

class TestController extends Controller{

    public function index(){
        $data['yzm']=D("Curl","Logic")->getYzmInfo();
        $this->assign($data);
        $this->display();
    }

    public function test(){
        $data['yzm']=D("Curl","Logic")->getYzmInfo();
        $this->assign($data);
        $this->display();
    }

}

