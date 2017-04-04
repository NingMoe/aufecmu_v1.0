<?php
namespace Home\Controller;
use Think\Controller;

class SuccessController extends Controller{
	
	public function index(){
		$data['title']=I("get.title","操作成功");
        $data['content']=I("get.content","操作成功喽~");
        $data['redirectUri']=urldecode(I("get.redirectUri",""));
		$this->assign($data);
		$this->display();
	}
	
}



?>