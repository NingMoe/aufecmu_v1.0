<?php
/**
 * Created by PhpStorm.
 * User: ancai4399
 * Date: 2016/12/18
 * Time: 12:50
 */

namespace Home\Model;
use Think\Model;

//openid对应的是用户针对会员卡所属订阅号的openid
class VipuserModel extends Model{
    /*
     * 允许传入数组和非数组以验证是否存在，存在返回 true
     * */
    public function isExist($openidArr) {
        if(!is_array($openidArr)) {
            $openidArr=array(
                'openid'=>"$openidArr"
            );
        }
        if($this->where($openidArr)->count() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    //新增数据
    public function addNew($arr) {
        if($this->data($arr)->add()){
            return true;
        }
        else{
            return false;
        }
    }

    //批量传入关联数组以获取基本信息，返回一条数据
    public function getInfoByArr($arr) {
        $res=$this->where($arr)->find();
        if(!empty($res['openid'])){
            return $res;
        }
        else{
            return null;
        }
    }

    /*
     * 根据openid更新函数
     * 参数1：需要更改的用户openid
     * 参数2：待更新的关联数组
     * */
    public function update($openid,$upArr){
        if($this->where("`openid`='$openid'")->data($upArr)->save() !== false){
            return true;
        }
        else{
            return false;
        }
    }

    //删除用户
    public function deleteInfo($whereArr) {
        return $this->where($whereArr)->delete();
    }
}
