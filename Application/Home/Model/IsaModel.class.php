<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/7/30
 * Time: 23:17
 * status状态码：
 * 1：登陆状态
 * 2：注销状态
 */
namespace Home\Model;
use Think\Model;
//openid对应的是用户针对服务号的openid
class IsaModel extends Model{

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
        return $this->where($arr)->find();
    }

    /*
     * 根据openid更新函数
     * 参数1：需要更改的用户openid
     * 参数2：待更新的关联数组
     * */
    public function update($openid,$upArr) {
        if($this->where("`openid`='$openid'")->data($upArr)->save() !== false){
            return true;
        }
        else{
            return false;
        }
    }

    /*
     * 一个专用于读取两表用户个人信息
     * */
    public function getInfo($openid,$status=1) {
        return $this->join("__STUDENT__ on __ISA__.studentid = __STUDENT__.studentid and __ISA__.openid='$openid' and __ISA__.status='$status'")->find();
    }



    //--------------------------------------分隔线，以下函数用于测试使用---------------------------------------------------------------------------------------
    //根据学号获取用户信息
    public function getInfoByStu($studentid){
        $selectArr=array('studentid'=>$studentid);
        $res=$this->where($selectArr)->limit(1)->select();
        if(!empty($res)){
            return $res[0];
        }
        else{
            return null;
        }
    }

    //根据index获取用户信息
    public function getInfoByIndex($index){
        $res=$this->where(1)->limit($index,1)->select();
        if(!empty($res)){
            return $res[0];
        }
        else{
            return null;
        }
    }

    //新增数据，
    public function addNewForAc($arrAc){
        if(! $this->isExist($arrAc['unionid'],$arrAc['studentid'])){
            if($this->data($arrAc)->add()){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return true;
        }
    }
}