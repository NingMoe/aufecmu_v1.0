/**
 * Created by WeiZeng on 2016/8/30.
 */
var count=1,info=window.localStorage.getItem("electricInfo"),dormStr='<option value="0" selected="selected">&nbsp;校区</option>\
    <option value="1">东校南区</option>\
    <option value="2">东校北区</option>\
    <option value="3">西校南区</option>\
    <option value="4">西校北区</option>',buildingStr="",floorStr="",roomStr="";




$(function(){
    initBalance();
    $("#dorm").change(function(){
        $("#building").attr("disabled","disabled");
        $("#floor").attr("disabled","");
        $("#room").attr("disabled","");
        ajaxdorm("");
    })

    $("#building").change(function(){
        $("#floor").attr("disabled","disabled");
        $("#room").attr("disabled","");
        ajaxbuilding("","");
    })

    $("#floor").change(function(){
        $("#room").attr("disabled","disabled");
        ajaxfloor("","","");
    })

    $("#bugElectric").click(function(){
        if(judge!=true){
            $.alert("请选择寝室号");
            return ;
        }
        if($("#bugElectric").data("elec")!=1){
            $.alert("当前状态不允许购电！");
            return ;
        }
        var m=($("#moneyInput").val()!="") ? $("#moneyInput").val() : $("#money").val(),info = $("#dorm").val()+","+$("#dorm").find("option:selected").text()+"|"+$("#building").val()+","+$("#building").find("option:selected").text()+"|"+$("#floor").val()+","+$("#floor").find("option:selected").text()+"|"+$("#room").val()+","+$("#room").find("option:selected").text(),dormId=$("#dorm").val(),buildingId=$("#building").val(),floorId=$("#floor").val(),roomId=$("#room").val(),
            dormVal=$("#dorm").find("option:selected").text(),buildingVal=$("#building").find("option:selected").text(),floorVal=$("#floor").find("option:selected").text(),roomVal=$("#room").find("option:selected").text();
        if(dormId == "0" || buildingId == "0" || floorId == "0" || roomId == "0") {
            //说明用户未能正确选择寝室楼，这里将禁止用户下一步
            $.alert("请正确选择寝室楼层！","温馨提示");
            return ;
        }
        else {
            //全部正确，允许用户下一步并将数据记录在本地
            $.showLoading("全力加载中...");
            var electricInfo={
                "dorm" : { "id" : dormId , "val" : dormVal ,"option" : dormStr},
                "building" : { "id" : buildingId , "val" : buildingVal,"option" : buildingStr},
                "floor" : { "id" : floorId , "val" : floorVal,"option" : floorStr},
                "room" : { "id" : roomId , "val" : roomVal,"option" : roomStr},
            },data={roomId: roomId,dormId: dormId,dormName: dormVal,buildName: buildingVal,floorName:  floorVal,roomName: roomVal,choosePayType: $("#choosePayType").val(),money: m,electricinfo: info}, url=yktAPI+"ajaxFunction&fnOption=electric",
                fn_success=function (data) {
                    $.hideLoading();
                    if(data.status==1){
                        location.href=data.url;
                        // $.alert("购电成功，测试阶、段不跳转页面!");
                    }
                    else{
                        $.alert("购电失败!");
                    }
                },fn_error=function () {
                    $.hideLoading();
                    $.alert("请尝试联系管理员或刷新页面重试！","温馨提示");
                },type="post";
            Public.loadAjaxData(url,data,fn_success,fn_error,type);
            window.localStorage.setItem("electricInfo",JSON.stringify(electricInfo));
        }
    })

    $("#campusBuy").click(function(){
        var status=$("#bankMoney").data("status");
        if(status==1 || status==2 || status==3 || status==4){
            $("#choosePayType").val("1");
            $("#x2").css("display","none");
            $("#x1").css("display","");
        }
        else{
            if(count==1){
                if($("#bugElectric").data("elec")==0){
                    $.alert($("#elec").html());
                }
                else{
                    $.alert("校园网络不稳定，请稍后再试");
                }
                count++;
            }
            else{
                count--;
            }
        }
    })

    $("#bankBuy").click(function(){
        var status=$("#bankMoney").data("status");
        if(status==1){
            $("#x1").css("display","none");
            $("#x2").css("display","");
            $("#choosePayType").val("2");
        }
        else if(status==2){
            if(count==1){
                if($("#bugElectric").data("elec")==0){
                    $.alert($("#elec").html());
                }
                else{
                    $.alert("每晚22:00至第二天早6:00为银行结账时间，银行功能不可用");
                }
                count++;
            }
            else{
                count--;
            }
        }
        else{
            if(count==1){
                if($("#bugElectric").data("elec")==0){
                    $.alert($("#elec").html());
                }
                else{
                    $.alert("银行服务暂不可用");
                }
                count++;
            }
            else{
                count--;
            }
        }
    })

})

