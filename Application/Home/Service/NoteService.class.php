<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/2/21
 * Time: 15:08
 *
 * 返回值都为数组形式，其中retCode数值所代表的意义如下所示
 *  1：操作成功
 * -1：用户未登录，请先登录
 * -2：数据库操作执行失败，内部错误
 */
namespace Home\Service;

class NoteService {

    private $comment;

    private $do;

    private $note;

    public function __construct() {
        $this->comment=D("Comment");
        $this->note=D("Note");
        $this->do=D("Do");
    }

    /*
     * 拉取数据库帖子信息
     * 接收参数：
     * $start：开始位置
     * $count：拉取信息条数
     * */
    public function getData($start,$count=8,$ischeck=false) {
        if($ischeck !== false) {
            if( ! $this->isLogin() ) {
                return array(
                    'retCode'=>-1
                );
            }
        }
        $res = $this->note->getData($start,$count);
        $result=array();
        foreach ($res as $item) {
            $item['noteid']=authcode($item['id'], "ENCODE", C("OWN_KEY"));
            $item['time']=getTime($item['signtime']);
            $item['imgInfo']=$item['img_info'];
            unset($item['id']);
            unset($item['img_info']);
            unset($item['signtime']);
            $result[]=$item;
        }
        return $result;
    }

    /*
     * 根据id拉取某一个帖子的信息
     * */
    public function getOneNote($id) {
        $res = $this->note->getOneDate($id);
        $res['time']=getTime($res['signtime']);
        $res['backuser']=authcode($res['openid'], "ENCODE", C("OWN_KEY"));
        $res['noteid']=authcode($res['id'], "ENCODE", C("OWN_KEY"));
        $res['imgInfo']=$res['img_info'];
        unset($res['id']);
        unset($res['img_info']);
        unset($res['openid']);
        return $res;
    }

    /*
     * 拉取数据库某一个帖子的评论信息
     * */
    public function getComments($start,$id,$count=30,$ischeck=false) {
        if($ischeck !== false) {
            if( ! $this->isLogin() ) {
                return array(
                    'retCode'=>-1
                );
            }
        }
        // return array('retCode'=>-1);
        $res=$this->comment->getData($start,$count,$id);
        $result=array();
        $replied_comment=array();
        //第一步，拉取single为0的所有评论并设置为主评论
        if(session("?openid")) {
            $openid=session("openid");
            foreach ($res as $item) {
                if($item['openid'] == $openid) {
                    $item['visibility']="hide";
                }
                else {
                    $item['visibility']="";
                }
                if($item['single'] == 0) {
                    $result[]=$item;
                }
            }
        }
        else {
            foreach ($res as $item) {
                $item['visibility']="";
                if($item['single'] == 0) {
                    $result[]=$item;
                }
            }
        }
        //为从属评论找到他们的主评论
        foreach ($res as $it) {
            if($it['single'] != 0) {
                for( $i =0 ;$i < count($result) ; ++$i) {
                    if($it['note_openid'] == $result[$i]['openid'] && $it['single'] == $result[$i]['cid']) {
                        $it['noteid']=authcode($it['id'], "ENCODE", C("OWN_KEY"));
                        $it['comid']=authcode($it['cid'], "ENCODE", C("OWN_KEY"));
                        $it['notename']=$it['note_name'];
                        $it['backuser']=authcode($it['openid'], "ENCODE", C("OWN_KEY"));
                        $it['time']=getTime($it['comtime']);
                        unset($it['id']);
                        unset($it['cid']);
                        unset($it['note_name']);
                        unset($it['openid']);
                        unset($it['comtime']);
                        unset($it['single']);
                        $result[$i]['replied_comment'][]=$it;
                        break;
                    }
                    if($i==count($result)-1) {
                        //找不到该条评论属于谁，默认将该评论设置为single为0且没有回复的状态
                        $it['noteid']=authcode($it['id'], "ENCODE", C("OWN_KEY"));
                        $it['comid']=authcode($it['cid'], "ENCODE", C("OWN_KEY"));
                        $it['notename']=$it['note_name'];
                        $it['backuser']=authcode($it['openid'], "ENCODE", C("OWN_KEY"));
                        $it['time']=getTime($it['comtime']);
                        unset($it['id']);
                        unset($it['cid']);
                        unset($it['note_name']);
                        unset($it['openid']);
                        unset($it['comtime']);
                        unset($it['single']);
                        $result[]=$it;
                    }
                }
            }
        }
        $_result=array();
        foreach ($result as $item) {
            $item['noteid']=authcode($item['id'], "ENCODE", C("OWN_KEY"));
            $item['comid']=authcode($item['cid'], "ENCODE", C("OWN_KEY"));
            $item['notename']=$item['note_name'];
            $item['backuser']=authcode($item['openid'], "ENCODE", C("OWN_KEY"));
            $item['time']=getTime($item['comtime']);
            unset($item['id']);
            unset($item['cid']);
            unset($item['note_name']);
            unset($item['openid']);
            unset($item['comtime']);
            unset($item['single']);
            $_result[]=$item;
        }
        return $_result;
    }

