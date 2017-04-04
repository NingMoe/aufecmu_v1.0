<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2017/2/27
 * Time: 14:19
 */

require_once './autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
$accessKey = 'WN-R4Thq6VlAuGh510VfGXue4YutvS3B7pMPnj_b';
$secretKey = '1nu027Jw5mpAkR6MNHdOaWhd0hgnzxUvDirZ7fM6';
// 构建鉴权对象
$auth = new Auth($accessKey, $secretKey);
// 要上传的空间
$bucket = 'community';
// 生成上传 Token
//echo $auth->uploadToken($bucket);die;
echo $auth->uploadToken($bucket);die;
echo json_encode(array('uptoken'=>$auth->uploadToken($bucket)));