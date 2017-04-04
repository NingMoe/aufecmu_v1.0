<?php
namespace Home\Controller;
use Think\Controller;

class ErrorController extends Controller{

	//接受标准错误页面
	public function index(){
		$data['title']=I("title",false)===false ? "错误提示界面" : I("title");
		$data['des']=I("des",false)===false ? "亲，您遇到了错误哦，请重新操作，若持续遇到这种情况请与管理员交流!" : I("des");;
        $data['redirectUri']=urldecode(I("get.redirectUri",""));
        $this->assign($data);
		$this->display();
	}
}



?>