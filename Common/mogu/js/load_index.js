var goods_page = 1;
var goods_rid = 1;
var is_can_next = 1;
$(function(){
	var link = [];
	is_can_next = 0;
	nextpage();
	$('.nav-item').click(function(){
		goods_rid = $(this).attr('data-rid');
		goods_page = 1;
		$(this).addClass('active').siblings().removeClass('active');
		//$('#goods_content').html('');
		nextpage();
		return false;
	})
	//load best pintuan
	$.ajax({
		url:load_pintuan_url,
		type:'get',
		dataType:'json',
		success:function(ret)
		{
			if(ret.code == 1){
				var s_data = ret.list;
				for(var i in s_data){
					var s_tmp = {};
					s_tmp.url = s_data[i].url;
					s_tmp.fan_image = s_data[i].fan_image;
					s_tmp.name = s_data[i].name;
					s_tmp.type = s_data[i].type;
					s_tmp.pin_price = s_data[i].pin_price;
					s_tmp.pin_count = s_data[i].pin_count;
					s_tmp.seller_count = s_data[i].seller_count;
					link.push(s_tmp);
				}
				bindImgEvent();
				$('#best_pintuan').tmpl(link).appendTo('#best_pintuan_wrap');
			}
		}
	})
	
	//load best goods
	$(window).scroll( function() { 
		loaddata();
	});
	
})


var cur_url = "{:U('Index/index')}";

function loaddata()
{ 
	var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());     //浏览器的高度加上滚动条的高度 

	if ($(document).height() <= totalheight+200)     //当文档的高度小于或者等于总的高度的时候，开始动态加载数据
	{ 
		if(is_can_next == 1)//加载数据
		{
			is_can_next = 0;
			nextpage();
		}
	} 
}

function load_caini_xihuan()
{
	
	$.ajax(
		{ 
			url: load_suiji_url, 
			type:'post',
			data:{},
			dataType: 'json', 
			success: function(ret){
				
				if(ret.code == 1)
				{
					//id="m123585"
					if(ret.html != ''){
						$('#suiji_goods_content').html(ret.html);	
						$('#guss_box').show();
						$('#m123585').show();
						bindImgEvent();
					}
					
				} else if(result.code == 0) {
					
				}
				
			}
		})
		
	
}

function nextpage()
{	
	
	$('#center_pullup').show();
	$.ajax(
		{ 
			url: load_goods_url, 
			type:'post',
			data:{page:goods_page,goods_rid:goods_rid},
			dataType: 'json', 
			success: function(ret){
				var links = [];
				if(ret.code == 1)
				{
					var s_data = ret.list;
					for(var i in s_data){
						var s_tmp = {};
						s_tmp.url = s_data[i].url;
						s_tmp.image = s_data[i].fan_image;
						s_tmp.name = s_data[i].name;
						s_tmp.danprice = s_data[i].danprice;
						s_tmp.seller_count = s_data[i].seller_count;
					
						links.push(s_tmp);
					} 
					if(goods_page== 1)
					{
						$('#goods_content').html('');
					}
					console.log('next_page:..');
					console.log(links);
					
					$('#best_goods').tmpl(links).appendTo('#goods_content');
					
					goods_page++;
					is_can_next = 1;
					
					bindImgEvent();
					
				} else if(ret.code == 0) {
					load_caini_xihuan();
				}
				
			}
		}
	);
}