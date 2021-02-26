function bindImgEvent(){
        echo.init({
		  offset: 0,
		  throttle: 0,
		  unload: false,
		  callback: function (element, op) {
			//console.log(element, 'has been', op + 'ed')
		  }
		});
}
function _touch(){
          var startx;//让startx在touch事件函数里是全局性变量。
          var endx;
		var el=document.getElementById('main');
        function cons(){   //独立封装这个事件可以保证执行顺序，从而能够访问得到应该访问的数据。
                
			if(startx>endx){  //判断左右移动程序
				bindImgEvent();
			}else{
				  bindImgEvent();
			}
        }
		el.addEventListener('touchstart',function(e){
			var touch=e.changedTouches;
			startx=touch[0].clientX;
			starty=touch[0].clientY;
		});
        el.addEventListener('touchend',function(e){
            var touch=e.changedTouches;
            endx=touch[0].clientX;
            endy=touch[0].clientY;
            cons();  
       });
 }
 _touch();