<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/2/21
 * Time: 15:17
 */
namespace Home\Model;
use Think\Model;

class CommentModel extends Model{

    /*
     * 允许传入数组和非数组以验证是否存在，存在返回true
     * */
    public function isExist($cidArr) {
        if(!is_array($cidArr)) {
            $cidArr=array(
                'cid'=>$cidArr
            );
        }
        if($this->where($cidArr)->count() > 0) {
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

    //根据要求拉取大量数据库数据，测试完成许封锁多余数据
    public function getData($offset,$length=30,$id=1) {
        return $this->field("club_isa.openid,club_isa.headimgurl,club_isa.cname,club_comment.*")->where(1)->join("__ISA__ on __COMMENT__.openid=__ISA__.openid and __COMMENT__.id='$id'")->limit($offset,$length)->order("comtime asc")->select();
    }

    /*
     * 根据cid更新函数
     * 参数1：需要更改的用户cid
     * 参数2：待更新的关联数组
     * */
    public function update($cid,$upArr) {
        if($this->where("`cid`='$cid'")->data($upArr)->save() !== false){
            return true;
        }
        else{
            return false;
        }
    }

}