function initUpload(){
    
    return ;
    // console.log("test upup");
    // var a=$(".img_main").find(".include").length;a>=9&&(addstatus=!1),a<=0||a>=9
    //     ?$(".btn-add").hide()
    //     :($(".btn-add").show(),addstatus=!0),
    //     $(".uploadbtn,.addimg_btn").remove(),
    //     $(".input-con").append('<input type="file" class="uploadbtn" accept="image/jpeg,image/jpge,image/png" />'),
    //     $(".img_add_wr").append('<input class="img_add hide addimg_btn" accept="image/jpeg,image/jpge,image/png"  type="file">')
}
function postImg(a,i){
    var t=API_URL+"images",
        e={req_data:{type:"photo",hash:a}},
        n="post",d=function(a){
            if(a.succeeded){
                var t="",e=a.resp_data,n=e.image_id;
                e.upload_url?
                    (t=e.upload_url,postUploadurl(t,i,n,imgName)):
                    previewPic(i,n,imgName),imgarr.push(n)
            }
        };
        // console.log("test");
        // Public.loadAjaxData(t,e,d,null,n)  
}
function previewPic(a,i,t){
    var e=new FileReader;
    e.onload=function(a){
        compressImg(a.target.result,i,t)},e.readAsDataURL(a)
}
function postUploadurl(a,i,t,e){
    var a=a,n=i,d="post",o=!1,r=function(a){
        a.succeeded?
            previewPic(i,t,e):
            Public.showToastByCode(a.code)},
        g=new FormData;
    g.append("file",n)
        // Public.loadAjaxData(a,g,r,!1,d,!1,!0,o,!1,"status")
}
function templateImg(a,i){
    imgName="file"+ a==undefined ? (new Date).getTime() : a;
    var t=$(".img_ul"),e='<div class="include '+imgName+'"><div class="in_bg"></div><img style="max-width: 204px;"><span class="hide in_close"></span></div>';
    t.append(e);var n=$(".include");
    setstyles(n)
}
function clearImgarr(a){
    for(var i=0;i<imgarr.length;i++)
        imgarr[i]==+a&&imgarr.splice(i,i+1)
}
function setstyles(a,i){
    $(a).css({width:conwidth+"px",height:conwidth+"px"}),
    $(a).find("img").css({"max-width":conwidth+"px","max-height":conwidth+"px","width":conwidth+"px"}),
    $(".btn-add").css({width:conwidth+"px",height:conwidth+"px","background-size":conwidth+"px "+conwidth+"px"})
}
function compressImg(a,i,t){
    if(a){
        var e=document.createElement("canvas");
        maxHeight=maxHeight>300?maxHeight:300,e.width=maxHeight,e.height=maxHeight;
        var n=new Image;
        n.src=a,n.onload=function(){
            var a,d=n.height,o=n.width,r=0,g=0;d>o?(a=o,r=(d-a)/2):(a=d,g=(o-a)/2);var s=e.getContext("2d");s.fillStyle="#fff",s.fillRect(0,0,e.width,e.height),s.drawImage(n,g,r,a,a,0,0,maxHeight,maxHeight);var p=$("."+t),c=p.find("img");c.attr("src",e.toDataURL("image/jpeg")),p.find(".in_close").attr("data-id",i).show(),$(".in_bg").hide()}}
}var imgarr=[],addstatus=!0,ww=$(".wrap").width(),conwidth=Math.floor((ww-60)/3),maxHeight=conwidth,imgName="",typearr=["jpg","jpeg","png"];$("body").on("change",".uploadbtn,.addimg_btn",function(){if(file=this.files[0]){var a=file.type.toLowerCase(),i="1234567890123456789012345678901234567890123456789012345678901234";a=a.substr(6,a.length);for(var t=!1,e=0;e<typearr.length;e++)typearr[e]==a&&(t=!0);if(!t)return void Public.showToast("ä¸æ”¯æŒè¯¥æ–‡ä»¶ç±»åž‹");templateImg(file),postImg(i,file)}}),
    $("body").on("tap",".in_close",function(){
        (--imgCount <= 8) ? $("#btn-add2").removeClass("hide") : $("#btn-add2").addClass("hide");
        $(this).parents(".include").remove();
        if($(".include").length == 0) {
            $("#btn-add2").addClass("hide");
        }
    }),imgCount=0,__count__=1,uploader = Qiniu.uploader({
            runtimes: 'html5,flash,html4',      // 上传模式，依次退化
            browse_button: 'img_m',         // 上传选择的点选按钮，必需
    //         uptoken : '<Your upload token>', // uptoken是上传凭证，由其他程序生成
            uptoken_url: API_URL+"getToken/",         // Ajax请求uptoken的Url，强烈建议设置（服务端提供）
    //         uptoken_func: function(date){    // 在需要获取uptoken时，该方法会被调用
    //             console.log("test")
    //             console.log(date);
    //             return date.token;
    //         },
            get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的 uptoken
            // downtoken_url: '/downtoken',
            // Ajax请求downToken的Url，私有空间时使用，JS-SDK将向该地址 POST 文件的 key 和 domain，服务端返回的JSON必须包含url字段，url值为该文件的下载地址
            unique_names: true,              // 默认false，key为文件名。若开启该选项，JS-SDK会为每个文件自动生成key（文件名）
            // save_key: true,                  // 默认false。若在服务端生成uptoken的上传策略中指定了sava_key，则开启，SDK在前端将不对key进行任何处理
            domain: 'file.woai662.com',     // bucket 域名，下载资源时用到，必需
            container: 'img_button',             // 上传区域DOM ID，默认是browser_button的父元素
            max_file_size: '10mb',             // 最大文件体积限制
            flash_swf_url: 'path/of/plupload/Moxie.swf',  //引入flash，相对路径
            max_retries: 1,                     // 上传失败最大重试次数
            dragdrop: false,                     // 开启可拖曳上传
            drop_element: '',          // 拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
            chunk_size: '4mb',                  // 分块上传时，每块的体积
            auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传
            //x_vars : {
            //    查看自定义变量
            //    'time' : function(up,file) {
            //        var time = (new Date()).getTime();
            // do something with 'time'
            //        return time;
            //    },
            //    'size' : function(up,file) {
            //        var size = file.size;
            // do something with 'size'
            //        return size;
            //    }
            //},
            init: {
                'FilesAdded': function(up, files) {
                    if(imgCount >= 8) {
                        $("#btn-add2").addClass("hide");
                        if(imgCount > 8) return false; else imgCount++;
                    }
                    else imgCount++;
                    plupload.each(files, function(file) {
                        // console.log(up);
                        // console.log(file.type);
                        if(file.type != "image/png" && file.type != "image/jpge" && file.type != "image/jpeg") {
                            Public.showToast("只能上传png、jpeg格式的图片");
                            return false;
                        }
                        else{
                            templateImg(file.id);
                        }
                        // console.log((file.size/1048576).toFixed(2));
                    });
                    Public.isLogined() ? $("#btn-add2").removeClass("hide") : Public.wxlogin();
                },
                'BeforeUpload': function(up, file) {
                    // 每个文件上传前，处理相关的事情
                    if(file.type != "image/png" && file.type != "image/jpge" && file.type != "image/jpeg") {
                        Public.showToast("只能上传png、jpeg格式的图片");
                        return false;
                    }
                    if(__count__ == 1) {
                        uploader2 = Qiniu.uploader({
                            runtimes: 'html5,flash,html4',      // 上传模式，依次退化
                            browse_button: 'btn-add2',         // 上传选择的点选按钮，必需
                    //         uptoken : '<Your upload token>', // uptoken是上传凭证，由其他程序生成
                            uptoken_url: API_URL+"getToken&type=1",         // Ajax请求uptoken的Url，强烈建议设置（服务端提供）
                    //         uptoken_func: function(date){    // 在需要获取uptoken时，该方法会被调用
                    //             console.log("test")
                    //             console.log(date);
                    //             return date.token;
                    //         },
                            get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的 uptoken
                            // downtoken_url: '/downtoken',
                            // Ajax请求downToken的Url，私有空间时使用，JS-SDK将向该地址 POST 文件的 key 和 domain，服务端返回的JSON必须包含url字段，url值为该文件的下载地址
                            unique_names: true,              // 默认false，key为文件名。若开启该选项，JS-SDK会为每个文件自动生成key（文件名）
                            // save_key: true,                  // 默认false。若在服务端生成uptoken的上传策略中指定了sava_key，则开启，SDK在前端将不对key进行任何处理
                            domain: 'file.woai662.com',     // bucket 域名，下载资源时用到，必需
                            container: 'add_more',             // 上传区域DOM ID，默认是browser_button的父元素
                            max_file_size: '10mb',             // 最大文件体积限制
                            flash_swf_url: 'path/of/plupload/Moxie.swf',  //引入flash，相对路径
                            max_retries: 1,                     // 上传失败最大重试次数
                            dragdrop: false,                     // 开启可拖曳上传
                            drop_element: '',          // 拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
                            chunk_size: '4mb',                  // 分块上传时，每块的体积
                            auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传
                            //x_vars : {
                            //    查看自定义变量
                            //    'time' : function(up,file) {
                            //        var time = (new Date()).getTime();
                            // do something with 'time'
                            //        return time;
                            //    },
                            //    'size' : function(up,file) {
                            //        var size = file.size;
                            // do something with 'size'
                            //        return size;
                            //    }
                            //},
                            init: {
                                'FilesAdded': function(up, files) {
                                    if(imgCount >= 8) {
                                        $("#btn-add2").addClass("hide");
                                        if(imgCount > 8) return false; else imgCount++;
                                    }
                                    else imgCount++;
                                    plupload.each(files, function(file) {
                                        // console.log(up);
                                        // console.log(file);
                                        if(file.type != "image/png" && file.type != "image/jpge" && file.type != "image/jpeg") {
                                            Public.showToast("只能上传png、jpeg格式的图片");
                                            return false;
                                        }
                                        else{
                                            templateImg(file.id);
                                        }
                                        // console.log((file.size/1048576).toFixed(2));
                                    });
                                },
                                'BeforeUpload': function(up, file) {
                                    // 每个文件上传前，处理相关的事情
                                    if(file.type != "image/png" && file.type != "image/jpge" && file.type != "image/jpeg") {
                                        Public.showToast("只能上传png、jpeg格式的图片");
                                        return false;
                                    }
                                    // console.log(up);
                                    // console.log(file);
                                },
                                'UploadProgress': function(up, file) {
                                    // 每个文件上传时，处理相关的事情
                                    // console.log("UploadProgress，文件类型："+file.type);
                                    if(file.type != "image/png" && file.type != "image/jpge" && file.type != "image/jpeg") {
                                        Public.showToast("只能上传png、jpeg格式的图片");
                                        return false;
                                    }
                                },
                                'FileUploaded': function(up, file, info) {
                                   // console.log("每个文件上传成功后，处理相关的事情")
                                   // console.log(up);
                                   // console.log(file);
                                   // console.log(info);
                                    // 每个文件上传成功后，处理相关的事情
                                    // 其中info是文件上传成功后，服务端返回的json，形式如：
                                    // {
                                    //    "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
                                    //    "key": "gogopher.jpg"
                                    //  }
                                    // 查看简单反馈
                                    if(file.type == "image/png" || file.type == "image/jpge" || file.type == "image/jpeg") {
                                        var res = JSON.parse(info);
                                        var sourceLink = 'http://'+up.getOption('domain')+'/' + res.key; //获取上传成功后的文件的Url
                                        // console.log(sourceLink);
                                        $(".include").each(function() {
                                            if($(this).hasClass(file.id)) {
                                                $(this).children("img").attr("src",sourceLink);
                                                $(this).children(".in_bg").removeClass("in_bg");
                                            }
                                        })                                    
                                    }
                                    else {
                                        Public.showToast("只能上传png、jpeg格式的图片");
                                        return false;
                                    }
                                    // $("#loading").css("display","none");
                                },
                                'Error': function(up, err, errTip) {
                                    //上传出错时，处理相关的事情
                                    // $("#loading").css("display","none");
                                    // console.log(up);
                                    // console.log(err);
                                    // console.log(errTip);
                                    Public.showToast(errTip);
                                    // if(err.file.size > 10485760) {
                                    //     alert("音乐大小不能超过10M！");
                                    // }
                                    // else{
                                    //     alert(errTip);
                                    // }

                                },
                                'UploadComplete': function() {
                                    // $("#loading").css("display","none");
                                    // 队列文件处理完毕后，处理相关的事情
                                },
                                'Key': function(up, file) {
                                    // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
                                    // 该配置必须要在 unique_names: false，save_key: false时才生效
                                    var key = "";
                                    // do something with key here
                                    return key
                                }
                            }
                        });
                        __count__++;
                    }
                    // console.log(up);
                    // console.log(file);
                },
                'UploadProgress': function(up, file) {
                    // 每个文件上传时，处理相关的事情
                    // console.log("UploadProgress，文件类型："+file.type);
                    if(file.type != "image/png" && file.type != "image/jpge" && file.type != "image/jpeg") {
                        Public.showToast("只能上传png、jpeg格式的图片");
                        return false;
                    }
                },
                'FileUploaded': function(up, file, info) {
                   // console.log("每个文件上传成功后，处理相关的事情")
                   // console.log(up);
                   // console.log(file);
                   // console.log(info);
                    // 每个文件上传成功后，处理相关的事情
                    // 其中info是文件上传成功后，服务端返回的json，形式如：
                    // {
                    //    "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
                    //    "key": "gogopher.jpg"
                    //  }
                    // 查看简单反馈
                    if(file.type == "image/png" || file.type == "image/jpge" || file.type == "image/jpeg") {
                        var res = JSON.parse(info);
                        var sourceLink = 'http://'+up.getOption('domain')+'/' + res.key; //获取上传成功后的文件的Url
                        // console.log(sourceLink);
                        $(".include").each(function() {
                            if($(this).hasClass(file.id)) {
                                $(this).children("img").attr("src",sourceLink);
                                $(this).children(".in_bg").removeClass("in_bg");
                            }
                        })
                    }
                    else {
                        Public.showToast("只能上传png、jpeg格式的图片");
                        return false;
                    }
                    // $("#loading").css("display","none");
                },
                'Error': function(up, err, errTip) {
                    //上传出错时，处理相关的事情
                    // $("#loading").css("display","none");
                    // console.log(up);
                    // console.log(err);
                    // console.log(errTip);
                    Public.showToast(errTip);
                    // if(err.file.size > 10485760) {
                    //     alert("音乐大小不能超过10M！");
                    // }
                    // else{
                    //     alert(errTip);
                    // }

                },
                'UploadComplete': function() {
                    // $("#loading").css("display","none");
                    // 队列文件处理完毕后，处理相关的事情

                },
                'Key': function(up, file) {
                    // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
                    // 该配置必须要在 unique_names: false，save_key: false时才生效
                    var key = "";
                    // do something with key here
                    return key
                }
            }
        });

    