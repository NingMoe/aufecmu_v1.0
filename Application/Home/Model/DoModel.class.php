<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/2/21
 * Time: 15:20
 */
namespace Home\Model;
use Think\Model;
class DoModel extends Model{

    /*
     * 允许传入数组和非数组以验证是否存在，存在返回true
     * */
    public function isExist($doArr) {
        if($this->where($doArr)->count() > 0) {
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

    /*
     * 删除某一条记录,$_count表示该函数只能删除多少条记录，这里要求外部必须传入1
     * 返回值为true 0 false ， 外部判断条件需要使用恒等于===
     * */
    public function deleteOne($whereArr,$_count=null) {
        $count=$this->where($whereArr)->count();
        if ($count > 0 ) {
            $count = empty($_count) ? $count : $_count;
            return $this->where($whereArr)->limit($count)->delete();
        }
        else {
            return 0;
        }
    }

    //拉取八条点赞头像
    public function getLikes($id,$whereArr=1,$count=7) {
        return $this->field("club_isa.headimgurl")->where($whereArr)->join("__ISA__ on __DO__.openid=__ISA__.openid and __DO__.id='$id' and __DO__.thing=1")->limit($count)->order("club_do.addtime desc")->select();
    }

    //批量传入关联数组以获取基本信息，返回一条数据
    public function getInfoByArr($arr){
        return $this->where($arr)->find();
    }


}