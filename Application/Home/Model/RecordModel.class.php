<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/28
 * Time: 12:34
 */
namespace Home\Model;
use Think\Model;

class RecordModel extends Model {

    /*
     * 根据传入的批量参数查询是否有符合该条件的数据，
     * 如果有返回真，否则返回假
     * */
    public function selectAll($arr){
        if($this->where($arr)->count()>0){
            return true;
        }
        else{
            return false;
        }
    }

    /*
     * 插入新函数
     * */
    public function addNew($insertArr){
        //先判断当前条件下是否符合插入基本条件
        if(! $this->selectAll(array(
            'studentid'=>$insertArr['studentid'],
            'date'=>$insertArr['date'],
        ))){
            if($this->data($insertArr)->add()){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }
}




