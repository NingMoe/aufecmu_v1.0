/**
 * Created by WeiZeng on 2016/8/31.
 */
var count=1;
function net1(){
    var status=$("#bankMoney").data("status");
    if(status==1 || status==2 || status==3 || status==4){
        $("#choosePayType").val("1");
        $("#x2").css("display","none");
        $("#x1").css("display","");
    }
    else{
        if(count==1){
            if($("#ok").data("net")==0){
                $.alert($("#ok").html());
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
}

function net2(){
    var status=$("#bankMoney").data("status");
    if(status==1){
        $("#x1").css("display","none");
        $("#x2").css("display","");
        $("#choosePayType").val("2");
    }
    else if(status==2){
        if(count==1){
            if($("#ok").data("net")==0){
                $.alert($("#ok").html());
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
            if($("#ok").data("net")==0){
                $.alert($("#ok").html());
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


}

function sel1() {
    document.getElementById('radio1').setAttribute("class","radio-checked")
    document.getElementById('radio2').setAttribute("class","radio-uncheck")
    document.getElementById('radio3').setAttribute("class","radio-uncheck")
    $("#money").val("5");
    $("#inputText").val("");
}
function sel2() {
    document.getElementById('radio1').setAttribute("class","radio-uncheck")
    document.getElementById('radio2').setAttribute("class","radio-checked")
    document.getElementById('radio3').setAttribute("class","radio-uncheck")
    $("#money").val("10");
    $("#inputText").val("");
}
function sel3() {
    document.getElementById('radio1').setAttribute("class","radio-uncheck")
    document.getElementById('radio2').setAttribute("class","radio-uncheck")
    document.getElementById('radio3').setAttribute("class","radio-checked")
    $("#money").val("20");
    $("#inputText").val("");
}
function sel4() {
    document.getElementById('radio1').setAttribute("class","radio-uncheck")
    document.getElementById('radio2').setAttribute("class","radio-uncheck")
    document.getElementById('radio3').setAttribute("class","radio-uncheck")
    document.getElementById('inputText').setAttribute("placeholder","")
}
function g() {
    document.getElementById('inputText').setAttribute("placeholder","其他金额")
}

function initBalance(){
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
                $("#ok").data("net","1");
                $("#ok").attr("class","weui_btn weui_btn_primary");
                if($("#netBalance").data("netBalance")!=1){
                    $("#ok").html("确认缴费");
                }
            }
            else if(data.campus != null && data.campus != "" && data.campus != undefined){
                $("#campus").html(data.campus+"元");
                $("#bankMoney").val(-1);
                $("#campusMoney").val(data.campus);
                $("#bank").html("非服务时间");
                $("#bankMoney").data("status",2);
                $("#ok").data("net","1");
                $("#ok").attr("class","weui_btn weui_btn_primary");
                if($("#netBalance").data("netBalance")!=1){
                    $("#ok").html("确认缴费");
                }
            }
            else{
                $("#bugElectric").data("elec","0");
                $("#bankMoney").val(-1);
                $("#campusMoney").val(data.campus);
                $("#campus").html("请重新登陆");
                $("#bank").html("银行服务暂不可用");
                $("#ok").html("请重新登陆");
            }
        }
    })
}

function initNet(){
    $.ajax({
        url: yktAPI+"ajaxFunction&fnOption=netBalance",
        type:"get",
        dataType:"JSON",
        success:function(data){
            if(data.netBalance=="未开通"){
                $("#netBalance").html(data.netBalance+" >");
                $("#netBalance").data("netBalance","1");
                $("#ok").html("点我一键开通");
            }
            else{
                $("#netBalance").html(data.netBalance);
            }
        }
    })
}

$(function(){
    initNet();
    initBalance();
    $("#ok").click(function(){
        if($("#netBalance").data("netBalance")!=1){
            if($("#ok").data("net")==1){
                var uri=yktAPI+"ajaxFunction&fnOption=netPay",data={choosePayType :$("#choosePayType").val(),money: ($("#inputText").val()!="") ? $("#inputText").val() :$("#money").val()},fn_success=function (data) {
                    $.hideLoading();
                    if(data.status==1){
                        location.href=data.url;
                        // $.alert("购网成功，测试阶、段不跳转页面!");
                    }
                    else{
                        $("#ok").html("请重新登录再试");
                        $.alert("请重新登录再试","温馨提示");
                    }
                },fn_error=function () {
                    $.hideLoading();
                    $.alert("请尝试联系管理员或刷新页面重试！","温馨提示");
                };
                $.showLoading("全力加载中...");
                Public.loadAjaxData(uri,data,fn_success,fn_error,"post");
            }
            else{
                $.alert($("#ok").html(),"温馨提示");
            }
        }
        else{
            //这里我们将自动跳转到开通界面
            location.href=yktAPI+"netAccount";
        }
    })

    $("#netBalance").click(function(){
        if($("#netBalance").data("netBalance")==1){
            location.href=yktAPI+"netAccount";
        }
    })


})