function ajaxdorm(str){
    $.ajax({
        url: yktAPI+"ajaxFunction&fnOption=electricRoom",
        type:"post",
        data:{dormId: $("#dorm").val(),dormName: $("#dorm").find("option:selected").text(),buildingId: "",floorId: ""},
        dataType:"json",
        success: function(data){
            //alert(data.electric);
            if(data.status==1){
                $("#building").attr("disabled",false);
                var electric=data.electric;
                var it="";
                if(str==""){
                    var option='<option value="0" selected="selected">&nbsp;寝室楼</option>';
                }
                else{
                    var option='';
                }
                if($("#dorm").val()!=0){
                    if($("#dorm").val()==2){
                        electricArr=electric.split("|");
                        var store=new Array();
                        var index;
                        for(var i=0;i<electricArr.length;++i){
                            it=electricArr[i].split(",");
                            index=it[1].split("号")[0];
                            store[index]=new Array();
                            store[index][0]=it[0];
                            store[index][1]=it[1];
                        }
                        for(var i=1;i<electricArr.length+1;++i){
                            option=option+'<option value="'+store[i][0]+'">'+store[i][1]+'</option>';
                        }
                    }
                    else{
                        electricArr=electric.split("|");
                        for(var i=0;i<electricArr.length;++i){
                            //$("#dorm").val()
                            it=electricArr[i].split(",");
                            option=option+'<option value="'+it[0]+'">'+it[1]+'</option>';
                        }
                    }
                    buildingStr=option;
                    $("#building").html(option);
                    if(str!=""){
                        $("#building option[value="+str+"] ").attr("selected","selected");
                    }
                }
            }
            else{
                $.alert((data.info == undefined || data.info=="" || data.info==null) ? "内部错误" : data.info,"温馨提示");
            }
        },
        error: function(){
            $.alert("校园网络不稳定，请稍后再试！");
        }
    })
}

function ajaxbuilding(str,valbuilding){
    var buildingval;
    if(str!=""){
        buildingval=valbuilding;
    }
    else{
        buildingval=$("#building").val();
    }
    if(buildingval!=0){
        $.ajax({
            url: yktAPI+"ajaxFunction&fnOption=electricRoom",
            type:"post",
            data:{dormId: $("#dorm").val(),dormName: $("#dorm").find("option:selected").text(),buildingId: buildingval,floorId: ""},
            dataType:"json",
            success: function(data){
                if(data.status==1){
                    $("#floor").attr("disabled",false);
                    var electric=data.electric;
                    var it="";
                    if(str==""){
                        var option='<option value="0" selected="selected">&nbsp;楼层</option>';
                    }
                    else{
                        var option='';
                    }
                    electricArr=electric.split("|");
                    for(var i=0;i<electricArr.length;++i){
                        it=electricArr[i].split(",");
                        option=option+'<option value="'+it[0]+'">'+it[1]+'</option>';
                    }
                    floorStr=option;
                    $("#floor").html(option);
                    if(str!=""){
                        $("#floor option[value="+str+"] ").attr("selected","selected");
                    }
                }
                else{
                    $.alert((data.info == undefined || data.info=="" || data.info==null) ? "内部错误" : data.info,"温馨提示");
                }
            },
            error: function(){
                $.alert("校园网络不稳定，请稍后再试！");
            }
        })
    }
}

