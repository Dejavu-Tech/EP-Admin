$(function() {
	require(['util'], function(util) {
		var funbaredit = false;
		var edithtml = '<span class="editdel"><span class="edit">编辑</span> <span class="del">删除</span></span>';
		var funbarstatus = util.cookie.get('funbar');
		if (funbarstatus && funbarstatus > 0) {
			$(".funbar").addClass("active")
		}
		$(".funbar-toggle").unbind('click').click(function() {
			$(".funbar").toggleClass('active');
			if ($('.funbar').hasClass('active')) {
				util.cookie.set('funbar', 1)
			} else {
				util.cookie.set('funbar', 0)
			}
		});
		$(".funbar .edit-btn").unbind('click').click(function() {
			$(".fb-footer .default").hide();
			$(".fb-footer .complete").show();
			$(".funbar").data('edit', 1).addClass('edit');
			$(".page-gotop.style2").addClass('edit');
			funbaredit = true;
			require(['jquery.ui'], function() {
				$("#funbar-btns").sortable({
					containment: 'parent'
				})
			})
		});
		$(document).on('mouseover', '.funbar .fb-inner nav', function() {
			if (funbaredit) {
				if ($(this).find('.editdel').length <= 0) {
					$(this).append(edithtml)
				} else {
					$(this).find('.editdel').show()
				}
			}
		});
		$(document).on('mouseleave', '.funbar .fb-inner nav', function() {
			$(this).find('.editdel').hide()
		});
		$(".funbar .save-btn").unbind('click').click(function() {
			$(".fb-footer .complete").hide();
			$(".fb-footer .default").show();
			$(".funbar").data('edit', 0).removeClass('edit');
			$(".page-gotop.style2").removeClass('edit');
			funbaredit = false;
			$("#funbar-btns").sortable('destroy');
			saveFun()
		});
		$(document).on('click', '.funbar .fb-inner nav .edit', function() {
			if (funbaredit) {
				var nav = $(this).closest('nav');
				var text = nav.find('span').eq(0).text();
				var color = rgb2hex(nav.css('color'));
				if (color == '#ffffff') {
					color = '#666666'
				}
				var bold = nav.css('font-weight');
				if (bold == 'bold') {
					$("#modal-funbar").find('#menubold1').prop('checked', true);
					$("#modal-funbar").find('#menubold0').removeAttr('checked')
				} else {
					$("#modal-funbar").find('#menubold0').prop('checked', true);
					$("#modal-funbar").find('#menubold1').removeAttr('checked')
				}
				var link = nav.data('href');
				if ($.trim(link) == '') {
					link = thisUrl()
				}
				$("#modal-funbar").find('#menuname').val(text);
				$("#modal-funbar").find('#menucolor').val(color);
				$("#modal-funbar").find('#menulink').val(link);
				nav.addClass('active');
				$("#modal-funbar .modal-header h3").text("编辑快捷导航");
				$("#modal-funbar").modal({
					backdrop: 'static',
					keyboard: false
				})
			}
		});
		$(document).on('click', '.funbar .fb-inner nav .del', function() {
			if (funbaredit) {
				var _this = $(this);
				tip.confirm("确定删除吗? 删除后点击保存才生效哦~", function() {
					_this.closest("nav").remove()
				})
			}
		});
		$("#modal-funbar .close-modal").unbind('click').click(function() {
			var modal = $(this).closest('#modal-funbar');
			var text = modal.find('#menuname').val();
			var color = modal.find('#menucolor').val();
			var link = modal.find('#menulink').val();
			var bold = modal.find("input[name='bold']:checked").val();
			if ($.trim(text) == '') {
				modal.find('#menuname').focus();
				tip.msgbox.err("请填写导航名称！");
				return false
			}
			if ($.trim(link) == '') {
				link = thisUrl()
			}
			if (bold && bold > 0) {
				var style = {
					'font-weight': 'bold'
				}
			} else {
				var style = {
					'font-weight': 'normal'
				}
			}
			if (color != '#666666') {
				style.color = color
			}
			if ($(".funbar .fb-inner nav.active").length > 0) {
				$(".funbar .fb-inner nav.active").css(style).data('href', link).find("span").eq(0).text(text);
				$(".funbar .fb-inner nav").removeClass('active')
			} else {
				var _html = '<nav data-href="' + link + '" style="';
				if (color != '#666666') {
					_html += 'color: ' + color + "; "
				}
				if (bold && bold > 0) {
					_html += 'font-weight: bold; '
				} else {
					_html += 'font-weight: normal; '
				}
				_html += '"><span>' + text + '</span></nav>';
				$("#funbar-btns").append(_html)
			}
		});
		$("#modal-funbar .close-modal2").unbind('click').click(function() {
			$(".funbar .fb-inner nav.active").removeClass('active')
		});
		$(document).on('click', '.funbar .fb-inner nav', function() {
			if (!funbaredit) {
				var href = $(this).data('href');
				if (href) {
					location.href = href
				}
			}
		});
		$(".add-btn").unbind('click').click(function() {
			$("#modal-funbar .modal-header h3").text("添加快捷导航");
			$("#modal-funbar").find('#menuname').val('');
			$("#modal-funbar").find('#menucolor').val('#666666');
			$("#modal-funbar").find('#menulink').val(thisUrl());
			$("#modal-funbar").modal({
				backdrop: 'static',
				keyboard: false
			})
		});
		$("#funsearch").keyup(function() {
			var kw = $(this).val();
			if (kw) {
				$("#funbar-btns nav").each(function() {
					var _kw = $(this).find('span').eq(0).text();
					if (_kw.indexOf(kw) >= 0) {
						$(this).show()
					} else {
						$(this).hide()
					}
				})
			} else {
				$("#funbar-btns nav").show()
			}
		});
		if ($(".funbar.style2").length > 0) {
			var funbarTop = $(".funbar.style2").css('top').replace('px', '');
			$(window).bind('scroll resize load', function() {
				var top = $(window).scrollTop();
				var top = top + parseInt(funbarTop);
				$(".funbar.style2").animate({
					'top': top + 'px'
				}, 13)
			})
		}
	});

	function rgb2hex(rgb) {
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

		function hex(x) {
			return ("0" + parseInt(x).toString(16)).slice(-2)
		}
		return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3])
	}
	function saveFun() {
		var fundata = [];
		$("#funbar-btns nav").each(function() {
			var _href = $(this).data('href');
			var _text = $(this).find('span').eq(0).text();
			var _color = rgb2hex($(this).css('color'));
			if (_color == '#666666') {
				_color = ''
			}
			var _bold = 0;
			if ($(this).css('font-weight') == 'bold') {
				_bold = 1
			}
			fundata.push({
				'href': _href,
				'text': _text,
				'color': _color,
				'bold': _bold,
			})
		});
		$.post(biz.url('sysset/funbar'), {
			funbardata: fundata
		}, function(ret) {
			if (ret.status == 1) {
				tip.msgbox.suc("保存成功！")
			} else {
				tip.msgbox.err("保存失败请重试！")
			}
		}, 'json')
	}
	function thisUrl() {
		var str = location.protocol + "//" + location.hostname + "/web/";
		var url = window.location + '';
		return url.replace(str, "./")
	}
});