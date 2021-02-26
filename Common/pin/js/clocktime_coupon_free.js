function lxfEndtime(){
	 $(".clock_div").each(function(){
		  var lxfday=$(this).attr("endtime");
		  var endtime = new Date(lxfday).getTime();
		  var nowtime = new Date().getTime();
		  var youtime = endtime-nowtime;
		  var seconds = youtime/1000;
		  var mc = youtime/100;
		  
		  mc = Math.floor(mc % 10);
			
			
		  var minutes = Math.floor(seconds/60);
		  var hours = Math.floor(minutes/60);
		  var days = Math.floor(hours/24);
		  var CDay= days ;
		  var CHour= hours % 24;
		  var CMinute= minutes % 60;
		  var CSecond= Math.floor(seconds%60);
		  
		  if(endtime<=nowtime){
				$(this).html("已过期")
			}else{
				
				var s_time = "";
				if(days > 0){
					s_time+= "<div class='time-block'>"+days+"</div> ";
				}
				if(CHour >0){
					s_time+="<div class='time-block'>"+CHour+"</div><div class='separator-block'>:</div>";
				}
				if(CMinute > 0){
					s_time+="<div class='time-block'>"+CMinute+"</div><div class='separator-block'>:</div>";
				}
				s_time+="<div class='time-block'>"+CSecond+"</div>";
				s_time+="<div class='time-desc'>后结束"+"</div>";
				
				$(this).html(s_time);
			}
	 });
	setTimeout("lxfEndtime()",100);
}