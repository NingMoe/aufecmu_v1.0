function parse(e){for(var t=e,o=new RegExp(/&lt;e (.*?)\/&gt;/g),i=!0;i;){var n=o.exec(e);if(n){var a=n[1].split(" "),r={};a.forEach(function(e){if(e.length>0){var t=e.split("="),o=t[0],i=t[1].split('"')[1];r[o]=i}});var s=n[0],c="";switch(r.type){case"@":c=this.parseMention(r);break;case"mention":c=this.parseMention(r);break;case"web":c=this.parseHref(r);break;case"hashtag":c=this.parseTag(r);break;default:c=this.parseStrangeType(r)}t=t.replace(s,c)}else i=!1}return t}
function parseMention(e){if("mention"==e.type){var t=(e.uid,decodeURIComponent(e.title));t=t.replace(/</g,"&lt;").replace(/>/g,"&gt;");var o=t;return o}var t=(e.uid,decodeURIComponent(e.name)),o=t;return o}
function parseHref(e){
    var t=(e.uid,decodeURIComponent(e.title));t=t.replace(/\+/g," ");var o=decodeURIComponent(e.href),i=decodeURIComponent(e.cache),n="";return n="undefined"!=i?'<a href="'+i+'" target="_blank">链接：</a><a href="'+o+'" title="'+t+'" target="_blank" style="color: #1685b9;">'+t+"</a>":'<a href="'+o+'" title="'+t+'" target="_blank">链接：'+t+"</a>"
}
function parseTag(e){
    var t=e.hid,o=decodeURIComponent(e.title);o=o.replace(/</g,"&lt;").replace(/>/g,"&gt;");
    var i='<span class="hashtag" hid="'+t+'">'+o+"</span>";return i
}
function parseStrangeType(e){var t=decodeURIComponent(e.title),o=t;return o}
function GetQueryString(e){var t=new RegExp("(^|&)"+e+"=([^&]*)(&|$)"),o=window.location.search.substr(1).match(t);return null!=o?unescape(o[2]):null}
function isPC(){
    for(var e=navigator.userAgent,t=["Android","iPhone","SymbianOS","Windows Phone","iPod","iPad"],o=!0,i=0;i<t.length;i++)
    if(e.indexOf(t[i])>0){o=!1;break}return o
}
function allCookies(){
    var e=Cookies.get("m_wx_name"),t=Cookies.get("m_user_id"),o=Cookies.get("m_user_avatar_url");
    e&&Cookies.set("m_wx_name",e,{expires:5}),t&&Cookies.set("m_user_id",t,{expires:5}),o&&Cookies.set("m_user_avatar_url",o,{expires:5})
}


/**
 * 替换所有匹配exp的字符串为指定字符串
 * @param exp 被替换部分的正则
 * @param newStr 替换成的字符串
 */
String.prototype.replaceAll = function (exp, newStr) {
    return this.replace(new RegExp(exp, "gm"), newStr);
};

/**
 * 原型：字符串格式化
 * @param args 格式化参数值
 */
