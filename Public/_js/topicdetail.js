function handleTime(e,t){var i=function(e,t){var i=e.toString().length;for(t||(t=2);i<t;)e="0"+e,i++;return e},a=new Date(e.substring(0,23)).getTime()-288e5;
    t?a+=t:a-=1;
    var o=new Date(a),n=o.getFullYear()+"-"+i(o.getMonth()+1)+"-"+i(o.getDate())+"T"+i(o.getHours())+":"+i(o.getMinutes())+":"+i(o.getSeconds())+"."+i(o.getMilliseconds(),3)+"+0800";
    return n=encodeURIComponent(n)
}

var templeteComment='<li style="border-top:none">{0}</li>';

var templeteSign='<div class="clear reply-show"><span class="b-r-50 reply-tx fl"><img src="{0}"/></span>\
            <img src=""/></span><div class="reply-txt fl">\
            <p class="name">{1}</p>\
    <p class="txt">{2}</p></div></div><div class="reply-area"><span class="time">{3}</span>\
            <span class="span-rep time js-reply-btns {7}">回复</span></div>\
            <div class="reply-div comm-div hide" data-type="childComment" data-backuser="{4}" data-noteid="{5}" data-notename="{1}" data-comid="{6}">\
            <div class="comm_wrap b-r-2"><span class="input_value">@</span>\
    <textarea class="b-r-2 expand" readOnly=true placeholder="回复{1}:"></textarea>\
            </div><div class="reply-buttons"><a class="reply-cancel b-r-4 js-reply-c" data-type="rep_type">取消</a>\
            <a class="reply-submit b-r-4 js-reply-s">评论</a></div></div>';


var templeteSec='<div class="re-com-div"><div><div class="child-com-hr"></div>\
            <div class="clear reply-show"><div class="reply-com-txt fl">\
            <span class="name">{0}</span><span style="color:#aeaeae">回复</span>\
            <span class="name">{1}</span><span>: </span>\
    <span class="txt">{2}</span></div></div><div class="reply-area">\
            <span class="time">{3}</span><span class="span-rep time js-reply-btns {7}">回复</span></div>\
            <div class="reply-div comm-div hide" data-type="childComment" data-backuser="{4}" data-noteid="{5}" data-notename="{0}" data-comid="{6}">\
            <div class="comm_wrap b-r-2"><span class="input_value">@</span>\
    <textarea class="b-r-2 expand" readOnly=true placeholder="回复{0}:"></textarea>\
            </div><div class="reply-buttons"><a class="reply-cancel b-r-4 js-reply-c" data-type="rep_type">取消</a>\
            <a class="reply-submit b-r-4 js-reply-s">评论</a></div></div></div></div>';

var templeteLike='<li class="b-r-50"><img src="{0}"></li>';


