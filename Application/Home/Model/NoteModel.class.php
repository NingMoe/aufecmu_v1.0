<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/2/21
 * Time: 15:15
 */
namespace Home\Model;
use Think\Model;

class NoteModel extends Model{
    /*
     * 允许传入数组和非数组以验证是否存在，存在返回true
     * */
    public function isExist($idArr) {
        if(!is_array($idArr)) {
            $idArr=array(
                'id'=>$idArr
            );
        }
        if($this->where($idArr)->count() > 0) {
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

    //批量传入关联数组以获取基本信息，返回批量数据
    public function getInfoByArr($arr) {
        return $this->where($arr)->select();
    }

    //批量传入关联数组以获取基本信息，返回一条数据
    public function getOneInfoByArr($arr) {
        return $this->where($arr)->find();
    }

    //根据要求拉取大量数据库数据，测试完成许封锁多余数据
    public function getData($offset,$length=8,$arr=1) {
        return $this->where($arr)->join("__ISA__ on __NOTE__.openid=__ISA__.openid")->limit($offset,$length)->order("signtime desc")->select();
    }

    //根据id拉取一条数据库数据，测试完成许封锁多余数据
    public function getOneDate($id) {
        return $this->where(1)->join("__ISA__ on __NOTE__.openid=__ISA__.openid and __NOTE__.id='$id'")->find();
    }

    /*
     * 根据id更新函数
     * 参数1：需要更改的帖子id
     * 参数2：待更新的关联数组
     * */
    public function update($id,$upArr) {
        if($this->where("`id`='$id'")->data($upArr)->save() !== false){
            return true;
        }
        else{
            return false;
        }
    }
}