    /*
     * 获得用户赞列表
     * */
    public function getLikesList($id,$ischeck=false) {
        if($ischeck !== false) {
            if( ! $this->isLogin() ) {
                return array(
                    'retCode'=>-1
                );
            }
        }
        $res = $this->do->getLikes($id);
        return $res;
    }

    /*
     * 判断当前用户是否赞了当前该帖子
     * */
    public function isLiked($id) {
        return $this->do->isExist(array(
            'openid'=>session("openid"),
            'id'=>$id,
            'thing'=>1
        ));
    }

    /*
     * 用户发布一个帖子
     * 接收参数
     * $noteContent：帖子内容
     * $ischeck：是否需要进行登录check
     * */
    public function signNote($noteContent,$img_info,$ischeck=false) {
        $openid=session("openid");
        if($ischeck !== false) {
            if( ! $this->isLogin() ) {
                return array(
                    'retCode'=>-1
                );
            }
        }
        if($this->note->add(array(
            'content'=>$noteContent,
            'openid'=>$openid,
            'img_info'=>empty($img_info) ? null : $img_info,
            'signtime'=>time()
        ))) {
            return array('retCode'=>1);
        }
        else {
            return array('retCode'=>-2);
        }
    }

    /*
     * 用户评论一个帖子
     * 接收参数
     * $id：该评论对应的帖子id
     * $comment：评论内容
     * $note_openid：该评论是否存在需要@的人员
     * $ischeck：是否需要进行登录check
     * */
    public function commentNote($id,$comment,$isSingle,$note_openid=null,$noteName=null,$ischeck=false) {
        $openid=session("openid");
        if($ischeck !== false) {
            if( ! $this->isLogin() ) {
                return array(
                    'retCode'=>-1
                );
            }
        }
        $date=array(
            'id'=>$id,
            'openid'=>$openid,
            'note_openid'=>$note_openid,
            'note_name'=>$noteName,
            'comment'=>$comment,
            'single'=>$isSingle ,
            'comtime'=>time(),
        );
        if($this->comment->add($date)) {
            //要求改id下的帖子评论数+1
            $noteInfo=$this->note->getOneInfoByArr(array('id'=>$id));
            return array('retCode'=> $this->note->update($id,array('comcount'=>$noteInfo['comcount']+1)) ? 1 : -2 );
        }
        else {
            return array('retCode'=>-2);
        }
    }

    /*
     * 用户行为记录
     * 接收参数
     * $id：该评论对应的帖子id
     * $ischeck：是否需要进行登录check，这里默认需要登录验证
     *
     * 定义痕迹类型：（thing）
     * 1：点赞（点赞将自动判定是否已经存在，存在则删除该记录）
     * 2：评论
     *
     * */
    public function userMark($id,$thing=1,$ischeck=true) {
        $ip=getIPaddress();
        $openid=session("openid");
        if($ischeck === true) {
            if( ! $this->isLogin() ) {
                return array(
                    'retCode'=>-1
                );
            }
        }
        if($thing==1) {
            $whereArr=array(
                'openid'=>$openid,
                'id'=>$id,
                'thing'=>1
            );
            $noteInfo=$this->note->getOneInfoByArr(array('id'=>$id));
            
            if($this->do->isExist($whereArr)) {
                //已存在，删除该赞评论
                $this->do->deleteOne($whereArr,1) ? $this->note->update($id,array('zan'=>$noteInfo['zan']-1)) : "";
                return array(
                    'retCode'=>1,
                    'zan'=>"cancel"
                );
            }
        }
        if($this->do->add(array(
            'openid'=>$openid,
            'id'=>$id,
            'ipaddress'=>$ip,
            'addtime'=>time(),
            'thing'=>$thing
        ))) {
            $this->note->update($id,array('zan'=>$noteInfo['zan']+1));
            return array('retCode'=>1,'zan'=>"zan");
        }
        else {
            return array('retCode'=>-2,'zan'=>"zan");
        }
    }


    /*
     * 判断当前用户是否登录
     * */
    public function isLogin() {
        //查询该用户当前的微信授权状态
        $res=D("Init","Logic")->check(1);
        if($res['retCode'] == 1) {
            return true;
        }
        else {
            return false;
        }
    }

}



