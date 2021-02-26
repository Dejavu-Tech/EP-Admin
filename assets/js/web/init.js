define(['jquery', 'bootstrap'], function($, bs) {
	window.redirect = function(url) {
		location.href = url
	}, $(document).on('click', '[data-toggle=refresh]', function(e) {
		e && e.preventDefault();
		var url = $(e.target).data("href");
		url ? window.location = url : window.location.reload()
	}), $(document).on('click', '[data-toggle=back]', function(e) {
		e && e.preventDefault();
		var url = $(e.target).data("href");
		url ? window.location = url : window.history.back()
	});

	function _bindCssEvent(events, callback) {
		var dom = this;

		function fireCallBack(e) {
			if (e.target !== this) {
				return
			}
			callback.call(this, e);
			for (var i = 0; i < events.length; i++) {
				dom.off(events[i], fireCallBack)
			}
		}
		if (callback) {
			for (var i = 0; i < events.length; i++) {
				dom.on(events[i], fireCallBack)
			}
		}
	}
	$.fn.animationEnd = function(callback) {
		_bindCssEvent.call(this, ['webkitAnimationEnd', 'animationend'], callback);
		return this
	};
	$.fn.transitionEnd = function(callback) {
		_bindCssEvent.call(this, ['webkitTransitionEnd', 'transitionend'], callback);
		return this
	};
	$.fn.transition = function(duration) {
		if (typeof duration !== 'string') {
			duration = duration + 'ms'
		}
		for (var i = 0; i < this.length; i++) {
			var elStyle = this[i].style;
			elStyle.webkitTransitionDuration = elStyle.MozTransitionDuration = elStyle.transitionDuration = duration
		}
		return this
	};
	$.fn.transform = function(transform) {
		for (var i = 0; i < this.length; i++) {
			var elStyle = this[i].style;
			elStyle.webkitTransform = elStyle.MozTransform = elStyle.transform = transform
		}
		return this
	};
	$.toQueryPair = function(key, value) {
		if (typeof value == 'undefined') {
			return key
		}
		return key + '=' + encodeURIComponent(value === null ? '' : String(value))
	};
	$.toQueryString = function(obj) {
		var ret = [];
		for (var key in obj) {
			key = encodeURIComponent(key);
			var values = obj[key];
			if (values && values.constructor == Array) {
				var queryValues = [];
				for (var i = 0, len = values.length, value; i < len; i++) {
					value = values[i];
					queryValues.push($.toQueryPair(key, value))
				}
				ret = concat(queryValues)
			} else {
				ret.push($.toQueryPair(key, values))
			}
		}
		return ret.join('&')
	};
	myrequire(['web/table']);
	myrequire(['jquery.gcjs']);
	myrequire(['web/tip']);
	myrequire(['tooltipbox']);
	if ($('form.form-validate').length > 0 || $('form.form-modal').length > 0) {
		myrequire(['web/form'], function(form) {
			form.init()
		})
	}
	myrequire(['web/biz']);
	if ($('.select2').length > 0) {
		myrequire(['select2'], function() {
			$('.select2').each(function() {
				$(this).select2({})
			})
		})
	}
	myrequire(['web/table']);
	if ($('.js-switch').length > 0) {
		myrequire(['switchery'], function() {
			$('.js-switch').switchery()
		})
	}
	if ($('.js-clip').length > 0) {
		myrequire(['clipboard'], function(Clipboard) {
			var clipboard = new Clipboard('.js-clip', {
				text: function(e) {
					return $(e).data('url') || $(e).data('href')
				}
			});
			clipboard.on('success', function(e) {
				tip.msgbox.suc('复制成功')
			})
		})
	}
	$.fn.append2 = function(html, callback) {
		var len = $("body").html().length;
		this.append(html);
		var e = 1,
			interval = setInterval(function() {
				e++;
				var clear = function() {
						clearInterval(interval);
						callback && callback()
					};
				if (len != $("body").html().length || e > 1000) {
					clear()
				}
			}, 1)
	};
	$('[data-toggle="popover"]').popover();
	$(document).on("click", '[data-toggle="ajaxModal"]', function(e) {
		e.preventDefault();
		var obj = $(this),
			confirm = obj.data("confirm");
		var handler = function() {
				$("#ajaxModal").remove(), e.preventDefault();
				var url = obj.data("href") || obj.attr("href"),
					data = obj.data("set"),
					modal;
				$.ajax(url, {
					type: "get",
					dataType: "html",
					cache: false,
					data: data
				}).done(function(html) {
					if (html.substr(0, 8) == '{"status') {
						json = eval("(" + html + ')');
						if (json.status == 0) {
							msg = typeof(json.result) == 'object' ? json.result.message : json.result;
							tip.msgbox.err(msg || tip.lang.err);
							return
						}
					}
					modal = $('<div class="modal fade" id="ajaxModal"><div class="modal-body "></div></div>');
					$(document.body).append(modal), modal.modal('show');
					myrequire(['jquery.gcjs'], function() {
						modal.append2(html, function() {
							var form_validate = $('form.form-validate', modal);
							if (form_validate.length > 0) {
								$("button[type='submit']", modal).length && $("button[type='submit']", modal).attr("disabled", true);
								myrequire(['web/form'], function(form) {
									form.init();
									$("button[type='submit']", modal).length && $("button[type='submit']", modal).removeAttr("disabled")
								})
							}
						})
					})
				})
			},
			a;
		if (confirm) {
			tip.confirm(confirm, handler)
		} else {
			handler()
		}
	}), $(document).on("click", '[data-toggle="ajaxPost"]', function(e) {
		e.preventDefault();
		var obj = $(this),
			confirm = obj.data("confirm"),
			url = obj.data('href') || obj.attr('href'),
			data = obj.data('set') || {},
			html = obj.html();
		handler = function() {
			e.preventDefault();
			if (obj.attr('submitting') == '1') {
				return
			}
			obj.html('<i class="fa fa-spinner fa-spin"></i>').attr('submitting', 1);
			$.post(url, {
				data: data
			}, function(ret) {
				ret = eval("(" + ret + ")");
				if (ret.status == 1) {
					tip.msgbox.suc(ret.result.message || tip.lang.success, ret.result.url)
				} else {
					tip.msgbox.err(ret.result.message || tip.lang.error, ret.result.url), obj.removeAttr('submitting').html(html)
				}
			}).fail(function() {
				obj.removeAttr('submitting').html(html), tip.msgbox.err(tip.lang.exception)
			})
		};
		confirm && tip.confirm(confirm, handler);
		!confirm && handler()
	}), $(document).on("click", '[data-toggle="ajaxEdit"]', function(e) {
		var obj = $(this),
			url = obj.data('href') || obj.attr('href'),
			data = obj.data('set') || {},
			html = $.trim(obj.text()),
			required = obj.data('required') || true,
			edit = obj.data('edit') || 'input';
		var oldval = $.trim($(this).text());
		e.preventDefault();
		submit = function() {
			e.preventDefault();
			var val = $.trim(input.val());
			if (required) {
				if (val == '') {
					tip.msgbox.err(tip.lang.empty);
					return
				}
			}
			if (val == html) {
				input.remove(), obj.html(val).show();
				return
			}
			if (url) {
				$.post(url, {
					value: val
				}, function(ret) {
					ret = eval("(" + ret + ")");
					if (ret.status == 1) {
						obj.html(val).show()
					} else {
						tip.msgbox.err(ret.result.message, ret.result.url)
					}
					input.remove()
				}).fail(function() {
					input.remove(), tip.msgbox.err(tip.lang.exception)
				})
			} else {
				input.remove();
				obj.html(val).show()
			}
			obj.trigger('valueChange', [val, oldval])
		}, obj.hide().html('<i class="fa fa-spinner fa-spin"></i>');
		var input = $('<input type="text" class="form-control input-sm" style="width: 80%;display: inline;" />');
		if (edit == 'textarea') {
			input = $('<textarea type="text" class="form-control" style="resize:none" rows=3 ></textarea>')
		}
		obj.after(input);
		input.val(html).select().blur(function() {
			submit(input)
		}).keypress(function(e) {
			if (e.which == 13) {
				submit(input)
			}
		})
	}), $(document).on("click", '[data-toggle="ajaxSwitch"]', function(e) {
		e.preventDefault();
		var obj = $(this),
			confirm = obj.data('msg') || obj.data('confirm'),
			othercss = obj.data('switch-css'),
			other = obj.data('switch-other'),
			refresh = obj.data('switch-refresh') || false;
		if (obj.attr('submitting') == '1') {
			return
		}
		var value = obj.data('switch-value'),
			value0 = obj.data('switch-value0'),
			value1 = obj.data('switch-value1');
		if (value === undefined || value0 === undefined || value1 === undefined) {
			return
		}
		var url, css, text, newvalue, newurl, newcss, newtext;
		value0 = value0.split('|');
		value1 = value1.split('|');
		if (value == value0[0]) {
			url = value0[3], css = value0[2], text = value0[1], newvalue = value1[0], newtext = value1[1], newcss = value1[2]
		} else {
			url = value1[3], css = value1[2], text = value1[1], newvalue = value0[0], newtext = value0[1], newcss = value0[2]
		}
		var html = obj.html();
		var submit = function() {
				$.post(url).done(function(data) {
					data = eval("(" + data + ")");
					if (data.status == 1) {
						if (other && othercss) {
							if (newvalue == '1') {
								$(othercss).each(function() {
									if ($(this).data('switch-value') == newvalue) {
										this.className = css;
										$(this).data('switch-value', value).html(text || html)
									}
								})
							}
						}
						obj.data('switch-value', newvalue);
						obj.html(newtext || html);
						obj[0].className = newcss;
						refresh && location.reload()
					} else {
						obj.html(html), tip.msgbox.err(data.result.message || tip.lang.error, data.result.url)
					}
					obj.removeAttr('submitting')
				}).fail(function() {
					obj.removeAttr('submitting');
					obj.button('reset');
					tip.msgbox.err(tip.lang.exception)
				})
			},
			a;
		if (confirm) {
			tip.confirm(confirm, function() {
				obj.html('<i class="fa fa-spinner fa-spin"></i>').attr('submitting', 1), submit()
			})
		} else {
			obj.html('<i class="fa fa-spinner fa-spin"></i>').attr('submitting', 1), submit()
		}
	});
	$(document).on('click', '[data-toggle="selectUrl"]', function() {
		$("#selectUrl").remove();
		var _input = $(this).data('input');
		var _full = $(this).data('full');
		var _platform = $(this).data('platform');
		var _callback = $(this).data('callback') || false;
		var _cbfunction = !_callback ? false : eval("(" + _callback + ")");
		if (!_input && !_callback) {
			return
		}
		var merch = $(".diy-phone").data("merch");
		var url = biz.url('util/selecturl', null, merch);
		var store = $(".diy-phone").data("store");
		if (store) {
			url = biz.url('store/diypage/selecturl')
		}
		if (_full) {
			url = url + "&full=1"
		}
		if (_platform) {
			url = url + "&platform=" + _platform
		}
		$.ajax('seller.php?s=/util/selecturl', {
			type: "get",
			dataType: "html",
			cache: false
		}).done(function(html) {
			modal = $('<div class="modal fade" id="selectUrl"></div>');
			$(document.body).append(modal), modal.modal('show');
			modal.append2(html, function() {
				$(document).off("click", '#selectUrl nav').on("click", '#selectUrl nav', function() {
					var _href = $.trim($(this).data("href"));
					if (_input) {
						$(_input).val(_href).trigger('change')
					} else if (_cbfunction) {
						_cbfunction(_href)
					}
					modal.find(".close").click()
				})
			})
		})
	});
	$(document).on('click', '[data-toggle="selectImg"]', function() {
		var _input = $(this).data('input');
		var _img = $(this).data('img');
		var _full = $(this).data('full');
		require(['jquery', 'util'], function($, util) {
			util.image('', function(data) {
				var imgurl = data.attachment;
				if (_full) {
					imgurl = data.url
				}
				if (_input) {
					$(_input).val(imgurl).trigger('change')
				}
				if (_img) {
					$(_img).attr('src', data.url)
				}
			})
		})
	});
	$(document).on('click', '[data-toggle="selectIcon"]', function() {
		var _input = $(this).data('input');
		var _element = $(this).data('element');
		if (!_input && !_element) {
			return
		}
		var merch = $(".diy-phone").data("merch");
		var url = biz.url('util/selecticon', null, merch);
		$.ajax(url, {
			type: "get",
			dataType: "html",
			cache: false
		}).done(function(html) {
			modal = $('<div class="modal fade" id="selectIcon"></div>');
			$(document.body).append(modal), modal.modal('show');
			modal.append2(html, function() {
				$(document).off("click", '#selectIcon nav').on("click", '#selectIcon nav', function() {
					var _class = $.trim($(this).data("class"));
					if (_input) {
						$(_input).val(_class).trigger('change')
					}
					if (_element) {
						$(_element).removeAttr("class").addClass("icon " + _class)
					}
					modal.find(".close").click()
				})
			})
		})
	});
	$(document).on('click', '[data-toggle="selectAudio"]', function() {
		var _input = $(this).data('input');
		var _full = $(this).data('full');
		require(['jquery', 'util'], function($, util) {
			util.audio('', function(data) {
				var audiourl = data.attachment;
				if (_full) {
					audiourl = data.url
				}
				if (_input) {
					$(_input).val(audiourl).trigger('change')
				}
			})
		})
	});
	$(document).on('click', '[data-toggle="selectVideo"]', function() {
		var _input = $(this).data('input');
		var _full = $(this).data('full');
		require(['jquery', 'util'], function($, util) {
			util.audio('', function(data) {
				var audiourl = data.attachment;
				if (_full) {
					audiourl = data.url
				}
				if (_input) {
					$(_input).val(audiourl).trigger('change')
				}
			}, {
				type: 'video'
			})
		})
	});
	$(document).on('click', '[data-toggle="previewVideo"]', function() {
		var videoelm = $(this).data('input');
		if (!videoelm) {
			return
		}
		var video = $(videoelm).data('url');
		if (!video || video == '') {
			tip.msgbox.err('未选择视频');
			return
		}
		if ($('#previewVideo').length < 1) {
			$('body').append('<div class="modal fade" id="previewVideo"><div class="modal-dialog" style="min-width: 400px !important;"><div class="modal-content"><div class="modal-header"><button data-dismiss="modal" class="close" type="button">×</button><h4 class="modal-title">视频预览</h4></div><div class="modal-body" style="padding: 0; background: #000;"><video src="' + video + '" style="height: 450px; width: 100%; display: block;" controls="controls"></video></div></div></div></div>')
		} else {
			$("#previewVideo video").attr("src", video)
		}
		$("#previewVideo").modal();
		$("#previewVideo").on("hidden.bs.modal", function() {
			$(this).find("video")[0].pause()
		})
	});
	$(window).resize(function() {
		var width = $(window).width();
		if (width <= 1440) {
			$(".wb-panel-fold").removeClass('in').html('<i class="icow icow-info"></i> 消息提醒');
			$(".wb-panel").removeClass('in');
			$('.wb-container').addClass('right-panel')
		} else {
			$(".wb-panel-fold").addClass('in').html('<i class="fa fa-angle-double-right"></i> 收起面板');
			$(".wb-panel").addClass('in');
			$('.wb-container').removeClass('right-panel')
		}
	});
	$(window).scroll(function() {
		if ($(window).scrollTop() > 200) {
			$('.fixed-header').addClass('active')
		} else {
			$('.fixed-header').removeClass('active')
		}
	});
	$('.wb-nav-fold').click(function() {
		var nav = $(this).closest(".wb-nav");
		if (nav.hasClass('fold')) {
			nav.removeClass('fold');
			$(".wb-header .logo").removeClass('small');
			$(".fast-nav").removeClass('indent');
			util.cookie.set('foldnav', 0)
		} else {
			nav.addClass('fold');
			$(".wb-header .logo").addClass('small');
			$(".fast-nav").addClass('indent');
			util.cookie.set('foldnav', 1)
		}
	});
	$('.wb-subnav-fold').click(function() {
		var subnav = $(this).closest(".wb-subnav");
		if (subnav.hasClass('fold')) {
			subnav.removeClass('fold')
		} else {
			subnav.addClass('fold')
		}
	});
	$('.menu-header').click(function() {
		if ($(this).hasClass('active')) {
			$(this).next('ul').eq(0).hide();
			$(this).find('.menu-icon').removeClass('fa-caret-down').addClass('fa-caret-right');
			$(this).removeClass('active')
		} else {
			$(this).next('ul').eq(0).show();
			$(this).find('.menu-icon').removeClass('fa-caret-right').addClass('fa-caret-down');
			$(this).addClass('active')
		}
	});
	$('.wb-header-btn').click(function() {
		if ($('.wb-topbar-search').hasClass('expand-search')) {
			$('.wb-search-box').focus();
			var keyword = $.trim($(".wb-search-box").val());
			if (keyword != '') {
				location.href = './index.php?c=site&a=entry&m=eaterplanet_ecommerce&do=admin&r=search&keyword=' + keyword;
				return
			}
		} else {}
	});
	$(".wb-search-box").bind('input propertychange', function() {
		var keyword = $.trim($(this).val());
		var merch = $(this).data('merch') || 0;
		if (keyword == '') {
			$('.wb-search-result ul').empty();
			$('.wb-search-result').hide();
			$(".wb-search-box").val('');
			return
		}
		$.getJSON(biz.url('searchlist', null, merch), {
			keyword: keyword
		}, function(ret) {
			var result = ret.result;
			var html = '';
			$('.wb-search-result ul').empty();
			if (result.menu.length < 1) {
				html = '<li class="empty-data"><a>暂未搜索到与“' + keyword + '”相关功能</a></li>'
			} else {
				$.each(result.menu, function(index, menu) {
					html += '<li><a href="' + menu.url + '">' + menu.title + '</a></li>'
				})
			}
			$('.wb-search-result ul').html(html);
			$('.wb-search-result').show()
		})
	});
	$(".wb-header-logout").click(function() {
		var href = $(this).closest('li').data('href');
		tip.confirm("当前已登录，确认退出？", function() {
			location.href = href
		})
	});
	$(".wb-panel-fold").click(function() {
		$(this).toggleClass('in');
		$(".wb-panel").toggleClass('in');
		if (!$(this).hasClass('in')) {
			$(this).html('<i class="icow icow-info"></i> 消息提醒');
			util.cookie.set('foldpanel', 1);
			$('.wb-container').addClass('right-panel')
		} else {
			$(this).html('<i class="fa fa-angle-double-right"></i> 收起面板');
			util.cookie.set('foldpanel', 0);
			$('.wb-container').removeClass('right-panel')
		}
	});
	$(".wb-shortcut").click(function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$(".fast-nav").removeClass('in')
		} else {
			$(this).addClass('active');
			$(".fast-nav").addClass('in')
		}
	});
	$(".fast-list.menu a").hover(function() {
		$(this).addClass('active').siblings().removeClass('active');
		var tab = $(this).data('tab');
		$(".fast-list.list [data-tab='" + tab + "']").addClass('in').siblings('.in').removeClass('in');
		$(".funbar-panel").hide();
		$("#funbar-name").val('');
		$("#funbar-color").val('#666666')
	});
	$(".funbar-add-btn").click(function() {
		$(".funbar-panel").show();
		$("#funbar-bold-0").prop('checked', 'checked')
	});
	$(".funbar-cancel-btn").click(function() {
		$(".funbar-panel").hide();
		$("#funbar-name").val('');
		$("#funbar-color").val('#666666')
	});
	$(".funbar-save-btn").click(function() {
		var name = $.trim($("#funbar-name").val());
		if (name == '') {
			tip.msgbox.err('请输入导航名称');
			return
		}
		var color = $("#funbar-color").val();
		var bold = $("#funbar-bold-1").is(':checked') ? 1 : 0;
		var link = $("#funbar-link").val();
		var fundata = {
			href: link,
			text: name,
			color: color,
			bold: bold
		};
		$.post(biz.url('sysset/funbar/post'), {
			funbardata: fundata
		}, function(ret) {
			if (ret.status == 1) {
				var html = '<a href="' + link + '" style=" ';
				if (bold == 1) {
					html += 'font-weight: bold;'
				}
				if (color != '#666666') {
					html += 'color: ' + color + ';'
				}
				html += '">' + name + '</a>';
				$("#funbar-list").prepend(html);
				$(".funbar-panel").hide();
				$("#funbar-name").val('');
				$("#funbar-color").val('#666666')
			} else {
				tip.msgbox.err("保存失败请重试！")
			}
		}, 'json')
	});
	$("#btn-clear-history").click(function() {
		var merch = $(this).data('merch') || 0;
		tip.confirm("确认清除最近访问吗？", function() {
			$.post(biz.url('clearhistory', null, merch), {
				type: 0
			}, function(ret) {
				$(".fast-list.history").remove()
			})
		})
	});
	$(document).click(function(e) {
		var btn1 = $(e.target).closest('.wb-shortcut').length;
		if (!btn1) {
			var fastNav = $(e.target).closest('.fast-nav').length;
			if (!fastNav) {
				$(".wb-shortcut").removeClass('active');
				$(".fast-nav").removeClass('in')
			}
		}
	});
	if ($(".form-editor-group").length > 0) {
		$(".form-editor-group .form-editor-btn").click(function() {
			var editor = $(this).closest(".form-editor-group");
			editor.find(".form-editor-show").hide();
			editor.find(".form-editor-edit").css('display', 'table')
		});
		$(".form-editor-group .form-editor-finish").click(function() {
			if ($(this).closest(".form-group").hasClass("has-error")) {
				return
			}
			var editor = $(this).closest(".form-editor-group");
			editor.find(".form-editor-show").show();
			editor.find(".form-editor-edit").hide();
			var input = editor.find(".form-editor-input");
			var value = $.trim(input.val());
			editor.find(".form-editor-text").text(value)
		})
	}
	$("img").error(function() {
		$(this).attr('src', '/assets/ep/images/nopic.png')
	})
});
