<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/7/30
 * Time: 23:17
 */
namespace Home\Model;
use Think\Model;
//openid对应的是用户针对使用一卡通功能的订阅号的openid
class YinterfaceModel extends Model{
    /*
     * 允许传入数组和非数组以验证是否存在，存在返回true
     * */
    public function isExist($openidArr) {
        if(!is_array($openidArr)) {
            $openidArr=array(
                'openid'=>$openidArr
            );
        }
        if($this->where($openidArr)->count() > 0) {
            return true;
        }
        else{
            return false;
        }
    }

    //新增数据
    public function addNew($arr){
        if($this->data($arr)->add()){
            return true;
        }
        else{
            return false;
        }
    }

    //批量传入关联数组以获取基本信息，返回一条数据
    public function getInfoByArr($arr){
        $res=$this->where($arr)->find();
        if(!empty($res['openid'])){
            return $res;
        }
        else{
            return null;
        }
    }

}