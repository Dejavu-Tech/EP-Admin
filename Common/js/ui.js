var ui={
	focus:function(focusId,autoTime){
		var wrap=$(focusId),
		pics=$(""+focusId+" .pic-item"),
		intros=$(""+focusId+" .intro"),
		pagItems=$(""+focusId+" .pagination-item"),
		len=pics.length;
		var preIndex=0,
		index=0;
		var firstPic=pics.eq(0).find('img').eq(0);
		var img=new Image();
		var wrap_width=wrap.width();
		var pic_height=0;
		img.onload=function(){
			pic_height=wrap_width*img.height/img.width;
			wrap.css({'height':pic_height});
			$(window).bind('resize',function(){
				wrap_width=wrap.width();
				pic_height=wrap_width*img.height/img.width;
				wrap.css({'height':pic_height});
			});
		};
		img.src=firstPic.attr('src');
		if(len==1){
			$(""+focusId+" .pagination").eq(0).hide();
			return;
		};
		(function init(){
			pics.each(function(){
				var i=$(this).index();
				$(this).css({"z-index":len-i});
			});
		})();
		function toSwitch(i){
			pics.eq(preIndex).stop(true,true).fadeOut();
			if(intros.length){
				intros.eq(preIndex).hide();
				intros.eq(i).show();
			};
			pagItems.eq(preIndex).removeClass("active");
			pics.eq(i).stop(true,true).fadeIn();
			pagItems.eq(i).addClass("active");
			preIndex=i;
		};
		pagItems.each(function(){
			$(this).bind("click",function(){
				if($(this).hasClass('active')){
					return;
				};
				var i=$(this).index();
				toSwitch(i);
				index=i;
			});
		});
		function autoPlay(){
			index++;
			if(index>=len){index=0;};
			toSwitch(index);
		};
		if(wrap.has('.arrows').length>0){
			var arrow_left=wrap.find('.arrow-left').eq(0);
			var arrow_right=wrap.find('.arrow-right').eq(0);
			arrow_left.bind('click',function(){
				index--;
				if(index<0){index=len-1;};
				toSwitch(index);
			});
			arrow_right.bind('click',function(){
				index++;
				if(index>=len){index=0;};
				toSwitch(index);
			});
		};
		var timer=setInterval(autoPlay,autoTime);
		wrap.hover(function(){
			clearInterval(timer);
		},function(){
			timer=setInterval(autoPlay,autoTime);
		});
	},
	titleDrop:function(titleId,contentId){
		var title=$(titleId);
		var ctbox=$(contentId);
		var allContent=$('.drop-container');
		var allTitle=$('.drop-title');
		var closeBtn=ctbox.find('.close');
		title.bind('click',function(){	
			if(title.hasClass('current')){
				title.removeClass('current');
				ctbox.stop(true,true).slideUp();
			}else{
				allContent.each(function(i){
					allContent.eq(i).hide();
					allTitle.eq(i).removeClass('current');
				});
				$(this).addClass('current');
				ctbox.stop(true,true).slideDown();
			};
		});
		if(closeBtn){
			closeBtn.bind('click',function(){
				title.removeClass('current');
				ctbox.stop(true,true).slideUp();
			});
		};
	},
	sideSlide:function(titleId,contentId){
		var title=$(titleId);
		var content=$(contentId);
		var sideConent=content.find('.side-right-container').eq(0);
		title.bind('click',function(){	
			if(title.hasClass('active')){
				title.removeClass('active');
				sideConent.stop(true,true).fadeOut(function(){
					content.stop(true,true).animate({'left':'-190px'},function(){
						content.hide();
					});
				});
				
			}else{
				content.show();
				$(this).addClass('active');
				content.stop(true,true).animate({'left':'0'},function(){
					sideConent.stop(true,true).fadeIn();
				});
			};
		});
	},
	pointDialog:function(points,dialogs){
		var pre=0;
		points.each(function(){
			$(this).bind('click',function(){
				var current=$(this).index();
				if(!$(this).hasClass('current')){
					points.eq(pre).removeClass('current');
					dialogs.eq(pre).hide();
					$(this).addClass('current');
					dialogs.eq(current).fadeIn(300);
					pre=current;
				};
			});
		});
		dialogs.each(function(){
			var closeBtn=$(this).find('.close');
			if(closeBtn){
				closeBtn.bind('click',function(){
					var dialog=$(this).parent('.point-dialog');
					var current=dialog.index();
					dialog.fadeOut(300);
					points.eq(current).removeClass('current');
				});
			};
		});
	},
	reviewPicFocus:function(smpic_options,bgpic_box){
		var options=$(smpic_options);
		var bgpicbox=$(bgpic_box);
		var bigPic=bgpicbox.find('img').eq(0);
		var pre=0;
		options.each(function(){
			var this_=$(this);
			this_.bind('click',function(){
				var current=this_.index();
				if(!this_.hasClass('active')){
					options.eq(pre).removeClass('active');
					this_.addClass('active');
					bgpicbox.addClass('active');
					bigPic.attr('src',this_.find('img').eq(0).attr('src'));
				}else{
					this_.removeClass('active');
					bgpicbox.removeClass('active');
				};
				pre=current;
			});
		});
	},
	scrollToTop:function(sideBox){
		var sideBox=$(sideBox);
		var documentHeight=$(document).height();
		$(window).bind('scroll',function(){
			if($(document).scrollTop()>=documentHeight/3){
				sideBox.fadeIn();
			}else{
				sideBox.fadeOut();
			}
		});
		$("#totop").click(function(){ 
	        $.scrollTo('#wrap',500); 
	    });
	},
	dialog:function(handle,dialog,callback){
		var handle=$(handle);
		var dialog=$(dialog);
		handle.bind('click',function(){
			dialog.show();
		});
		var closeBtn=dialog.find('.close');
		var cancelBtn=dialog.find('.cancel');
		if(closeBtn||cancelBtn){
			closeBtn.bind('click',function(){
				dialog.hide();
			});
			cancelBtn.bind('click',function(){
				dialog.hide();
			});
		};
		if(typeof arguments[2]=='function'){
			(callback)();
		};
	},
	hintDialog:function(handle,dialog,callback){
		var handle=$(handle);
		var dialog=$(dialog);
		handle.bind('click',function(){
			dialog.show();
			if(dialog.timer){
				clearTimeout(dialog.timer);
			};
			dialog.timer=setTimeout(function(){
				dialog.stop().fadeOut(300);
			},2000);
		});
		if(typeof arguments[2]=='function'){
			(callback)();
		};
	},
	tab:function(options,tabContents){
		var preIndex=0;
		var options=$(options);
		var tabContents=$(tabContents);
		(function init(){
			options.eq(0).addClass('active');
			tabContents.eq(0).fadeIn();
		})();
		options.each(function(){
			var this_=$(this);
			this_.bind('click',function(){
				if(preIndex==this_.index()){
					return;
				};
				var curIndex=this_.index();
				tabContents.eq(curIndex).fadeIn();
				tabContents.eq(preIndex).hide();
				this_.addClass('active');
				options.eq(preIndex).removeClass('active');
				preIndex=curIndex;
			});
		});
	}
};

