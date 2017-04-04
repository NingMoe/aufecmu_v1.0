<?php
/**
 * Created by PhpStorm.
 * User: ancai4399
 * Date: 2016/9/17
 * Time: 18:01
 * -M-jAJdl-Zdy-alkLh3mXwbn0z6ZeB1I21N3ZmDH8Ik    白底SA  logo
 * -M-jAJdl-Zdy-alkLh3mX_JY1qBUcIngKqw-N6FDTaI
 */


$img="test.jpg";//需要上传的图片名称
$res=getThumbId($img);
var_dump(json_decode($res,true));



function getThumbId($img){
    $accessToken=getAccessToken();
    $url="https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=$accessToken";
    $real_path= __DIR__.DIRECTORY_SEPARATOR.$img;
    $data= array("buffer"=>"@{$real_path}");
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    curl_close($curl); // 关键CURL会话
    return $tmpInfo;
}

function getAccessToken() {
    return file_get_contents("http://121.42.57.23/wxJssdk/JssdkInterface.php?type=access_token_web");
}

function updateAccessToken() {
    return file_get_contents("http://ancai4399.com/wxJssdk/JssdkInterface.php?type=update_access_token");
}
