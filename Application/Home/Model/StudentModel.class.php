<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/31
 * Time: 23:39
 */
namespace Home\Model;
use Think\Model;

class StudentModel extends Model{

    /*
     * 根据传入的批量参数查询是否有符合该条件的数据，
     * 如果有返回真，否则返回假，自动判定是否为数组，不是默认为根据学号查询
     * */
    public function isExist($arr){
        if(!is_array($arr)) {
            $arr=array(
                'studentid'=>$arr
            );
        }
        if($this->where($arr)->count()>0){
            return true;
        }
        else{
            return false;
        }
    }

    /*
     * 获取用户基本信息
     * */
    public function getInfo($studentid){
        $res = $this->where(array('studentid'=>"$studentid"))->find();
        if(!empty($res['studentid'])){
            return $res;
        }
        else{
            return null;
        }
    }

    /*
     * 添加新用户
     * */
    public function addNew($data){
        if($this->data($data)->add()){
            return true;
        }
        else{
            return false;
        }
    }

    /*
     * 更新用户
     * 传入两个参数
     * 第一个为学号表示即将更新的参数
     * 第二个是更新的关联数组
     * */
    public function update($studentid,$data) {
        if($this->where("`studentid`='$studentid'")->data($data)->save() !==false){
            return true;
        }
        else{
            return false;
        }
    }

    /*
     * 添加购电信息，根据主键studentid添加用户的寝室信息
     * */
    public function addElectricInfo($studentid,$info){
        if($this->where("`studentid`='$studentid'")->count()>0){
            $update['electricinfo']=$info;
            if($this->where("`studentid`='$studentid'")->save($update)!==false)
                return true;
            else
                return false;
        }
        else{
            $update['studentid']=$studentid;
            $update['electricinfo']=$info;
            if($this->data($update)->add()){
                return true;
            }
            else{
                return false;
            }
        }
    }
}

