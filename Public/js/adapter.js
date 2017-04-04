var yktAPI="http://wx.aufe.vip/aufecmu/index.php?s=Home/Ykt/",
    URL="http://wx.aufe.vip/aufecmu/",
    APPID="wx5aba40d737e98b5d";
var Public={
    loadAjaxData : function (uri, data, fn_success, fn_error, type) {
        type = type || "get";
        $.ajax({
            'type':type,
            'url':uri,
            'data':data,
            'dataType':'json',
            success: function (data) {
                fn_success(data);
            },
            error:function (e,t) {
                fn_error(e,t);
            }
        })
    },
    wxLogin: function (targetSign) {
        var e=URL+"index.php?s=Home/Safe/safeRoute";
        window.localStorage.setItem("option",targetSign);
        window.location.href="https://open.weixin.qq.com/connect/oauth2/authorize?appid="+APPID+"&redirect_uri="+
            encodeURIComponent(e)+"&response_type=code&scope=snsapi_base&state="+targetSign+"#wechat_redirect"
    },
    isWeiXin:function(){
        var e=window.navigator.userAgent.toLowerCase();
        return"micromessenger"==e.match(/MicroMessenger/i)
    }

}