function ajaxfloor(str,valbuilding,valfloor){
    var buildingval;
    var floorval;
    if(str!=""){
        buildingval=valbuilding;
        floorval=valfloor;
    }
    else{
        buildingval=$("#building").val();
        floorval=$("#floor").val();
    }
    if(floorval!=0){
        $.ajax({
            url: yktAPI+"ajaxFunction&fnOption=electricRoom",
            type:"post",
            data:{dormId: $("#dorm").val(),dormName: $("#dorm").find("option:selected").text(),buildingId: buildingval,floorId: floorval},
            dataType:"json",
            success: function(data){
                if(data.status==1){
                    $("#room").attr("disabled",false);
                    var electric=data.electric;
                    var it="";
                    if(str==""){
                        var option='<option value="0" selected="selected">&nbsp;房间</option>';
                    }
                    else{
                        var option='';
                    }
                    //alert(electric);
                    electricArr=electric.split("|");
                    for(var i=0;i<electricArr.length;++i){
                        it=electricArr[i].split(",");
                        option=option+'<option value="'+it[0]+'">'+it[1]+'</option>';
                    }
                    roomStr=option;
                    $("#room").html(option);
                    if(str!=""){
                        $("#room option[value="+str+"] ").attr("selected","selected");
                    }
                    judge=true;
                }
                else{
                    $.alert((data.info == undefined || data.info=="" || data.info==null) ? "内部错误" : data.info,"温馨提示");
                }
            },
            error: function(){
                $.alert("校园网络不稳定，请稍后再试！");
            }
        })
    }
}

function initBalance(){
    initOption();
    $.ajax({
        url: yktAPI+"getMoneyBalance",
        type:"get",
        dataType:"JSON",
        success:function(data){
            if(data.status==1){
                $("#bank").html(data.bank+"元");
                $("#campus").html(data.campus+"元");
                $("#bankMoney").val(data.bank);
                $("#campusMoney").val(data.campus);
                $.post(yktAPI+"moneyTogether",{campusMoney : data.campus});
                $("#bankMoney").data("status",data.status);
                // $("#bugElectric").data("elec","1");
                // $("#bugElectric").attr("class","weui_btn weui_btn_primary");
                // $("#bugElectric").html("确认购电");
            }
            else if(data.campus != null && data.campus != "" && data.campus != undefined){
                $("#campus").html(data.campus+"元");
                $("#bankMoney").val(-1);
                $("#campusMoney").val(data.campus);
                $("#bank").html("非服务时间");
                $("#bankMoney").data("status",2);
                // $("#bugElectric").data("elec","1");
                // $("#bugElectric").attr("class","weui_btn weui_btn_primary");
                // $("#bugElectric").html("确认购电");
            }
            else if(data.status==5) {
                Public.isWeiXin() ? Public.wxLogin("electric") : $.alert("请于微信端登录系统！","温馨提示")
            }
            else{
                $("#bugElectric").data("elec","0");
                $("#bankMoney").val(-1);
                $("#campusMoney").val(data.campus);
                $("#campus").html("请重新登陆");
                $("#bank").html("银行服务暂不可用");
                $("#bugElectric").html("请重新登陆");
            }
        }
    })
}


function initOption() {
    if(info!=undefined && info!="") {
        info=JSON.parse(info);
        $("#building").attr("disabled",false);
        $("#floor").attr("disabled",false);
        $("#room").attr("disabled",false);
        for(var index in info ) {
            window[index+'Str']=info[index].option;
            $("#"+index).html(info[index].option);
            $("#"+index+" option[value="+info[index].id+"] ").attr("selected","selected");
        }
        judge=true;
    }
}

function Hide(){
    document.getElementById("text0").placeholder="";
}
function Show(){
    document.getElementById("text0").placeholder="输入整数";
}
function sel1() {
    document.getElementById('radio1').setAttribute("class","radio-checked")
    document.getElementById('radio2').setAttribute("class","radio-uncheck")
    document.getElementById('radio3').setAttribute("class","radio-uncheck")
    $("#money").val("10");
    $("#moneyInput").val("");
}
function sel2() {
    document.getElementById('radio1').setAttribute("class","radio-uncheck")
    document.getElementById('radio2').setAttribute("class","radio-checked")
    document.getElementById('radio3').setAttribute("class","radio-uncheck")
    $("#money").val("30");
    $("#moneyInput").val("");
}
function sel3() {
    document.getElementById('radio1').setAttribute("class","radio-uncheck")
    document.getElementById('radio2').setAttribute("class","radio-uncheck")
    document.getElementById('radio3').setAttribute("class","radio-checked")
    $("#money").val("50");
    $("#moneyInput").val("");
}
function sel4() {
    document.getElementById('radio1').setAttribute("class","radio-uncheck")
    document.getElementById('radio2').setAttribute("class","radio-uncheck")
    document.getElementById('radio3').setAttribute("class","radio-uncheck")
    document.getElementById('moneyInput').setAttribute("placeholder","")
}
function g() {
    document.getElementById('moneyInput').setAttribute("placeholder","其他金额")
}