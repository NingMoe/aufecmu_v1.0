function audioInit(i){length=i,$(".audio_clik").off("click"),$(".audio_clik").on("click",function(){for(var i=$(this).parents(".audioplayer"),e=i.find(".load")[0],a=i.find(".audio_time")[0],t=$("body").find("audio"),n=$(this),o=0;o<t.length;o++)t[o].className="unable",$(i).find("audio").removeClass("unable").addClass("audio"),"audio"==t[o].className?(t[o].paused?(t[o].play(),$(this).addClass("audio_pause")):(t[o].pause(),$(this).removeClass("audio_pause")),initAudio(t[o],e,a,n,length)):(t[o].pause(),$(".audio_clik").not(this).removeClass("audio_pause"),t[o].currentTime=0)})}function initAudio(i,e,a,t,n){n||(n=$(i).attr("length")),i.ontimeupdate||(i.ontimeupdate=function(){if(!isNaN(n)){var o=Math.ceil(i.currentTime)/n*100;e.style.width=parseInt(o)+"%",a.innerHTML=ceilTime(n-Math.ceil(i.currentTime)),parseInt(o)>="100"&&(e.style.width="0%",a.innerHTML=ceilTime(n),t.removeClass("audio_pause"))}})}function ceilTime(i){var e=Math.floor(i/60)+":"+(i%60/100).toFixed(2).slice(-2);return e}var length="";