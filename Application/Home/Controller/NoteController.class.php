<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/2/21
 * Time: 22:36
 */
namespace Home\Controller;
use Think\Controller;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
/*
 * retCode：全局说明
 *  1：成功
 * -1：未登录
 * -2：数据库操作错误
 * -3：数据不完整
 *
 *
 * info：ajax返回到主界面的返回信息说明
 *
 * data：全局说明，拉取数据的全局说明
 * null：未能成功拉取数据则返回
 * 其他情况为二维数组
 * */
class NoteController extends Controller{

    /*
     * 获得主页数据
     * */
    public function getData() {
        $note=D("Note","Service");
        if(isset($_POST['start']) && isset($_POST['count'])) {
            $dateArr=$note->getData(I("post.start"),I("post.count"));
            if($dateArr != false) {
                $ajax=array(
                    'retCode'=>1,
                    'info'=>"ok",
                    'data'=>$dateArr
                );
            }
            else {
                $ajax=array(
                    'retCode'=>-2,
                    'info'=>"内部错误，请联系管理员味增！",
                    'data'=>null
                );
            }
        }
        else {
            $ajax=array(
                'retCode'=>-3,
                'info'=>"数据不完整"
            );
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 获得评论显示
     * */
    public function getComments() {
        $note=D("Note","Service");
        if(isset($_POST['start']) && isset($_POST['count']) && isset($_POST['noteid'])) {
            $dateArr=$note->getComments(I("post.start"),authcode(I("post.noteid"),"DECODE", C("OWN_KEY")),I("post.count"));
            if($dateArr !== false) {
                $ajax=array(
                    'retCode'=>1,
                    'info'=>"ok",
                    'data'=>$dateArr
                );
            }
            else {
                $ajax=array(
                    'retCode'=>-2,
                    'info'=>"内部错误，请联系管理员味增！",
                    'data'=>null
                );
            }
        }
        else {
            $ajax=array(
                'retCode'=>-3,
                'info'=>"数据不完整"
            );
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 获得赞了该条评论的人员的头像，这不需要判定是否登录
     * */
    public function getLikesList() {
        $note=D("Note","Service");
        if(isset($_POST['noteid'])) {
            $id=authcode(I("post.noteid"),"DECODE", C("OWN_KEY"));
            $ajax=array(
                'retCode'=>1,
                'info'=>"ok",
                'data'=>$note->getLikesList($id)
            );
        }
        else {
            $ajax=array(
                'retCode'=>-3,
                'info'=>"数据不完整"
            );
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 判断当前用户是否已经赞过这个评论
     * */
    public function isLiked() {
        $note=D("Note","Service");
        if($note->isLogin()) {
            if(isset($_POST['noteid'])) {
                $id=authcode(I("post.noteid"),"DECODE", C("OWN_KEY"));
                $ajax=array(
                    'retCode'=>1,
                    'data'=>$note->isLiked($id) ? 1  : 0
                );
            }
            else {
                $ajax=array(
                    'retCode'=>-3,
                    'info'=>"数据不完整"
                );
            }
        }
        else {
            $ajax=array(
                'retCode'=>-1,
                'info'=>"请先登录"
            );
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 发送一套帖子
     * */
    public function sendNote() {
        $note=D("Note","Service");
        if($note->isLogin() ) {
        	if(isset($_POST['content']) && isset($_POST['jsonImg'])) {
        		$content=I("content");
        		$jsonImg=urldecode(I("jsonImg"));
	            $res=$note->signNote($content,$jsonImg);
	            if($res['retCode'] == 1) {
	                $ajax=array(
	                    'retCode'=>1,
	                    'info'=>"ok"
	                );
	            }
	            else{
	                $ajax=array(
	                    'retCode'=>-2,
	                    'info'=>"内部错误，请联系管理员味增！"
	                );
	            }
        	}
            else {
            	$ajax=array(
                    'retCode'=>-3,
                    'info'=>"数据不完整，请刷新页面重试！"
                );
            }
        }
        else {
            $ajax=array(
                'retCode'=>-1,
                'info'=>"请先登录"
            );
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 发送一条评论
     * */
    public function sendComment() {
        $note=D("Note","Service");
        if($note->isLogin()) {
            if(isset($_POST['commentContent']) && isset($_POST['backUser']) && isset($_POST['noteid']) && isset($_POST['noteName']) && isset($_POST['isSingle']) ) {
                $id=authcode(I("post.noteid"),"DECODE", C("OWN_KEY"));
                $noteOpenid=authcode(I("post.backUser"),"DECODE", C("OWN_KEY"));
                $comment=I("post.commentContent");
                $noteName=I("post.noteName");
                $isSingle=authcode(I("post.isSingle"),"DECODE", C("OWN_KEY"));
                $res=$note->commentNote($id,$comment,$isSingle,$noteOpenid,$noteName);
                if($res['retCode'] == 1) {
                    $ajax=array(
                        'retCode'=>1,
                        'info'=>"ok"
                    );
                }
                else {
                    $ajax=array(
                        'retCode'=>-2,
                        'info'=>"内部错误，请联系管理员味增！"
                    );
                }
            }
            else {
                $ajax=array(
                    'retCode'=>-3,
                    'info'=>"数据不完整，请刷新页面重试！"
                );
            }
        }
        else {
            $ajax=array(
                'retCode'=>-1,
                'info'=>"请先登录"
            );
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 赞接口
     * */
    public function zan() {
        $note=D("Note","Service");
        if(isset($_POST['noteid'])) {
            $id=authcode(I("post.noteid"),"DECODE", C("OWN_KEY"));
            $ajax=$note->userMark($id);
        }
        else {
            $ajax=array(
                'retCode'=>-3,
                'info'=>"数据不完整，请刷新页面重试！"
            );
        }
        $this->ajaxReturn($ajax,"json");
    }

    /*
     * 获得七牛云权限key值
     * */
    public function getToken() {
    	require ENTRA_PATH.'autoload.php';
    	$accessKey = 'WN-R4Thq6VlAuGh510VfGXue4YutvS3B7pMPnj_b';
		$secretKey = '1nu027Jw5mpAkR6MNHdOaWhd0hgnzxUvDirZ7fM6';
		// 构建鉴权对象
		$auth = new Auth($accessKey, $secretKey);
		// 要上传的空间
		$bucket = 'community';
		// 生成上传 Token
		// echo "wdwa";
        $this->ajaxReturn(array('uptoken'=>$auth->uploadToken($bucket)),"json");
    }

}
