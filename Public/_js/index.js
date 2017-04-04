var templete = '<li onclick="redirectTo(\'{0}\');"><div class="topic-main"><div class="topic-li"><p class="fl top-m-img">\
            <img src="{1}"></p>\
            <div class="topic-p"><p class="f-3-16">{2}</p>\
            <p class="f-g-11 pt4">{3}</p></div><em class="theme-narrow"></em></div>\
            <div class="f-3-16 topic-m-div">{4}<span class="dot"></span></div>{7}\
            <div class="pay-list" style="padding-left: 0 !important;">\
            <span class="pay-like">{5}</span>\
            <span class="pay-reply">{6}</span>\
            </div></div></li>',globalStart=0,loading = false;

var templeteUl = '<div class="topic-m-img clearfix"><ul class="img_list">{0}</ul></div>';
var templeteLi = '<li style="width: {0}px;height: {1}px"><img src="{2}" style="margin-top: 0px;"></li>';

function redirectTo(noteid) {
    location.href=SRC_URL+"detail"+"&noteid="+noteid;
}

function getObjCount(obj) {
    var objLen = 0;
    for(var i in obj){
        objLen++;
    }
    return objLen;
}

init();

function init() {
    getData(globalStart,8);
}

function getData(start,count) {
    start == undefined ? start=0 : "";
    count == undefined ? count=8 : "";
    var item,data={start : start,count : count},fn_success=function(data){
        var allDate=data.data,_imgInfo="",obj,width=90;
        // console.log(allDate);
        if(data.retCode == "1") {
            for (var index in allDate) {
                obj=JSON.parse(allDate[index].imgInfo);
                if(obj != null) {
                    // console.log(allDate[index]);
                    width=(getObjCount(obj)>=2) ? 90 : 150;
                    for(var j in obj) {
                        _imgInfo+=templeteLi.format(width,width,obj[j]);
                    }
                    _imgInfo=templeteUl.format(_imgInfo);
                }
                item=templete.format(allDate[index].noteid,allDate[index].headimgurl,allDate[index].cname,allDate[index].time,allDate[index].content,allDate[index].zan,allDate[index].comcount,_imgInfo);
                _imgInfo="";
                $("#topics").append(item);
                globalStart++;
            }
            loading = false;
            $(".dropload-down").html('<div class="dropload-refresh">↑上拉加载更多</div>');
        }
        else if(data.retCode == "-2") {
            $(".dropload-down").html('<div class="dropload-refresh">到底啦!</div>');
        }
        else {
            Public.showToast(data.info);
        }
    },fn_error=function(e,t){
        Public.showToast("未知错误，请联系管理员味增！");
    };
    Public.loadAjaxData(API_URL+"getData",data,fn_success,fn_error,"post");
}

// $(".create_a").on("click",function() {
//     console.log(Public.isLogined());
//     if(Public.isLogined()) {
//         window.location.href=SRC_URL+"editor";
//     }
//     else {
//         Public.wxlogin(SRC_URL+"editor");
//     }
// })




$(window).scroll(function(){
    if((($(window).scrollTop()+$(window).height())+50)>=$(document).height()){
        if(loading == false){
            loading = true;
            $(".dropload-down").html('<div class="dropload-load"><span class="loading"></span>加载中...</div>');
            getData(globalStart,8);
        }
    }
});