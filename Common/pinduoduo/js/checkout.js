$(function(){
	$('#oc-address').click(function(){
		
            var areaId=0;
            $.post(ajaxarea_url,{'areaId':areaId,'goods_id':goods_id},function(data){                
                
                $('#areaprovince').html('<option value="0">选择省份</option>');
                
                $.each(data,function(no,items){
                   $('#areaprovince').append('<option value="'+items.area_id+'">'+items.area_name+'</option>');
                });
                $('#m-addr-mask').show();
            });
	})
	
	$("#areaprovince").change(function(){
		var areaId = $(this).val();
		if(areaId == 0){
			$('#areacity').html('<option value="0">选择城市</option>');
			$('#areadistrict').html('<option value="0">选择地区</option>')
		} else {
			$.post(ajaxarea_url,{'areaId':areaId,'goods_id':goods_id},function(data){    
                $('#areacity').html('<option value="0">选择城市</option>');
                $.each(data,function(no,items){
                   $('#areacity').append('<option value="'+items.area_id+'">'+items.area_name+'</option>');
                });
                
                $('#areadistrict').html('<option value="0">选择地区</option>');
            });
		}
			
	})
	
	
	$("#areacity").change(function(){
		var areaId = $(this).val();
		if(areaId == 0){
			$('#areadistrict').html('<option value="0">选择地区</option>')
		} else {
			$.post(ajaxarea_url,{'areaId':areaId},function(data){    
                $('#areadistrict').html('<option value="0">选择地区</option>');
                $.each(data,function(no,items){
                   $('#areadistrict').append('<option value="'+items.area_id+'">'+items.area_name+'</option>');
                });
                
                
            });
		}
			
	})
	
	$('.oc-mall-coupon-info').on('click',function(){
		check_voucher_use(1);
	})
	
	
	
	$('.m-addr-save').on('click',function(){
		var name = $('#name').val();
		var m_addr_mobile = $('.m-addr-mobile').val();
		var areaprovince = $('#areaprovince').val();
		var areaprovince_html = $('#areaprovince').find("option:selected").text();
		
		var areacity = $('#areacity').val();
		var areacity_html = $('#areacity').find("option:selected").text();
		
		var areadistrict = $('#areadistrict').val();
		var areadistrict_html = $('#areadistrict').find("option:selected").text();
		
		var addr_address = $('#addr_address').val();
		
		if($.trim(name) == ''){
			showTip('请填写姓名');
			return false;
		}
		if($.trim(m_addr_mobile) == ''){
			showTip('请填写手机');
			return false;
		}
		
		if(!m_addr_mobile.match('^1[3|5|8|7][0-9]{9}$')){ 
			showTip('请输入正确的手机号码');
			return false; 
		}
		if(areaprovince <= 0)
		{
			showTip('请选择省份');
			return false; 
		}
		if(areacity <= 0)
		{
			showTip('请选择城市');
			return false; 
		}
		if(areadistrict <= 0)
		{
			showTip('请选择地区');
			return false; 
		}
		
		if($.trim(addr_address) == ''){
			showTip('请填写详细街道地址');
			return false;
		}
		
		var data_obj = {name:name,telephone:m_addr_mobile,is_default:1,province_id:areaprovince,city_id:areacity,country_id:areadistrict,address:addr_address};
		$.ajax({
			url:ajaxaddress_add_url,
			type:'post',
			data:data_obj,
			dataType:'json',
			success:function(json){
				if(json.code == 1) {
					var oc_address_html = '';
					oc_address_html+= '<div class="oc-address-info">';
					oc_address_html+= '    <div class="oc-address-receiver">';
					oc_address_html+= '        '+name+'&nbsp;&nbsp;&nbsp;'+m_addr_mobile;
					oc_address_html+= '    </div>';
					oc_address_html+= '   <div class="oc-address-detail">';
					oc_address_html+= '        '+areaprovince_html+'&nbsp;'+areacity_html+'&nbsp;'+areadistrict_html+'&nbsp;'+addr_address;
					oc_address_html+= '    </div>';
					oc_address_html+= '</div>';
					$('#m-addr-mask').hide();
				
					$('#oc-address').removeClass('oc-add-address');
					$('#oc-address').removeClass('indicator');
					$('#oc-address').html(oc_address_html);
					address_id = json.address_id;
				} else {
					showTip(json.error);
					return false;
				}
			}
		})
		
	})
	
	$('.m-addr-close').click(function(){
		$('#m-addr-mask').hide();
	})
	
	$('.check-lst li a').click(function(){
		
		$(this).parent().siblings().children('a').removeClass('checkboxed');
		$(this).addClass('checkboxed');
		var type = $(this).attr('type');
		if(type == 'express')
		{
			var s_fare = $(this).attr('fare');
			if(s_fare == undefined)
			{
				s_fare = 0;
			}
			$('#trans_free').val(s_fare);	
			$('#fare_show').html('¥'+s_fare);
			 
			express_id = $(this).attr('rel');
		} else if(type == 'pickup'){
			$('#trans_free').val(0);	
			$('#fare_show').html('¥0');
			pick_up_id = $(this).attr('rel');
		}
		change_total_free();
	})
	
	$("#skuNum").keyup(function(){  
        $(this).val($(this).val().replace(/[^0-9]/g,'1'));  
        var num = parseInt($(this).val());
       
        if(num > max_quantity) {
        	 $(this).val(max_quantity);
        	 check_voucher_use(0);
        	 change_total_free();
        	 showTip('很抱歉，该商品当前至多能购买'+max_quantity+'份');
        }
        if(isNaN(num))
        {
        	 $(this).val(1);
        }
        check_voucher_use(0);
        change_total_free();
        if($(this).val() >1){
        	$('.oc-goods-reduce').removeClass('oc-increase-disable');
        }
        	
    }).bind("paste",function(){ //CTR+V事件处理  $(this).val($(this).val().replace(/[^0-9.]/g,''));   
    	
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用 
	
	$('.oc-goods-increase').click(function(){
		$('#skuNum').val($('#skuNum').val().replace(/[^0-9]/g,'1'));  
        var num = parseInt($('#skuNum').val())+1;
        $('#skuNum').val(num);
        if($('#skuNum').val() >1){
        	$('.oc-goods-reduce').removeClass('oc-increase-disable');
        }
       
        if(num > max_quantity) {
       	 	$('#skuNum').val(max_quantity);
       	 	check_voucher_use(0);
       	 	change_total_free();
       	 	showTip('很抱歉，该商品当前至多能购买'+max_quantity+'份');
       }else {
    	   $('#skuNum').val(num);
    	   check_voucher_use(0);
    	   change_total_free();
       }
        
	})
	$('.oc-goods-reduce').click(function(){
		$('#skuNum').val($('#skuNum').val().replace(/[^0-9]/g,'1'));  
        var num = parseInt($('#skuNum').val());
       
       if(num > max_quantity) {
       	 $('#skuNum').val(max_quantity);
       	 check_voucher_use(0);
       	 change_total_free()
       	 showTip('很抱歉，该商品当前至多能购买'+max_quantity+'份');
       }
        num = parseInt($('#skuNum').val()) - 1;
        if(num == 0){
        	$('#skuNum').val(1);
        	 check_voucher_use(0);
        	 change_total_free()
        	$('.oc-goods-reduce').addClass('oc-increase-disable');
        }else {
        	$('#skuNum').val(num);
        	check_voucher_use(0);
        	 change_total_free()
        }
	})
	$('.oc-pay-btn').click(function(){
		if(!can_sub) {
			return false;
		} else {
			//can_sub = false;
			$(this).html('正在支付请稍后');
		}
		if(address_id == 0)
		{
			can_sub = true;
			$(this).html('立即支付');
			showTip('请添加收货地址');
			return false;
		}
		
		if(limit_haitao)
		{
			can_sub = true;
			$('.id-card-main-v2').show();
			return false;
		}
		
		var remark = $('#remark').val();
		
		var num = $('#skuNum').val();
		var transport_id = $('#transport_id').val();
		var payment_method = $('.oc-payment-selected').attr('payment-method');
		
		if(parseInt(num) == 0){
			 $(this).html('立即支付');
			 can_sub = true;
			 showTip('请选择购买数量');
			 return false;
		}
		$.ajax({
			url:cartdone_url,
			type:'post',
			data:{num:num,remark:remark,transport_id:transport_id,payment_method:payment_method,voucher_id:voucher_id,address_id:address_id,pick_up_id:pick_up_id,express_id:express_id,delivery:delivery},
			dataType:'json',
			success:function(res){
				if(res.code ==0){
					 showTip(res.msg);
					 can_sub = true;
				} else if(res.code == 1) {
					location.href = res.url;
					return false;
				}
			}
			
		})
			
		
	})
	
})

function check_voucher_use(is_show)
{
	//store_id
	
	var goods_price = parseFloat($('#goods_price').attr('data-price'));
	var skuNum = parseInt($('#skuNum').val());
	var trans_free = parseFloat($('#trans_free').val());
	var total_free = ((goods_price*skuNum) + trans_free).toFixed(2);
	
	$.ajax({
		url:user_pay_voucher_url,
		type:'post',
		data:{store_id:store_id,total_free:total_free,voucher_id:voucher_id},
		dataType:'json',
		success:function(result){
			if(result.code == 1){
				$('.oc-coupons').html(result.html);
				if(is_show == 1)
					$('.oc-coupons').show();
			}else if(result.code == 2) {
				$('.oc-coupons').html(result.html);
				
				voucher_id = 0;
				voucher_money = 0; 
				$('.oc-mall-coupon-desc').html(voucher_money+'元');
				change_total_free();	
				if(is_show == 1)
				{	
					
					$('.oc-coupons').show();
				}
			}else if(result.code == 0){
				//$('.oc-coupons').html('暂无可用的优惠券');
				var s_html  = '';
				s_html += '<div class="oc-coupons-mian" style="bottom: 0px;">';
				s_html += '	<div class="oc-coupons-title-container">';
				s_html += '		<div class="oc-coupons-title">';
				s_html += '			<span class="oc-coupons-title-m">暂无可用的优惠券</span>';
				s_html += '			<div class="oc-coupons-close" onclick="close_couponmain()">';
				s_html += '				<div class="oc-coupons-close-icon"></div>';
				s_html += '			</div>';
				s_html += '		</div>';
				s_html += '	</div>';
				s_html += '</div>';
				$('.oc-coupons').html(s_html);
				if(is_show == 1)
				{	
					$('.oc-coupons').show();
				}
			}
				
		}
		
	})
	
	
}

function close_couponmain()
{
	$('.oc-coupons').hide();
}

function coupon_ck(obj)
{
	$(obj).siblings().removeClass('oc-m-coupons-selected');
	$(obj).removeClass('oc-m-coupons-unselected');
	$(obj).addClass('oc-m-coupons-selected');
	
	voucher_id = $(obj).attr('data-voucher-id');
	voucher_money = parseFloat( $(obj).attr('data-voucher-credit')); 
	change_total_free();
	if(voucher_money > 0)
	{
		$('.oc-mall-coupons').html('<span>'+$(obj).children('.oc-m-coupon-right').children('.oc-m-coupon-min').html()+'</span>');
		$('.oc-mall-coupon-desc').html('- '+voucher_money+'元');
	}
		
	else 
		$('.oc-mall-coupon-desc').html(voucher_money+'元');
	
	$('.oc-coupons').hide();
}

function get_voucher(quan_id)
{
	
	$.ajax({
		url:get_vouher_url,
		type:'post',
		data:{quan_id:quan_id},
		dataType:'json',
		success:function(result){
			showTip(result.msg);
		}
	})
}

function change_total_free()
{
	var goods_price = parseFloat($('#goods_price').attr('data-price'));
	var skuNum = parseInt($('#skuNum').val());
	if($('#trans_free').val() == undefined){
		$('#trans_free').val(0)
		var trans_free = 0;
	} else{
		var trans_free = parseFloat($('#trans_free').val());
	}
	
	var total_free = ((goods_price*skuNum) + trans_free - voucher_money).toFixed(2);
	if(total_free<0)
	{
		total_free = 0;
	}
	
	if(is_free_tuan ==1)
	{
		$('#total_free').html( '¥0(免单券开团)');
		$('.oc-finial-amount').html('¥0(免单券开团)');
	} else{
		$('#total_free').html( '¥'+ total_free);
		$('.oc-finial-amount').html('¥'+ total_free);
	}
	
	
	
}

