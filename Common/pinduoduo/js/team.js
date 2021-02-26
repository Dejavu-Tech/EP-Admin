$(function(){
	$('#sku-quit').on('click',function(){
		$('#sku_selector').hide();
	});
	$(".checked").each(function(){
		var quantity = parseInt($(this).attr('relquantity'));
		if(quantity < max_quantity)
		{
			max_quantity = quantity;
		}
	});
	$('.goods').on('click',function(){
		if($(this).hasClass('disableds')){
			return false;
		}
		$(this).parent().siblings().children('span').removeClass('checked');
		$(this).addClass('checked');
		var relimg = $(this).attr('relimg');
		
		var mult_option_ckids = get_option_check_ids();
		if(mult_option_ckids != '' && mul_op_image[mult_option_ckids] != undefined)
		{
			if(mul_op_image[mult_option_ckids] != '')
				$('#sku-image').attr('src',mul_op_image[mult_option_ckids]);
		}else {
			if(relimg && relimg != ''){
				$('#sku-image').attr('src',relimg);
			}
		}
		
		if(mult_option_ckids != '' && mul_op_quitity[mult_option_ckids] != undefined)
		{
			max_quantity = mul_op_quitity[mult_option_ckids];
		}else {
			$(".checked").each(function(){
				var quantity = parseInt($(this).attr('relquantity'));
				if(quantity < max_quantity)
				{
					max_quantity = quantity;
				}
			});
		}
	});
	
	$("#skuNum").keyup(function(){  
        $(this).val($(this).val().replace(/[^0-9]/g,'1'));  
        var num = parseInt($(this).val());
        if(num > max_quantity) {
        	 $(this).val(max_quantity);
        	 showTip('很抱歉，该商品当前至多能购买'+max_quantity+'份');
        }
		
		
        if($(this).val() >1){
        	$('.sku-buy-amount-reduce span').removeClass('button-disabled');
        }
        	
    }).bind("paste",function(){ //CTR+V事件处理  $(this).val($(this).val().replace(/[^0-9.]/g,''));   
    	
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用 
	
	$('.sku-buy-amount-increase').click(function(){
		$('#skuNum').val($('#skuNum').val().replace(/[^0-9]/g,'1'));  
        var num = parseInt($('#skuNum').val())+1;
        if(num > max_quantity) {
       	 $('#skuNum').val(max_quantity);
       	 showTip('很抱歉，该商品当前至多能购买'+max_quantity+'份');
       }else {
    	   $('#skuNum').val(num);
       }
        
	})
	$('.sku-buy-amount-reduce').click(function(){
		$('#skuNum').val($('#skuNum').val().replace(/[^0-9]/g,'1'));  
        var num = parseInt($('#skuNum').val());
        if(num > max_quantity) {
       	 $('#skuNum').val(max_quantity);
       	 showTip('很抱歉，该商品当前至多能购买'+max_quantity+'份');
		 return false;
       }
        num = parseInt($('#skuNum').val()) - 1;
        if(num == 0){
        	$('#skuNum').val(1);
        	$('.sku-buy-amount-reduce span').addClass('button-disabled');
        }else {
        	$('#skuNum').val(num);
        }
	})
	$('#sku-buy').on('click',function(){
		//relgoods_option_id="{$option.goods_option_id}" relgoods_option_value_id
		
		 var num = parseInt($('#skuNum').val());
		 
		 var mult_option_ckids = get_option_check_ids();
		
		
		if(mult_option_ckids != '' && mul_op_quitity[mult_option_ckids] != undefined)
		{
			max_quantity = mul_op_quitity[mult_option_ckids];
		}
		
        if(num > max_quantity) {
       	 $('#skuNum').val(max_quantity);
		 if(max_quantity == 0)
		 {
			showTip('该规格已售罄'); 
		 }else {
			 showTip('很抱歉，该商品当前至多能购买'+max_quantity+'份');
		 }
       	 
		 return false;
       }
	   
	   
		var sku_info_length = $('.sku-info').length;
		
		var sku_infock_length = $('.sku-info .checked ').length;
		if(sku_infock_length < sku_info_length)
		{
			showTip('请选择商品规格');
			return false;
		}
		
		
		
		
		
		
		var data ={};
		var optionc = [];
		$(".checked").each(function(){
			var tp_goods_option_id = $(this).attr('relgoods_option_id');
			var tp_relgoods_option_value_id = $(this).attr('relgoods_option_value_id');
			var s_gvi = tp_goods_option_id + '_' +tp_relgoods_option_value_id;
			optionc.push(s_gvi)
		});
		data.goods_id = $('#goods_id').val();
		data.pin_id = $('#pin_id').val();
		data.quantity = $('#skuNum').val();
		data.pin_type = $('#pin_type').val();
		data.optionc = optionc;
		sub_cart(data);
	})
	
	$('#tuan_more_btn').click(function(){
		
		$('#pin_type').val('pin');
		
		if(has_option){
			$('#sku-price-depends').html($('#tuan_more_price').html());
			$('#sku_selector').show();
		} else{
			var data = {};
			data.goods_id = $('#goods_id').val();
			data.pin_id = $('#pin_id').val();
			data.option = [];
			data.quantity = 1;
			data.pin_type = $('#pin_type').val();
			sub_cart(data);
		}
	})
	$('#tuan_one_btn').click(function(){
		$('#pin_type').val('dan');
		var type = $('#type').val();
		if(type == 'lottery')
		{
			 showTip('抽奖活动暂不支持单独购买');
			 return false;
		}
		if(type == 'zeyuan')
		{
			 showTip('免费试用活动暂不支持单独购买');
			 return false;
		}
		
		if(has_option){
			$('#sku-price-depends').html($('#tuan_one_price').html());
			$('#sku_selector').show();
		} else{
			var data = {};
			data.goods_id = $('#goods_id').val();
			data.pin_id = 0;
			data.option = [];
			data.quantity = 1;
			data.pin_type = $('#pin_type').val();
			sub_cart(data);
		}
	})
	
})

function get_option_check_ids()
{
	var ck_arr = [];
	$(".checked").each(function(){
		var option_value_id = parseInt($(this).attr('option_value_id'));
		ck_arr.push(option_value_id);
	});
	if(ck_arr.length >0)
	{
		return ck_arr.join('_');
		
	}else 
		return '';
	
}
/**
 * 
 * 购物车提交
 */
function sub_cart(datobj)
{
	 //name="option[<?php echo $option['goods_option_id']; ?>]" 
	 //value="<?php echo $option_value['goods_option_value_id']; ?>"
	//goods_id,quantity,option
	$.ajax({
		url: add_cart_url,
		type: 'post',
		data: datobj,
		dataType: 'json',
		success: function(json) {
			
			//{success: "成功加入购物车！！", total: 1}
			if (json['error']) {
				
                if (json['error']['option']) {
                	var error_tip_html = '';
		          for (i in json['error']['option']) {
		            error_tip_html += json['error']['option'][i];
		          }
				}
                showTip(error_tip_html);
			}	
			
			if (json['success']) {				
				location.href = add_cart_success_url;
				return false;
			}else if(json['error']['quantity']){
				 showTip(json['error']['quantity']);
			}	
		}
	});	
}