String.prototype.format = function(args) {
    var result = this;
    if (arguments.length < 1) {
        return result;
    }

    var data = arguments; // 如果模板参数是数组
    if (arguments.length == 1 && typeof (args) == "object") {
        // 如果模板参数是对象
        data = args;
    }
    for ( var key in data) {
        var value = data[key];
        if (undefined != value) {
            result = result.replaceAll("\\{" + key + "\\}", value);
        }
    }
    return result;
}
var APPID="wx5aba40d737e98b5d",URL="http://wx.aufe.vip/aufecmu/",API_URL="http://wx.aufe.vip/aufecmu/index.php?s=Home/Note/",SRC_URL="http://wx.aufe.vip/aufecmu/index.php?s=Home/Person/",WAPI_URL="http://wx.aufe.vip/",APP_OPEN_ID="wx958aa81d89e22ca4",toastTime=null,displayTime=null,savedBgColor=null,codeMap={600:"网络超时",16903:"账户余额不足",16905:"提现过于频繁",16908:"您今日的提现次数超过限制",16909:"短时间内提现过于频繁，请稍后重试",50001:"请求失败(50001)",50003:"请求失败(50003)",50101:"圈子已被删除",50202:"圈主不能成为嘉宾",50203:"您要加入的圈子不存在",50204:"您已经是嘉宾",50205:"该链接已失效",50206:"已超过圈子嘉宾数量限制",50501:"主题已被删除"},
    Public={
        init:function(){
            this.getSize(),this.setBorderWidth(),$(".js_goto_my").on("click",function(){
                window.location.href="../myhome/myhome.html?d="+(new Date).getTime().toString().substr(-8)
            }),
                $(".js_goto_group").on("click",function(){
                    window.location.href="../grouplist/grouplist.html?d="+(new Date).getTime().toString().substr(-8)}),
                $(".err_refresh").on("click",function(){urlReload()})
        },
        getSize:function(){
            $.fn.fontFlex=function(e,t,o){
                var i=this;
                $(window).resize(function(){
                    var n=window.innerWidth/o;n<e&&(n=e),n>t&&(n=t),isPC()?(n=17,savedBgColor=$("body").css("background-color"),
                        $("body").css("background-color","#e5e5e5")):savedBgColor&&$("body").css("background-color",savedBgColor),i.css("font-size",n+"px")}).trigger("resize")},
                $("html").fontFlex(10,20,50)
        }
        ,setBorderWidth:function(){
                if(/iP(hone|od|ad)/.test(navigator.userAgent)){
                    var e=navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/),t=parseInt(e[1],10);
                    if(t>=8){
                        var o='<link rel="stylesheet" href="Public/_css/border.css"/>';
                        $($("head")[0]).append(o);}}
        },
        loadAjaxData:function(e,t,o,i,n,a,r,s,c,l){
            n=n||"get",l=!l,a=a||"json",allCookies();
                $.ajax({
                    type:n,
                    url:e,
                    data:t,
                    dataType:a,
                    success:function(e){
                        o(e);
                    },error:function(e,t){
                        i(e,t);
                    	// Public.showToast("网络不稳定，请联系管理员味增或重新打开网页！")
                    }})
        }
        ,showToast:function(e,t){
                    t||(t=1e3),null!=toastTime&&(clearTimeout(toastTime),
                        clearTimeout(displayTime)),
                    0==$("#toastId").get().length&&$("body").append('<div id="toastId" class="toasttj" style="z-index:1003;font-size: 1.37em;position: fixed;bottom:25%;width: 100%;opacity:0;height: 24px;display: none;transition: opacity 1s ease-out;"></div>'),
                        $("#toastId").css("display","block"),$("#toastId").css("opacity",1),$("#toastId").html('<div style="color:#fff;background: rgba(0, 0, 0, 0.6);border-radius: 2px;padding: 2px;text-align: center;width:175px;margin: 0 auto;">'+e+"</div>"),toastTime=setTimeout(function(){$("#toastId").css("opacity",0),displayTime=setTimeout(function(){$("#toastId").css("display","none")},1e3)},t)
        },
        showToastByCode:function(e,t){
                            if(t)return Public.showToast(t,2e3),!0;if(codeMap[e]){var o=codeMap[e];return Public.showToast(o,2e3),!0}return Public.showToast("错误码： "+e,2e3),!1}
        ,showErrorPage:function(e){
            $(".main_loading").remove(),600==e?$(".network_error").show():($(".api_err_code").html(e),$(".api_error").show())
        },
        wxlogin:function(m_target){
            Cookies.set("m_target",m_target==null||m_target=="undefined" ?  window.location.href : m_target);
            var e=URL+"index.php?s=Home/Person/globalRoute";
            window.location.href="https://open.weixin.qq.com/connect/oauth2/authorize?appid="+APPID+"&redirect_uri="+
                encodeURIComponent(e)+"&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect"
        },
        isWeiXin:function(){var e=window.navigator.userAgent.toLowerCase();return"micromessenger"==e.match(/MicroMessenger/i)},
        isLogined:function(){
            var e=Cookies.get("m_access_token");
            return !!e
        },
        setTitle:function(e){
            if(Public.isWeiXin()){var t=$("body");document.title=e;var o=$("<iframe style='display:none;' src='/favicon.ico'></iframe>");
                o.on("load",function(){setTimeout(function(){o.off("load").remove()},0)}).appendTo(t)}else document.title=e},
        showJoinHint:function(e,t){
            var o="'"+e+"'",i="";
            i="loginAllow"==e?"<div class='hint-form close_normal'><div class='hint-body'><p class='conform-close' onclick='Public.close()'></p><p class='conform-txt'>圈内成员才能点赞和评论，<br/>加入圈子查看更多精彩内容</p><a onclick=Public.postApplication("+o+","+t+") class='join-aa b-r-4'>加入圈子</a></div></div>":"<div class='hint-form close_normal'><div class='hint-body'><p class='conform-close' onclick='Public.close()'></p><p class='conform-txt'>圈内成员才能点赞和评论，<br/>加入圈子查看更多精彩内容</p><a onclick=Public.postApplication("+o+","+t+") class='join-aa b-r-4'>申请加入圈子</a></div></div>",
            $("body").append(i),$(".code-bg").show()},
        confirmLogin:function(e){
            if("unLoginAllow"==e&&Public.wxlogin(),"loginAllow"==e){
                var t="'"+e+"'";$(".hint-form").remove();
                var o="<div class='hint-form close_update'><p class='conform-close' onclick=Public.close("+t+")></p><p class='conform-txt'>圈内成员才能点赞和评论，<br/>加入圈子查看更多精彩内容</p><p class='next-txt'>已成功加入圈子</p></div>";
                $("body").append(o),$(".code-bg").show()
            }if("loginApply"==e){
                var t="'"+e+"'";
                $(".hint-form").remove();
                var o="<div class='hint-form close_update'><p class='conform-close' onclick=Public.close("+t+")></p><p class='conform-txt'>圈内成员才能点赞和评论，<br/>加入圈子查看更多精彩内容</p><p class='next-txt'>申请已发送，等待管理员审核</p></div>";
                $("body").append(o),$(".code-bg").show()}},
        close:function(e){
            event.cancelBubble=!0,$(".hint-form").css("display","none"),$(".code-bg").hide(),"loginApply"!=e&&"loginAllow"!=e||urlReload()
        },
        goEdit:function() {
            if(Public.isWeiXin()) {
                if(Public.isLogined()) {
                    window.location.href=SRC_URL+"editor";
                }
                else {
                    Public.wxlogin(SRC_URL+"editor");
                }
            }
            else {
                Public.showToast("请在微信端打开该页面！");
            }
        },
        postApplication:function(e,t){
            var o=API_URL+"groups/"+t+"/examinations",i="",n="post",
                a=function(t){t.succeeded?"allowed"==t.resp_data.status?($(".hint-form").remove(),
                    Public.confirmLogin(e)):"examining"==t.resp_data.status&&Public.confirmLogin(e):Public.showToastByCode(t.code)},r=function(){
                        Public.showToastByCode(600)};Public.loadAjaxData(o,i,a,r,n)
        },
        bindTouchHover:function(e,t){
                $("body").on("touchstart",e,function(e){
                    $(this).addClass(t)
                }),$("body").on("touchmove",e,function(e){
                        $(this).hasClass(t)&&$(this).removeClass(t)
                }),$("body").on("touchend",e,function(e){
                        $(this).hasClass(t)&&$(this).removeClass(t)
                }),$("body").on("touchcancel",e,function(e){
                        $(this).hasClass(t)&&$(this).removeClass(t)
                })
            }
};
        $(function(){
            Public.init()
        }),document.onreadystatechange=function(){
                "complete"==document.readyState&&$("body").show()
            };


