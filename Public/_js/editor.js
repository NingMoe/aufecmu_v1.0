var group_id=GetQueryString("group_id"),tipsShow=!0,isCanPub=!0,
    editor={
        init:function(){
        var i=this;
        // $(".img_m").on("click",function(){
        //     // console.log("img_m");
        //     alert("test");
        // });
    // $(".btn-add").on("click",function(i){var t=$(".addimg_btn");t&&addstatus?t.click():addstatus||Public.showToast("最多可以上传九张照片")});
    var i=this;
        if(isCanPub){
                isCanPub=!1;
                var c=function(i){
                        if(i.succeeded){
                                var o=i.resp_data.topic;
                                $(".txt_form").val("");
                                $(".img_main").html("");
                                window.location.href="../topic/topic.html?group_id="+o.group.group_id
                        }
                        else
                                t.showTips("主题发送失败","fail"),isCanPub=!0
                },n=function(){
                        isCanPub=!0
                };
                // Public.loadAjaxData(o,s,c,n,a)
        }},
        showTips:function(i,t){
            var o='<div class="tip_main"><div class="cre_tips"><p class="tip-txt">'+i+'</p><div class="tips_btn">';o+="drop"==t?'<a class="tip_s">取消</a><a class="tip_c">退出</a>':'<a class="tip_k">知道了</a>',o+='</div></div><div class="code-bg" style="display:block"></div></div>',$("body").append(o),tipsShow=!1}};
        $(function(){
            editor.init();

            $(".nt_submit").on("click",function(){
                var textContent=$(".txt_form").val();
                Public.isLogined() ? textContent != "" ? sendNote(textContent) : Public.showToast("发表主题不能为空") : Public.wxlogin();
            });

            $(".nt_cancel").on("click",function() {
                window.location.href=SRC_URL+"index";
            });
        });

        function sendNote(content) {
            if(void 0!=content&&""!=content) {
                var imgObject={},count=0,n="";
                $(".include").each(function() {
                    n = $(this).children("img").attr("src");
                    if( n != "" && n != undefined ) {
                        //需要判断是否上传成功
                        // imgObject[count++]=$(this).prop("className").split(" ")[1];
                        imgObject[count++]=n;
                    }
                })
                console.log(count);
                var uri=API_URL+"sendNote",data={content : content ,jsonImg : (count==0) ? "" : encodeURI(JSON.stringify(imgObject)) },fn_success=function(data) {
                    if(data.retCode == "1") {
                        window.location.href=SRC_URL+"index";
                    }
                    else if(data.retCode == "-1") {
                        Public.isWeiXin() ? Public.wxlogin() : Public.showToast("请在微信端登陆哦!");
                    }
                    else {
                        Public.showToast(data.info);
                    }
                },fn_error=function(e,i) {
                    Public.showToast("未知错误，请联系管理员味增！");
                },type="post";
                // console.log(data);
                Public.loadAjaxData(uri,data,fn_success,fn_error,type);
            }
            else {
                Public.showToast("亲，内容不能为空哦！");
            }
        }
        

