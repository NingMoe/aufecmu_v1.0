<?php
return array(
    //系统默认配置项
    'URL_MODEL'             =>  '3',
    'DB_PARAMS'             =>  array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),//设置数据库字段为大小写敏感

    //'配置项'=>'配置值'
    'DEFAULT_URL'=>'https://ykt.aufe.edu.cn/',
    'DEFAULT_HEADIMGURL'=>'http://wx.ancai4399.com/public_img/touxiang.jpg',
    'DEFAULT_YZM'			=>	'./Public/img_yzm/',
    'ABSOLUTE_CACHE'			=>	ENTRA_PATH.'Public'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR,
    'AU_KEY'				=>	'2495ifmwayurtlcxs',//公钥
    'OWN_KEY'				=>	'woai565',//私钥
    'DEFAULT_ICONURL'       => 'http://wx.ancai4399.com/ykt/Public/ykticon/'
//    'SHOW_PAGE_TRACE'       =>  true,
);