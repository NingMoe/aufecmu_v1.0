/**
 * Created by WeiZeng on 2016/8/15.
 */

$.post(share,{ url: encodeURIComponent(location.href.split('#')[0])},function(data){
		console.log(data);
    var date=JSON.parse(data);
    console.log(date);
    wx.config({
	        debug: false,
	
	        appId: date.appId,
	
	        timestamp: date.timestamp,
	
	        nonceStr: date.nonceStr,
	
	        signature: date.signature,
	        jsApiList: [
	            'onMenuShareTimeline',
	            'onMenuShareAppMessage',
	            'onMenuShareQQ',
	            'onMenuShareWeibo',
	            'onMenuShareQZone',
	            'hideMenuItems',
	            'hideOptionMenu',
	            'showOptionMenu',
	            'showMenuItems',
	            'hideAllNonBaseMenuItem',
	            'showAllNonBaseMenuItem',
	        ]
	    });
    
    
});

wx.ready(function () {
    	
		    wx.hideAllNonBaseMenuItem();
		    
		});