var groupType="",tt=1,group_id="",topic_id=GetQueryString("topic_id"),is_member="",likes=[],likeLoading=!1,group_type="",max_text_length=1e3,m_user_id=Cookies.get("m_user_id"),is_comment=!0,group_name="",qrstatus=!0,group_owner=null,begin_time="",shareContent={title:"",desc:"",link:location.href,imgUrl:""},
    topicDetail={
        init:function(){
            var e=this;Public.bindTouchHover(".group-hre","group_touch_hover"),
                $("body").on("focus",".reply-div textarea",function(){
                    if( !Public.isLogined() ) {
                        if(Public.isWeiXin()) {
                            Public.wxlogin();
                        }
                        else {
                            tt==1 ? (Public.showToast("请使用微信登陆！"),++tt) : "";
                        }
                    }
                    var t=$(this).parents(".reply-div"),i="comm";
                    $(this).css("border","1px solid #16b998");
                    e.decideType(t,i)
                }),
                $("body").on("blur",".reply-div textarea",function(){
                    $(this).css("border","1px solid #dcdcdc")}),
                $(".re-close").on("click",function(){
                    $(".reply-error,.code-bg").hide()}),
                $(".code-bg ,.join-wrap").on("click",function(e){
                    $(".code-bg").hide(),$(".join-wrap .scan-er").html(""),
                        $(".join-wrap").hide(),qrstatus=!0}),
                $("body").delegate(".js-reply-btns","click",function(){
                    var t="rep",i=$(this).parent().next();
                    $(this).parents("ul").find(".reply-div:visible").hide(),e.decideType(i,t)
                }),
                $("body").on("click",".js-reply-c",function(){
                    var t=$(this);
                    e.cancelComment(t);
                }),
                $("body").on("click",".js-reply-s",function(){
                    var t=$(this);
                    e.replyComment(t);
                    e.cancelComment(t.prev());
                }),
                $(document).click(function(e){
                    e=window.event||e;var t=e.srcElement||e.target;
                    $(t).is(".close_normal *")||0!=$(".close_update").length
                        ?0!=$(".close_normal").length||$(t).is(".close_update *")&&!$(t).is(".close_update .next-txt")||Public.close("loginAllow")
                        :Public.close()
                });
            //初始化评论内容，评论条数为30，按照时间先后顺序显示，最早的显示在最前方
            e.getComments(0,30);
            //初始化点赞人员的头像，只显示七个，按照时间先后顺序显示，最新的显示在最前方
            e.getLikesList();
        },
        decideType:function(e,t){
            Public.isLogined() ? "comm"==t?e.find(".reply-buttons").show() : (e.show(),e.find("textarea")[0].focus(),e.find("textarea").removeAttr("readOnly")) : Public.isWeiXin() ? Public.wxlogin() : Public.showToast("请使用微信登陆！");
        },
        getprivewsUrl:function(e,t){
            event.cancelBubble=!0;
            var i=t.split("."),a=i[i.length-1],o=["md","markdown","csv","pps","ppsx","epub","mobi"];
            if(o.indexOf(a)==-1){
                var n=WAPI_URL+"files/"+e+"/preview_url",r=function(e){
                    e.succeeded?window.location.href=e.resp_data.preview_url:Public.showToastByCode(res.code)};
                Public.loadAjaxData(n,!1,r)
            }else
                Public.showToast("暂不支持预览，请在小密圈客户端或网页版预览或下载")
        },
        showDownload:function(e,t){
            8214448122!=e&&($("#banner-group-name").text(t),
            $(".join-banner").show())
        },
        getTopic:function() {
            // var e=this,t=WAPI_URL+"topics/"+topic_id+"/details",i=function(t){
            //     if(t.succeeded){
            //         var i=t.resp_data.topic,a=!!Public.isWeiXin();
            //         if(group_name=i.group.name,is_member=t.resp_data.is_member,groupType=i.group.policies.examine.type,group_owner=i.group.owner,group_type=i.group.policies.examine.type,i.is_member=is_member,i.grouptype=groupType,i.iswx=a,group_id=i.group.group_id,e.showDownload(group_id,group_name),!i.talk&&!i.question){
            //             i.is_banned=!0;
            //             var o=template("topicdetailTmpl",i);
            //             return $("#topic_main").append(o),void $(".main_loading").hide()
            //         }
            //         var n=i.talk?i.talk.text:i.question.text,r=i.talk?i.talk.files:"",l=i.talk?i.talk.images:"";
            //         if(e.showContentInTitle(n,r,l),Public.isWeiXin())
            //             if(Public.isLogined()||"allow"==group_type){
            //             var o=template("topicdetailTmpl",i);
            //                 $("#topic_main").append(o),$(".main_loading").hide(),e.getLikesList(),e.getComments(),e.saveShareContent(i)
            //         }else
            //             Public.wxlogin();
            //         else{
            //             var o=template("topicdetailTmpl",i);
            //             $("#topic_main").append(o),$(".main_loading").hide(),e.getLikesList(),e.getComments(),e.saveShareContent(i)
            //         }"pay"==groupType&&n&&!is_member&&e.initTxt(n,i.type),e.initQrcode("init"),i.answer&&i.answer.voice&&audioInit(i.answer.voice.length),
            //             $("body .js_down_click").on("click",function(){
            //                 var e="../countdownload/down_click.html",t=function(){window.location.href="http://t.72h.io/xmq/"
            //                 },i=function(e){
            //                     Public.showToast("请求失败")
            //                 },a="html";Public.loadAjaxData(e,!1,t,i,"get",a)})
            //     }else if("15502"==t.code||"18101"==t.code){
            //         var o='<div class="topic_undefind"><img src="../../images/undefined.png"></div>';
            //         $("#topic_main").append(o),$(".main_loading").remove()
            //     }else Public.showErrorPage(t.code)},a=function(){
            //         Public.showErrorPage(600)};Public.loadAjaxData(t,!1,i,a)
        },
        getComments:function(start,count) {
            // var noteid=window.location.href.split("noteid=")[1];
            start == undefined ? start=0 : "";
            count == undefined ? count=30 : "";
            var e=this,t=API_URL+"getComments/",i=function(data){
                if(data.retCode=="1") {
                    e.dataHandle(data.data);
                }
                else {
                    Public.showToast(data.info==undefined || data.info=="" ? "其他错误，请联系管理员味增！" : data.info );
                }
            },a=function(e,t){
                Public.showToast("获取评论失败 - "+t)
            },d={ start : start , count : count ,noteid : window.location.href.split("noteid=")[1] },f="post";
            Public.loadAjaxData(t,d,i,a,f);
        },
        dataHandle:function(commentData) {
            var i="",j="",k;
            // console.log(commentData);
            if(commentData.length > 0) {
                for( var index in commentData) {
                    i=templeteSign.format(commentData[index].headimgurl,commentData[index].cname,commentData[index].comment,commentData[index].time,commentData[index].backuser,commentData[index].noteid,commentData[index].comid,commentData[index].visibility);
                    if(commentData[index].replied_comment != undefined && commentData[index].replied_comment.length!=0 ) {
                        k=commentData[index].replied_comment;
                        for (var _index in k) {
                            j+=templeteSec.format(k[_index].cname,k[_index].notename,k[_index].comment,k[_index].time,k[_index].backuser,k[_index].noteid,commentData[index].comid,commentData[index].visibility);
                        }
                    }
                    $("#reply-list-ul").append(templeteComment.format(i+j));
                    j="";
                }
            }
        },
        getMoreComments:function(e){
            // var t=this,i=WAPI_URL+"topics/"+topic_id+"/comments?count=30&sort=asc&begin_time="+begin_time,a=function(i){
            //     if(i.succeeded){
            //         var a=t.dataHandle();if(a.comments=i.resp_data.comments,a.comments&&0==a.comments.length)
            //             e.lock(),e.noData(),e.resetload(),
            //                 $(".dropload-down").hide();
            //         else{var o=a.comments[a.comments.length-1].create_time;begin_time=handleTime(o,1);
            //             var n=template("commentTmpl",a);
            //             $("#reply-list-ul").append(n),e.resetload()}}
            //             else Public.showToastByCode(i.code,i.info),e.resetload()},
            //     o=function(e,t){Public.showToast("获取更多评论失败 - "+t)};
            //     Public.loadAjaxData(i,!1,a,o)
        },
        getMoreRepliedComments:function(e){
            // var t=this,i=$(e),a=i.parent(),o=i.attr("data-status");
            // if("loading"!=o){
            //     i.attr("data-status","loading");
            //     var n=i.attr("data-time"),r=i.attr("data-id");
            //     n=handleTime(n,1);
            //     var l=WAPI_URL+"comments/"+r+"/replied_comments?count=30&begin_time="+n,s=function(e){
            //         if(e.succeeded){
            //             var o=e.resp_data.replied_comments;
            //             if(o&&o.length>0){
            //                 i.attr("data-time",o[o.length-1].create_time);
            //                 var n=t.dataHandle();n.replied_comments=o;var r=template("reCommentTmpl",n);a.prev().append(r)}else a.hide()}else Public.showToastByCode(e.code,e.info);i.attr("data-status","notLoading")},c=function(){i.attr("data-status","notLoading"),i.text("加载失败，请重试")};Public.loadAjaxData(l,!1,s,c)}
        },
        getLikesList:function() {
            //获得成员头像
            var e=this,t=API_URL+"getLikesList/";
            // if("pay"!=groupType||is_member){
            //     var e=WAPI_URL+"topics/"+topic_id+"/likes?count=7",
            //         t=function(e){
            //         e.succeeded
            //             ?(likes=e.resp_data.likes,likes.forEach(function(e,t){t<6&&$(".topic-avatar").find("ul").append('<li class="b-r-50"><img src="'+e.avatar_url+'"/></li>')}),7==likes.length&&$(".topic-avatar").find("ul").append('<li class="dots"><img src="../../images/circle01.png"></li>'))
            //             :Public.showToastByCode(e.code,e.info)
            //     },i=function(){};Public.loadAjaxData(e,!1,t,i)
            // }
        },
        dropLoad:function(){var e=this;$(".reply-list").dropload({scrollArea:window,domDown:{domClass:"dropload-down",domRefresh:"",domLoad:'<div class="dropload-load"><span class="loading"></span>加载中...</div>',domNoData:'<div class="dropload-noData">没有更多了</div>'},loadDownFn:function(t){e.getMoreComments(t),$(".dropload-down").show()},threshold:250}),$(".dropload-down").hide()},
        previewImg:function(e){var t=$(e.target).attr("src"),i=$(e.target).parent(),a=i.attr("class"),o=[];i.parent().find("."+a).each(function(e,t){var i=$(this).children("img").attr("src");o.push(i)}),wx.previewImage({current:t,urls:o})},
        saveShareContent:function(e){var t=this,i=function(e,t,i){var a;a=e?e[0].thumbnail.url:t?t:$(".gr_img").children("img").attr("src"),shareContent.imgUrl=a};if("talk"==e.type){var a=e.talk;a.text?shareContent.title=$(".main-content").text().substring(0,60):a.images?shareContent.title="分享图片":a.files&&(shareContent.title="分享文件"),shareContent.desc=a.owner.name+"的主题",i(a.images,a.owner.avatar_url,e.group.background_url)}else{var o=e.question;shareContent.title=$(".main-content").text().substring(0,60),shareContent.desc=o.questionee.name+"的主题",i(null,o.questionee.avatar_url,e.group.background_url)}shareContent.link=window.location.href,t.wxShareInit()},
        // wxShareInit:function(){var e=WAPI_URL+"js_sdk/config?url="+encodeURIComponent(location.href),t=function(e){if(e.succeeded){var t=["onMenuShareTimeline","onMenuShareAppMessage","previewImage"],i={appId:e.resp_data.appId,timestamp:e.resp_data.timestamp,nonceStr:e.resp_data.nonceStr,signature:e.resp_data.signature,jsApiList:t.slice(0)};wx.config(i),wx.ready(function(){var e=["onMenuShareTimeline","onMenuShareAppMessage"];e.forEach(function(e){wx[e]({title:shareContent.title,desc:shareContent.desc,link:shareContent.link,imgUrl:shareContent.imgUrl})})})}};Public.loadAjaxData(e,!1,t)},
        initTxt:function(e,t){var i=$(".js-topictxt");i.attr("data-type");e.length>=60&&$(".topic_more").show()},
        initQrcode:function(e){
            var t=window.location.href;
            t=t.split("/");
            var i="http://"+t[2]+"/mweb/views/joingroup/join_group.html?group_id="+group_id;
            $(".scan-er").qrcode({width:256,height:256,text:i});
            var a=$(".scan-er canvas");
            if(!is_member){
                var o=a.get(0).toDataURL("image/png");
                a.remove(),qrstatus&&("init"==e?$(".scan-area .scan-er").append("<img src="+o+">"):"confirm"==e&&($(".join-wrap .scan-er").append("<img src="+o+">"),$(".s_title").html("").html(group_name),qrstatus=!1))
            }},
        showContentInTitle:function(e,t,i){var a=function(e){return e=e.replace(/<\/?[^>]*>/g,""),e=e.replace(/[ | ]*\n/g,"\n"),e=e.replace(/&nbsp;/gi,"")},o="";if(e){e=a(e);var n=e.substr(0,60);o+=n}if(""==o){if(t)var r=t[0].name;t&&i?o+="[文件]"+r:t?o+="[文件]"+r:i&&(o+="[图片]"+i.length+"张")}Public.setTitle(o)},
        _replyComment:function(e){
            var t=this,i=e.parents(".reply-div"),a=i.attr("data-type"),o="childComment"==a,n=i.find("textarea").val(),r=n;
            if(n=t.parseHtml(n),void 0!=n&&""!=n){
                if(n.length>max_text_length)
                    return void Public.showToast("请输入小于"+max_text_length+"个字符的内容，已超出了"+(n.length-max_text_length)+"个字符");
                if(n&&is_member&&is_comment){
                    is_comment=!1;
                    var l={req_data:{text:r}};
                    if(o){
                        var s=i.find(".input_value").attr("data-id");
                        l.req_data.replied_comment_id=parseInt(s)
                    }var c=API_URL+"topics/"+topic_id+"/comments",d="post",p=function(e){
                        if(e.succeeded){
                            var a=e.resp_data.comment,n=t.dataHandle();
                            if(o){
                                n.replied_comments=[a];
                                var r=template("reCommentTmpl",n);
                                i.parents("li").find(".re-com-div").append(r)
                            }else{
                                n.comments=[a];var r=template("commentTmpl",n);
                                $(".reply-list").find("ul").prepend(r)
                            }
                            $(".reply-list").find(".reply-div:visible").hide();
                            var l=$(".js_reply_count").html();
                            $(".js_reply_count").html(+l+1),i.find("textarea").val("").height("38"),i.find(".reply-buttons").hide(),Public.showToast("发表成功"),is_comment=!0
                        }else
                            $(".reply-error,.code-bg").show()
                    },u=function(){
                        $(".reply-error,.code-bg").show()
                    };Public.loadAjaxData(c,l,p,u,d)}
            }
        },
        replyComment:function(e) {
            var t=this,i=e.parents(".reply-div"),li=i.parent("li"),ul=$("#reply-list-ul"),a=i.attr("data-type"),o="childComment"==a,n=i.find("textarea").val(),r=n;
            if(n=t.parseHtml(n),void 0!=n&&""!=n){
                if(n.length>max_text_length) {
                    return void Public.showToast("请输入小于"+max_text_length+"个字符的内容，已超出了"+(n.length-max_text_length)+"个字符");
                }
                var l={ commentContent:n ,noteid : i.attr("data-noteid"),backUser : i.attr("data-backuser"),noteName : i.attr("data-notename") ,isSingle : (a=="parentComment") ? 0 : i.attr("data-comid")};
                var c=API_URL+"sendComment/",d="post";
                var p=function(data) {
                    if(data.retCode == "1") {
                        //处理函数逻辑
                        if(a =="childComment") {
                            li.append(templeteSec.format(Cookies.get("cname"), i.attr("data-backuser"),noteName,n,"刚刚","","","","hide" ));
                        }
                        else if(a =="parentComment") {
                            // console.log(ul.html());
                            ul.prepend(templeteComment.format(templeteSign.format(Cookies.get("headimgurl"),Cookies.get("cname"),n,"刚刚","","","","hide" )));
                        }
                        // Public.showToast("测试阶段，没用动态添加，23333");
                        i.find("textarea").val("");
                    }
                    else if(data.retCode == "-1") {
                        Public.wxlogin();
                    }
                    else {
                        Public.showToast(data.info==undefined || data.info=="" ? "其他错误，请联系管理员味增！" : data.info );
                    }
                },u=function() {
                    Public.showToast("评论失败或网络不稳定，请联系管理员味增或重新打开网页！");
                };
                //上传评论并动态加载
                Public.loadAjaxData(c,l,p,u,d);
            }
        },
        cancelComment:function(e){
            e.parents(".reply-div").find("textarea").val(""),
                "rep_type"==e.attr("data-type")?e.parents(".reply-div").hide():e.parents(".reply-buttons").hide()
        },parseHtml:function(e){
            var t=/\<|\>|\"|\'|\&|　| /g;
            return e=e.replace(t,function(e){
                switch(e){
                    case"<":
                        return"&lt;";
                    case">":return"&gt;";
                    case'"':return"&quot;";
                    case"'":return"&#39;";
                    case"&":return"&amp;";
                    case" ":return"&ensp;";
                    case"　":return"&emsp;"
                }
            })},comparebrowse:function(){
            var e=navigator.userAgent.toLowerCase();
            m_user_id?/iphone|ipad|ipod/.test(e)?window.location.href="wxc297e24dc2dfd0da://extract?action=view_topic&topic_uid="+topic_id+"&group_uid="+group_id+"&user_uid="+m_user_id:/android/.test(e)&&(window.location.href="xiaomiquan://action=view_topic&topic_uid="+topic_id+"&group_uid="+group_id+"&user_uid="+m_user_id):Public.wxlogin()},
            groupHref:function(){window.location.href=SRC_URL+"index"},
        // sendLike : function() {
        //
        // },
        isLike:function(e,t){
            event.cancelBubble=!0;
            var i="";
            Public.isWeiXin()
                ?Public.isLogined()
                ?this.getlike(e,t)
                ?(i="loginAllow",Public.showJoinHint(i,group_id))
                :(i="loginApply",Public.showJoinHint(i,group_id))
                :Public.wxlogin()
                :(Public.showToast("请使用微信登陆！"))
        },
        getlike:function(e,t){
            if(!likeLoading){
                likeLoading=!0;
                var i=API_URL+"topics/"+e+"/likes",a={
                    is_like:"topic-like b-r-4 fl like_true"==t.className?"like_true":"like_false"
                };
                type="post";
                var o,n=parseInt($(t).children("span").html());
                if("like_false"==a.is_like){
                    var r=function(e){
                        if(likeLoading=!1,e.succeeded){
                            var t=!1,i=Cookies.get("m_user_avatar_url");
                            if(o="<li class='b-r-50 is_me'><img src="+i+"></li>",likes==[])
                                return $(".topic-avatar ul").append(o),likes;
                            $(".topic-avatar ul").prepend(o),likes.length>=6&&(6==likes.length&&likes.map(function(e,i){e.user_id==Cookies.get("m_user_id")&&(t=!0)}),t?($(".topic-avatar ul").children("li:eq(6)").remove(),t=!1):($(".topic-avatar ul").children("li:eq(6)").remove(),0==$(".dots").length&&(o="<li class='dots'><img src='../../images/circle01.png'></li>",$(".topic-avatar ul").append(o)))),$(".topic-avatar .topic-like span").html(n+1),$(".topic-avatar .topic-like").addClass("like_true").removeClass("like_false")}};
                    Public.loadAjaxData(i,a,r,!1,type)
                }else{
                    var r=function(e){
                        likeLoading=!1;
                        var t=!1;
                        if(e.succeeded){
                            $(".topic-avatar .topic-like").addClass("like_false").removeClass("like_true"),$(".topic-avatar .topic-like span").html(n-1);
                            var i=-1;
                            likes.map(function(e,a){
                                e.user_id==Cookies.get("m_user_id")&&(a<6&&(i=a),7==likes.length&&(t=!0))
                            }),0!=$(".is_me").length?($(".is_me").remove(),6==likes.length&&(o="<li class='b-r-50 is_me'><img src="+likes[5].avatar_url+"></li>",$(".topic-avatar ul").children("li:eq(5)").after(o),$(".topic-avatar ul").children("li.dots").remove()),likes.length>6&&(i!=-1?(o="<li class='b-r-50 is_me'><img src="+likes[6].avatar_url+"></li>",$(".topic-avatar ul").children("li:eq(4)").after(o)):(o="<li class='b-r-50 is_me'><img src="+likes[5].avatar_url+"></li>",$(".topic-avatar ul").children("li:eq(4)").after(o)),t&&($(".topic-avatar ul").children("li.dots").remove(),t=!1))):likes.length<7?$(".topic-avatar ul").children("li:eq("+i+")").remove():7==likes.length?(i!=-1&&($(".topic-avatar ul").children("li:eq("+i+")").remove(),o="<li class='b-r-50'><img src="+likes[6].avatar_url+"></li>",$(".topic-avatar ul").children("li:eq(5)").after(o)),$(".topic-avatar ul").children("li.dots").remove()):i!=-1&&($(".topic-avatar ul").children("li:eq("+i+")").remove(),o="<li class='b-r-50'><img src="+likes[6].avatar_url+"></li>",$(".topic-avatar ul").children("li:eq(5)").after(o))}
                    };Public.loadAjaxData(i,!1,r,!1,"delete")
                }}
        }
        ,getLikesList: function() {
            //获得点赞列表
            var t=$(".topic-avatar").children("ul"),uri=API_URL+"getLikesList/",data={ noteid : window.location.href.split("noteid=")[1] },type="post",fn_success=function(data) {
                var dataArr=data.data;
                for( var index in dataArr) {
                    t.prepend(templeteLike.format(dataArr[index].headimgurl));
                }
            },fn_error=function (e, t) {
                console.log(e,t);
            };
            Public.loadAjaxData(uri,data,fn_success,fn_error,type);
            this.isLiked();
        }
        ,isLiked: function () {
            //判断当前用户是否点赞了
            var uri=API_URL+"isLiked/",data={ noteid : window.location.href.split("noteid=")[1] },type="post",fn_success=function(data) {
                var t=$(".topic-avatar .topic-like");
                data.retCode =="1" ? data.data=="0" ? t.attr("data-isLiked",1) : (t.addClass("like_true").removeClass("like_false"),t.attr("data-isLiked",2)):"" ;
            },fn_error=function (e, t) {
                console.log(e,t);
            };
            Public.loadAjaxData(uri,data,fn_success,fn_error,type);
            //$(".topic-avatar .topic-like").addClass("like_false").removeClass("like_true");
        }
        ,zan : function () {
            var t=$(".topic-avatar .topic-like"),ul=t.next(),s=t.children("span");
            var n=parseInt(s.html());
            if(Public.isWeiXin() &&  t.attr("data-isLiked") != "-1"  ) {
                if(Public.isLogined() ) {
                    if(t.hasClass("like_false")) {
                        t.addClass("like_true").removeClass("like_false");
                        ul.length < 7 ?  ul.prepend(templeteLike.format($(".top-m-img img").attr("src"))) : "";
                        s.html(n+1);
                    }
                    else {
                        t.addClass("like_false").removeClass("like_true");
                        s.html(n-1);
                    }
                    var uri=API_URL+"zan/",data={ noteid : window.location.href.split("noteid=")[1] },type="post",fn_success=function(data) {
                        // console.log(data);
                        if(data.retCode == "1" && data.zan=="cancel") {
                            //遍历当前ul内部的所有li标签，查找是否有当前用户的头像，找到就删除该元素
                            $(".topic-avatar ul li").each(function() {
                                if($(this).children("img").attr("src") == $(".top-m-img img").attr("src")) {
                                    $(this).remove();
                                }
                            })
                        }
                    },fn_error=function (e, t) {
                        console.log(e,t);
                        Public.showToast("其他错误，请联系管理员味增！");
                    };
                    Public.loadAjaxData(uri,data,fn_success,fn_error,type);
                }
                else {
                    Public.wxlogin();
                }
            }
            else {
                Public.showToast("请于微信端登陆后在点赞！");
            }
        }
    };
$(function(){
    topicDetail.init();
    $(".topic-avatar .topic-like").click(function () {
        topicDetail.zan();
    })
});