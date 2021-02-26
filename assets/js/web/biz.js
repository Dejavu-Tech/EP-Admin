define(['jquery'], function($) {
	var biz = {};
	biz.url = function(routes, params, merch) {
		if (merch) {
			var url = './merchant.php?c=site&a=entry&m=eaterplanet_ecommerce&do=admin&controller=' + routes.replace(/\//ig, '.')
		} else {
			var url = './index.php?c=site&a=entry&m=eaterplanet_ecommerce&do=admin&controller=' + routes.replace(/\//ig, '.')
		}
		if (params) {
			if (typeof(params) == 'object') {
				url += "&" + $.toQueryString(params)
			} else if (typeof(params) == 'string') {
				url += "&" + params
			}
		}
		return url
	};
	biz.selector = {
		select: function(params) {
			params = $.extend({}, params || {});
			var name = params.name === undefined ? 'default' : params.name;
			var modalid = name + "-selector-modal";
			modalObj = $('#' + modalid);
			if (modalObj.length <= 0) {
				var modal = '<div id="' + modalid + '"  class="modal fade" tabindex="-1">';
				modal += '<div class="modal-dialog" style="width: 920px;">';
				modal += '<div class="modal-content">';
				modal += '<div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h3>数据选择器</h3></div>';
				modal += '<div class="modal-body" >';
				modal += '<div class="row">';
				modal += '<div class="input-group">';
				modal += '<input type="text" class="form-control" name="keyword" id="' + name + '_input" placeholder="' + (params.placeholder === undefined ? '' : params.placeholder) + '" />';
				modal += '<span class="input-group-btn"><button type="button" class="btn btn-primary" onclick="biz.selector.search(this, \'' + name + '\');">搜索</button></span>';
				modal += '</div>';
				modal += '</div>';
				modal += '<div class="content" style="padding-top:5px;" data-name="' + name + '"></div>';
				modal += '</div>';
				modal += '<div class="modal-footer"><a href="#" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">关闭</a></div>';
				modal += '</div>';
				modal += '</div>';
				modal += '</div>';
				modalObj = $(modal);
				modalObj.on('show.bs.modal', function() {
					if (params.autosearch == '1') {
						$.get(params.url, {
							keyword: ''
						}, function(dat) {
							$('.content', modalObj).html(dat)
						})
					}
				})
			};
			modalObj.modal('show')
		},
		search: function(searchbtn, name) {
			var input = $(searchbtn).closest('.modal').find('#' + name + '_input');
			var selector = $("#" + name + '_selector');
			var needkeywords = true;
			if (selector.data('nokeywords') == '1') {
				needkeywords = false
			};
			var keyword = $.trim(input.val());
			if (keyword == '' && needkeywords) {
				input.focus();
				return
			}
			var modalObj = $('#' + name + "-selector-modal");
			$('.content', modalObj).html("正在搜索....");
			$.get(selector.data('url'), {
				keyword: keyword
			}, function(dat) {
				$('.content', modalObj).html(dat)
			})
		},
		remove: function(obj, name) {
			var selector = $("#" + name + '_selector');
			var css = selector.data('type') == 'image' ? '.multi-item' : '.multi-audio-item';
			if (selector.data('type') == 'image') {
				css = ".multi-item"
			} else if (selector.data('type') == 'coupon') {
				css = ".multi-product-item"
			} else if (selector.data('type') == 'coupon_cp') {
				css = ".multi-product-item"
			} else if (selector.data('type') == 'coupon_share') {
				css = ".multi-product-item"
			} else if (selector.data('type') == 'coupon_shares') {
				css = ".multi-product-item"
			} else {
				css = ".multi-audio-item"
			}
			$(obj).closest(css).remove();
			biz.selector.refresh(name)
		},
		set: function(obj, data) {
			var name = $(obj).closest('.content').data('name');
			var modalObj = $('#' + name + "-selector-modal");
			var selector = $('#' + name + "_selector");
			var container = $('.container', selector);
			var key = selector.data('key') || 'id',
				text = selector.data('text') || 'title',
				thumb = selector.data('thumb') || 'thumb',
				multi = selector.data('multi') || 0,
				type = selector.data('type') || 'image',
				callback = selector.data('callback') || '',
				css = type == 'image' ? '.multi-item' : '.multi-audio-item';
			if ($(css + '[data-' + key + '="' + data[key] + '"]', container).length > 0) {
				if (multi === 0) {
					modalObj.modal('hide')
				}
				return
			}
			if (type == 'coupon_cp') {
				if ($(".setticket").length >= 3) {
					tip.msgbox.err('您已经选择了三张优惠券，若要更换请删除其他优惠券！');
					return
				}
			}
			if (type == 'coupon_share') {
				if ($(".shareticket").length >= 3) {
					tip.msgbox.err('您已经选择了三张优惠券，若要更换请删除其他优惠券！');
					return
				}
			}
			if (type == 'coupon_shares') {
				if ($(".sharesticket").length >= 3) {
					tip.msgbox.err('您已经选择了三张优惠券，若要更换请删除其他优惠券！');
					return
				}
			}
			var id = multi === 0 ? name : name + "[]";
			var html = "";
			if (type == 'image') {
				html += '<div class="multi-item" data-' + key + '="' + data[key] + '" data-name="' + name + '">';
				html += '<img class="img-responsive img-thumbnail" src="' + data[thumb] + '" >';
				html += '<div class="img-nickname">' + data[text] + '</div>';
				html += '<input type="hidden" value="' + data[key] + '" name="' + id + '">';
				html += '<em onclick="biz.selector.remove(this,\'' + name + '\')"  class="close">×</em>';
				html += '</div>'
			} else if (type == 'coupon') {
				html += "<tr class='multi-product-item' data-" + key + "='" + data[key] + "'>";
				html += "<input type='hidden' class='form-control img-textname' readonly='' value='" + data[text] + "'>";
				html += "<input type='hidden' value='" + data[key] + "' name='couponid[]'>";
				html += "<td style='width:80px;'><img src='" + data[thumb] + "' style='width:70px;border:1px solid #ccc;padding:1px'></td>";
				html += "<td style='width:220px;'>" + data[text] + "</td>";
				html += "<td><input class='form-control valid' type='text' value='' name='coupontotal" + data[key] + "'></td>";
				html += "<td><input class='form-control valid' type='text' value='' name='couponlimit" + data[key] + "'></td>";
				html += "<td style='width:80px;'><button class='btn btn-primary' onclick='biz.selector.remove(this,\"" + name + "\")' type='button'><i class='fa fa-remove'></i></button></td></tr>"
			} else if (type == 'coupon_cp') {
				html += "<tr class='multi-product-item setticket' data-" + key + "='" + data[key] + "'>";
				html += "<input type='hidden' class='form-control img-textname' readonly='' value='" + data[text] + "'>";
				html += "<input type='hidden' value='" + data[key] + "' name='couponid[]'>";
				html += "<td style='width:80px;'><img src='" + data[thumb] + "' style='width:70px;border:1px solid #ccc;padding:1px'></td>";
				html += "<td style='width:220px;'>" + data[text] + "</td>";
				html += "<td></td>";
				html += "<td></td>";
				html += "<td style='width:80px;'><button class='btn btn-primary' onclick='biz.selector.remove(this,\"" + name + "\")' type='button'><i class='fa fa-remove'></i></button></td></tr>"
			} else if (type == 'coupon_share') {
				html += "<tr class='multi-product-item shareticket' data-" + key + "='" + data[key] + "'>";
				html += "<input type='hidden' class='form-control img-textname' readonly='' value='" + data[text] + "'>";
				html += "<input type='hidden' value='" + data[key] + "' name='couponid[]'>";
				html += "<td style='width:80px;'><img src='" + data[thumb] + "' style='width:70px;border:1px solid #ccc;padding:1px'></td>";
				html += "<td style='width:220px;'>" + data[text] + "</td>";
				html += "<td></td>";
				html += "<td><input class='form-control valid' type='text' value='1' name='couponnum" + data[key] + "'></td>";
				html += "<td style='width:80px;'><button class='btn btn-primary' onclick='biz.selector.remove(this,\"" + name + "\")' type='button'><i class='fa fa-remove'></i></button></td></tr>"
			} else if (type == 'coupon_shares') {
				html += "<tr class='multi-product-item sharesticket' data-" + key + "='" + data[key] + "'>";
				html += "<input type='hidden' class='form-control img-textname' readonly='' value='" + data[text] + "'>";
				html += "<input type='hidden' value='" + data[key] + "' name='couponids[]'>";
				html += "<td style='width:80px;'><img src='" + data[thumb] + "' style='width:70px;border:1px solid #ccc;padding:1px' class='img_share'></td>";
				html += "<td style='width:220px;'>" + data[text] + "</td>";
				html += "<td></td>";
				html += "<td><input class='form-control valid' type='text' value='1' name='couponsnum" + data[key] + "'></td>";
				html += "<td style='width:80px;'><button class='btn btn-primary' onclick='biz.selector.remove(this,\"" + name + "\")' type='button'><i class='fa fa-remove'></i></button></td></tr>"
			} else {
				html += "<div class='multi-audio-item' data-" + key + "='" + data[key] + "' data-name='" + name + "'>";
				html += "<div class='input-group'><input type='hidden' name='" + id + "' value='" + data[key] + "'> ";
				html += "<input type='text' class='form-control img-textname' readonly='' value='" + data[text] + "'>";
				html += "<div class='input-group-btn'><button class='btn btn-primary' onclick='biz.selector.remove(this,\"" + name + "\")' type='button'><i class='fa fa-remove'></i></button></div></div></div>"
			}
			if (multi === 0) {
				container.html(html);
				modalObj.modal('hide')
			} else {
				container.append(html)
			}
			biz.selector.refresh(name);
			if (callback !== '') {
				var callfunc = eval(callback);
				if (callfunc !== undefined) {
					callfunc(data, obj)
				}
			}
		},
		refresh: function(name) {
			var titles = '';
			var selector = $('#' + name + '_selector');
			var type = selector.data('type') || 'image';
			if (type == 'image') {
				$('.multi-item', selector).each(function() {
					titles += " " + $(this).find('.img-nickname').html();
					if ($('.multi-item', selector).length > 1) {
						titles += "; "
					}
				})
			} else if (type == 'coupon') {
				$('.multi-product-item', selector).each(function() {
					titles += " " + $(this).find('.img-textname').val();
					if ($('.multi-product-item', selector).length > 1) {
						titles += "; "
					}
				})
			} else if (type == 'coupon_cp') {
				$('.multi-product-item', selector).each(function() {
					titles += " " + $(this).find('.img-textname').val();
					if ($('.multi-product-item', selector).length > 1) {
						titles += "; "
					}
				})
			} else if (type == 'coupon_share') {
				$('.multi-product-item', selector).each(function() {
					titles += " " + $(this).find('.img-textname').val();
					if ($('.multi-product-item', selector).length > 1) {
						titles += "; "
					}
				})
			} else if (type == 'coupon_shares') {
				$('.multi-product-item', selector).each(function() {
					titles += " " + $(this).find('.img-textname').val();
					if ($('.multi-product-item', selector).length > 1) {
						titles += "; "
					}
				})
			} else {
				$('.multi-audio-item', selector).each(function() {
					titles += " " + $(this).find('.img-textname').val();
					if ($('.multi-audio-item', selector).length > 1) {
						titles += "; "
					}
				})
			}
			$('#' + name + "_text", selector).val(titles)
		}
	};
	biz.selector_new = {
		select: function(params) {
			params = $.extend({}, params || {});
			var name = params.name === undefined ? 'default' : params.name;
			var modalid = name + "-selector-modal";
			modalObj = $('#' + modalid);
			if (modalObj.length <= 0) {
				var modal = '<div id="' + modalid + '"  class="modal fade" tabindex="-1">';
				modal += '<div class="modal-dialog" style="width: 920px;">';
				modal += '<div class="modal-content">';
				modal += '<div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h3>数据选择器</h3></div>';
				modal += '<div class="modal-body" >';
				modal += '<div class="row">';
				modal += '<div class="input-group">';
				modal += '<input type="text" class="form-control" name="keyword" id="' + name + '_input" placeholder="' + (params.placeholder === undefined ? '' : params.placeholder) + '" />';
				modal += '<span class="input-group-btn"><button type="button" class="btn btn-primary" onclick="biz.selector_new.search(this, \'' + name + '\');">搜索</button></span>';
				modal += '</div>';
				modal += '</div>';
				modal += '<div class="content" style="padding-top:5px;" data-name="' + name + '"></div>';
				modal += '</div>';
				modal += '<div class="modal-footer"><a href="#" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">关闭</a></div>';
				modal += '</div>';
				modal += '</div>';
				modal += '</div>';
				modalObj = $(modal);
				modalObj.on('show.bs.modal', function() {
					if (params.autosearch == '1') {
						$.get(params.url, {
							keyword: ''
						}, function(dat) {
							$('.content', modalObj).html(dat)
						})
					}
				})
			};
			modalObj.modal('show')
		},
		search: function(searchbtn, name) {
			var input = $(searchbtn).closest('.modal').find('#' + name + '_input');
			var selector = $("#" + name + '_selector');
			var needkeywords = true;
			if (selector.data('nokeywords') == '1') {
				needkeywords = false
			};
			var keyword = $.trim(input.val());
			if (keyword == '' && needkeywords) {
				input.focus();
				return
			}
			var modalObj = $('#' + name + "-selector-modal");
			$('.content', modalObj).html("正在搜索....");
			$.get(selector.data('url'), {
				keyword: keyword
			}, function(dat) {
				$('.content', modalObj).html(dat)
			})
		},
		remove: function(obj, name) {
			var selector = $("#" + name + '_selector');
			var css = selector.data('type') == 'image' ? '.multi-item' : '.multi-product-item';
			$(obj).closest(css).remove();
			biz.selector_new.refresh(name)
		},
		set: function(obj, data) {
			var name = $(obj).closest('.content').data('name');
			var modalObj = $('#' + name + "-selector-modal");
			var selector = $('#' + name + "_selector");
			var key = selector.data('key') || 'id',
				text = selector.data('text') || 'title',
				thumb = selector.data('thumb') || 'thumb',
				multi = selector.data('multi') || 0,
				type = selector.data('type') || 'image',
				callback = selector.data('callback') || '',
				css = type == 'image' ? '.multi-item' : '.multi-product-item',
				optionurl = selector.data('optionurl') || '',
				selectorid = selector.data('selectorid') || '';
			var container = $('.container', selector);
			if ($(css + '[data-' + key + '="' + data[key] + '"]', container).length > 0) {
				if (multi === 0) {
					modalObj.modal('hide')
				}
				return
			}
			var id = multi === 0 ? name : name + "[]";
			var html = "";
			if (type == 'image') {
				html += '<div class="multi-item" data-' + key + '="' + data[key] + '" data-name="' + name + '">';
				html += '<img class="img-responsive img-thumbnail" src="' + data[thumb] + '" >';
				html += '<div class="img-nickname">' + data[text] + '</div>';
				html += '<input type="hidden" value="' + data[key] + '" name="' + id + '">';
				html += '<em onclick="biz.selector_new.remove(this,\'' + name + '\')"  class="close">×</em>';
				html += '</div>'
			} else if (type == 'product') {
				var optionurl = optionurl == '' ? 'sale.package.hasoption' : optionurl;
				var url = "index.php?c=site&a=entry&m=eaterplanet_ecommerce&do=admin&controller=" + optionurl + "&goodsid=" + data[key] + "&selectorid=" + selectorid;
				html += '<tr class="multi-product-item" data-' + key + '="' + data[key] + '" data-name="' + name + '">';
				html += "<input type='hidden' name='" + id + "' value='" + data[key] + "'> ";
				html += "<input type='hidden' class='form-control img-textname' value='" + data[text] + "'>";
				html += '<td style="width:80px;"><img src="' + data[thumb] + '" style="width:70px;border:1px solid #ccc;padding:1px" /></td>';
				html += '<td style="width:220px;">' + data[text] + '</td>';
				html += "<td><a class='btn btn-primary btn-sm' data-toggle='ajaxModal' href='" + url + "' id='" + selectorid + "optiontitle" + data[key] + "'>设置</a>" + "<input type='hidden' id='" + selectorid + "packagegoods" + data[key] + "' value='' name='" + selectorid + "packagegoods[" + data[key] + "]'></td>";
				html += '<td><a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="biz.selector_new.remove(this,\'' + name + '\')" title="删除">';
				html += '<i class="fa fa-times"></i></a></td></tr>'
			} else if (type == 'fullback') {
				var optionurl = optionurl == '' ? 'sale.fullback.hasoption' : optionurl;
				var url = "index.php?c=site&a=entry&m=eaterplanet_ecommerce&do=admin&controller=" + optionurl + "&goodsid=" + data[key] + "&selectorid=" + selectorid;
				html += '<tr class="multi-product-item" data-' + key + '="' + data[key] + '" data-name="' + name + '">';
				html += "<input type='hidden' name='" + id + "' value='" + data[key] + "'> ";
				html += "<input type='hidden' class='form-control img-textname' value='" + data[text] + "'>";
				html += '<td style="width:80px;"><img src="' + data[thumb] + '" style="width:70px;border:1px solid #ccc;padding:1px" /></td>';
				html += '<td style="width:220px;">' + data[text] + '</td>';
				html += "<td><a class='btn btn-primary btn-sm' data-toggle='ajaxModal' href='" + url + "' id='" + selectorid + "optiontitle" + data[key] + "'>设置</a>" + "<input type='hidden' id='" + selectorid + "fullbackgoods" + data[key] + "' value='' name='" + selectorid + "fullbackgoods[" + data[key] + "]'></td>";
				html += '<td><a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="biz.selector_new.remove(this,\'' + name + '\')" title="删除">';
				html += '<i class="fa fa-times"></i></a></td></tr>'
			} else if (type == 'live') {
				var optionurl = optionurl == '' ? 'live.room.hasoption' : optionurl;
				var url = "index.php?c=site&a=entry&m=eaterplanet_ecommerce&do=admin&controller=" + optionurl + "&goodsid=" + data[key] + "&selectorid=" + selectorid;
				html += '<tr class="multi-product-item" data-' + key + '="' + data[key] + '" data-name="' + name + '">';
				html += "<input type='hidden' name='" + id + "' value='" + data[key] + "'> ";
				html += "<input type='hidden' class='form-control img-textname' value='" + data[text] + "'>";
				html += '<td style="width:80px;"><img src="' + data[thumb] + '" style="width:70px;border:1px solid #ccc;padding:1px" /></td>';
				html += '<td style="width:220px;">' + data[text] + '</td>';
				html += "<td><a class='btn btn-primary btn-sm' data-toggle='ajaxModal' href='" + url + "' id='" + selectorid + "optiontitle" + data[key] + "'>设置</a>" + "<input type='hidden' id='" + selectorid + "livegoods" + data[key] + "' value='' name='" + selectorid + "livegoods[" + data[key] + "]'></td>";
				html += '<td><a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="biz.selector_new.remove(this,\'' + name + '\')" title="删除">';
				html += '<i class="fa fa-times"></i></a></td></tr>'
			} else {
				html += "<div class='111 multi-audio-item' data-" + key + "='" + data[key] + "' data-name='" + name + "'>";
				html += "<div class='input-group'><input type='hidden' name='" + id + "' value='" + data[key] + "'> ";
				html += "<input type='text' class='form-control img-textname' readonly='' value='" + data[text] + "'>";
				html += "<div class='input-group-btn'><button class='btn btn-primary' onclick='biz.selector_new.remove(this,\"" + name + "\")' type='button'>" + "<i class='fa fa-remove'></i></button></div></div></div>"
			}
			if (multi === 0) {
				container.html(html);
				modalObj.modal('hide')
			} else {
				$("#param-items" + selectorid).append(html)
			}
			biz.selector_new.refresh(name);
			if (callback !== '') {
				var callfunc = eval(callback);
				if (callfunc !== undefined) {
					callfunc(data, obj)
				}
			}
		},
		refresh: function(name) {
			var titles = '';
			var selector = $('#' + name + '_selector');
			var type = selector.data('type') || 'image';
			if (type == 'image') {
				$('.multi-item', selector).each(function() {
					titles += " " + $(this).find('.img-nickname').html();
					if ($('.multi-item', selector).length > 1) {
						titles += "; "
					}
				})
			} else {
				$('.multi-product-item', selector).each(function() {
					titles += " " + $(this).find('.img-textname').val();
					if ($('.multi-product-item', selector).length > 1) {
						titles += "; "
					}
				})
			}
			$('#' + name + "_text", selector).val(titles)
		}
	};
	biz.selector_open = {
		callback: function() {},
		select: function(params) {
			params = $.extend({}, params || {});
			biz.selector_open.callback = typeof(params.callback) === 'undefined' ? false : params.callback;
			biz.selector_open.params = params;
			var name = params.name === undefined ? 'default' : params.name;
			var modalid = name + "-selector-modal";
			modalObj = $('#' + modalid);
			if (modalObj.length <= 0) {
				var modal = '<div id="' + modalid + '"  class="modal fade" tabindex="-1">';
				modal += '<div class="modal-dialog" style="width: 920px;">';
				modal += '<div class="modal-content">';
				modal += '<div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h3>数据选择器</h3></div>';
				modal += '<div class="modal-body" >';
				modal += '<div class="row">';
				modal += '<div class="input-group">';
				modal += '<input type="text" class="form-control" name="keyword" id="' + name + '_input" placeholder="' + (params.placeholder === undefined ? '' : params.placeholder) + '" />';
				modal += '<span class="input-group-btn"><button type="button" class="btn btn-primary" onclick="biz.selector_open.search(this, \'' + name + '\');">搜索</button></span>';
				modal += '</div>';
				modal += '</div>';
				modal += '<div class="content" style="padding-top:5px;" data-name="' + name + '"></div>';
				modal += '</div>';
				modal += '<div class="modal-footer"><a href="#" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">关闭</a></div>';
				modal += '</div>';
				modal += '</div>';
				modal += '</div>';
				modalObj = $(modal);
				modalObj.on('show.bs.modal', function() {
					if (params.autosearch == '1') {
						$.get(params.url, {
							keyword: ''
						}, function(dat) {
							$('.content', modalObj).html(dat)
						})
					}
				})
			};
			modalObj.modal('show')
		},
		search: function(searchbtn, name) {
			var input = $(searchbtn).closest('.modal').find('#' + name + '_input');
			var selector = $("#" + name + '_selector');
			var needkeywords = true;
			var params = biz.selector_open.params;
			if (params.nokeywords == '1') {
				needkeywords = false
			};
			var keyword = $.trim(input.val());
			if (keyword == '' && needkeywords) {
				input.focus();
				return
			}
			var modalObj = $('#' + name + "-selector-modal");
			$('.content', modalObj).html("正在搜索....");
			$.get(params.url, {
				keyword: keyword
			}, function(dat) {
				$('.content', modalObj).html(dat)
			})
		},
		remove: function(obj, name) {
			var params = biz.selector_open.params;
			var css = params.type == 'image' ? '.multi-item' : '.multi-audio-item';
			$(obj).closest(css).remove();
			biz.selector_open.refresh(name)
		},
		set: function(obj, data) {
			var name = $(obj).closest('.content').data('name');
			var modalObj = $('#' + name + "-selector-modal");
			var selector = $('#' + name + "_selector");
			var params = biz.selector_open.params;
			var multi = params.multi || 0;
			if (multi === 0) {
				modalObj.modal('hide')
			}
			if (typeof(biz.selector_open.callback) === 'function') {
				biz.selector_open.callback(data, obj)
			}
		}
	};
	biz.map = function(val, callback, tpl) {
		var modalobj = $('#map-dialog');
		if (modalobj.length === 0) {
			var content = '<div class="embed-responsive embed-responsive-16by9">' + '<iframe  class="embed-responsive-item" src="' + tpl + '" scrolling="no"></iframe>' + '</div>';
			var footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">取消</button>' + '<button type="button" class="btn btn-primary">确认</button>';
			modalobj = util.dialog('请选择地点', content, footer, {
				containerName: 'map-dialog'
			});
			modalobj.find('.modal-dialog').css('width', '80%');
			modalobj.modal({
				'keyboard': false
			});
			modalobj.find('.input-group :text').keydown(function(e) {
				if (e.keyCode == 13) {
					var kw = $(this).val();
					searchAddress(kw)
				}
			});
			modalobj.find('.input-group button').click(function() {
				var kw = $(this).parent().prev().val();
				searchAddress(kw)
			})
		}
		modalobj.find('button.btn-primary').off('click');
		modalobj.find('button.btn-primary').on('click', function() {
			if ($.isFunction(callback)) {
				var $point = modalobj.find("iframe").contents().find("#poi_json").val();
				if ($.isEmpty($point)) {
					tip.msgbox.err('尚未选择坐标!');
					return
				}
				var point = JSON.parse(modalobj.find("iframe").contents().find("#poi_json").val());
				var address = modalobj.find("iframe").contents().find("#addr_cur").val();
				var val = {
					lng: point.lng,
					lat: point.lat,
					label: address
				};
				callback(val)
			}
			modalobj.modal('hide')
		});
		modalobj.modal('show')
	};
	biz.TxMapToBdMap = function(gg_lat, gg_lon) {
		var point = new Object();
		var x_pi = 3.14159265358979324 * 3000.0 / 180.0;
		var x = new Number(gg_lon);
		var y = new Number(gg_lat);
		var z = Math.sqrt(x * x + y * y) + 0.00002 * Math.sin(y * x_pi);
		var theta = Math.atan2(y, x) + 0.000003 * Math.cos(x * x_pi);
		var bd_lon = z * Math.cos(theta) + 0.0065;
		var bd_lat = z * Math.sin(theta) + 0.006;
		point.lng = bd_lon;
		point.lat = bd_lat;
		return point
	};
	biz.BdMapToTxMap = function(bd_lat, bd_lon) {
		var point = new Object();
		var x_pi = 3.14159265358979324 * 3000.0 / 180.0;
		var x = new Number(bd_lon - 0.0065);
		var y = new Number(bd_lat - 0.006);
		var z = Math.sqrt(x * x + y * y) - 0.00002 * Math.sin(y * x_pi);
		var theta = Math.atan2(y, x) - 0.000003 * Math.cos(x * x_pi);
		var Mars_lon = z * Math.cos(theta);
		var Mars_lat = z * Math.sin(theta);
		point.lng = Mars_lon;
		point.lat = Mars_lat;
		return point
	};
	window.biz = biz;
	return biz
});
