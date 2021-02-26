$(function(){

	var breadcrumbs=$('#breadcrumbs li').first().find('a').text();

	var breadcrumbs2=$('#breadcrumbs li.active').text();

	$('#sidebar .menu-text').each(function(i){
		var t=$(this).text();

		if(t==breadcrumbs){
			$('#sidebar>ul>li').eq(i).addClass('open');
			$('#sidebar>ul>li').eq(i).find('ul').css({'display': 'block'});
		}
	});

	$('.submenu a span.url-title').each(function(i){
		var t=$(this).text();

		if(t==breadcrumbs2){

			$(this).css({'color': '#f60'});
		}
	});

	$('.delete').click(function(){
			var f=confirm('确认要执行该操作吗？');
			if(f==false){
				return false;
			}

			if($(this).hasClass("one")){
				if($('input:checked').size()==0){
					alert('请选择一个条目');
					return false;
				}else{
					var target,query,form;
					form=$('#table');

					target = $(this).attr('href');
					query = form.find('input,select,textarea').serialize();

					$.post(target,query).success(function(data){});
				}
			}
	});
		//全选的实现
	$(".check-all").click(function(){
		$(".ids").prop("checked", this.checked);
	});
	$(".ids").click(function(){
		var option = $(".ids");
		option.each(function(i){
			if(!this.checked){
				$(".check-all").prop("checked", false);
				return false;
			}else{
				$(".check-all").prop("checked", true);
			}
		});
	});

	var Osc = window.dejavutech={};

		/* 设置表单的值 */
		Osc.setValue = function(name, value){
			var first = name.substr(0,1), input, i = 0, val;
			if(value === "") return;
			if("#" === first || "." === first){
				input = $(name);
			} else {
				input = $("[name='" + name + "']");
			}

			if(input.eq(0).is(":radio")) { //单选按钮
				input.filter("[value='" + value + "']").each(function(){this.checked = true});
			} else if(input.eq(0).is(":checkbox")) { //复选框
				if(!$.isArray(value)){
					val = new Array();
					val[0] = value;
				} else {
					val = value;
				}
				for(i = 0, len = val.length; i < len; i++){
					input.filter("[value='" + val[i] + "']").each(function(){this.checked = true});
				}
			} else {  //其他表单选项直接设置值
				input.val(value);
			}
		}


});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown

			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});
			/**/
			// Click
			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);
