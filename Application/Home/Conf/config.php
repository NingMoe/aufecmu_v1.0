<?php
return array(
    //系统默认配置项
    'URL_MODEL'             =>  '3',
    'DB_PARAMS'             =>  array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),//设置数据库字段为大小写敏感
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost',   // 服务器地址
    'DB_NAME'               =>  'club',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'club_',    // 数据库表前缀

    //'配置项'=>'配置值'
    'DEFAULT_URL'    =>  'http://211.86.241.180/',
    'DEFAULT_URL_YKT'    =>  'https://ykt.aufe.edu.cn/',
    'DEFAULT_HEADIMGURL'    =>  'http://wx.ancai4399.com/public_img/touxiang.jpg',
    'DEFAULT_YZM'			=>	'./Public/img_yzm/',
    'ABSOLUTE_CACHE'			=>	ENTRA_PATH.'Public'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR,
    'AU_KEY'				=>	'2495ifmwayurtlcxs',//公钥
    'OWN_KEY'				=>	'woai565',//私钥
    'pattern'              =>   "1", //默认使用验证码机械识别
    'userinfoUrl'           =>  "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=%s#wechat_redirect",
    'baseUrl'           =>  "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx5aba40d737e98b5d&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect"
);