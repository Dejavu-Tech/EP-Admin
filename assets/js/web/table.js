define(['jquery'], function($) {
	$(document).on('change', 'table tfoot [type="checkbox"]', function(e) {
		e && e.preventDefault();
		var $table = $(e.target).closest('table'),
			$checked = $(e.target).is(':checked');
		$('thead [type="checkbox"]', $table).prop('checked', $checked);
		$('.page-table-header input[type="checkbox"]').prop('checked', $checked);
		$('tbody [type="checkbox"]', $table).prop('checked', $checked)
	});
	$(document).on('change', 'table thead [type="checkbox"]', function(e) {
		e && e.preventDefault();
		var $table = $(e.target).closest('table'),
			$checked = $(e.target).is(':checked');
		$('.page-table-header input[type="checkbox"]').prop('checked', $checked);
		$('tbody [type="checkbox"]:not(:disabled)', $table).prop('checked', $checked);
		$('tfoot [type="checkbox"]', $table).prop('checked', $checked)
	});
	$(document).on('change', '.page-table-header input[type="checkbox"]', function(e) {
		e && e.preventDefault();
		var $table = $(e.target).parent().next('table'),
			$checked = $(e.target).is(':checked');
		$('thead [type="checkbox"]', $table).prop('checked', $checked);
		$('tfoot [type="checkbox"]', $table).prop('checked', $checked);
		$('tbody [type="checkbox"]', $table).prop('checked', $checked)
	});
	var table_search = $('form.table-search');
	if (table_search.length > 0) {
		$('.daterange-time').addClass('btn-sm')
	}
	var div = $(".table-responsive"),
		checkboxs = $('tbody tr td:first-child [type="checkbox"]', div),
		batch = $('[data-toggle^="batch"]'),
		trigger = $('[data-trigger="batch"]');
	if (div.length > 0) {
		$('[data-toggle="ajaxDisplayorder"]').click(function() {
			var $this = $(this),
				href = $this.data("href"),
				html = $this.val() || $this.html(),
				buttontype = $this.val() ? 'input' : 'button';
			var postdata = {},
				selecteds = all_selects();
			postdata.displayorder = (function() {
				var displayorders = [];
				$.each(selecteds, function(i) {
					displayorders.push({
						id: this,
						displayorder: $(":input[name='displayorder[" + this + "]']").val()
					})
				});
				return displayorders
			})();
			$this.attr('disabled', true), buttontype == 'button' ? $this.html('<i class="fa fa-spinner fa-spin"></i> ' + tip.lang.processing) : $this.val(tip.lang.processing);
			$.post(href, postdata).done(function(data) {
				data = eval("(" + data + ")");
				if (data.status == 1) {
					tip.msgbox.suc(data.result.message || tip.lang.success);
					$this.removeAttr('disabled'), buttontype == 'button' ? $this.html(html) : $this.val(html)
				} else {
					buttontype == 'button' ? $this.html(html) : $this.val(html);
					$this.removeAttr('disabled'), tip.msgbox.err(data.result.message || tip.lang.error)
				}
			}).fail(function() {
				$this.removeAttr('disabled'), buttontype == 'button' ? $this.html(html) : $this.val(html), tip.msgbox.err(tip.lang.exception)
			})
		});
		checkboxs.click(function() {
			var $this = $(this);
			if ($this[0].checked) {
				$this.closest('tr').find('td').css('background', '#f7fbff')
			} else {
				$this.closest('tr').find('td').css('background', 'none')
			}
		});
		var all_selects = function() {
				var vals = checkboxs.map(function() {
					return $(this).val()
				}).get();
				return vals
			};
		var get_selecteds = function() {
				var selected_checkboxs = $('tbody tr td:first-child [type="checkbox"]:checked', div);
				selecteds = selected_checkboxs.map(function() {
					return $(this).val()
				}).get();
				return selecteds
			};
		var selecteds = get_selecteds();
		if (selecteds.length <= 0) {
			batch.attr("disabled", "disabled")
		}
		batch.on("click", function() {
			var $this = $(this),
				href = $this.data("href"),
				html = $this.val() || $this.html(),
				buttontype = $this.val() ? 'input' : 'button';
			if ($this.data('toggle') == 'batch-level' || $this.data('toggle') == 'batch-group') {
				return
			}
			$this.attr("disabled", "disabled");
			var chks = $('tbody tr td:first-child [type="checkbox"]:checked', div);
			var selecteds = get_selecteds();
			var submit = function() {
					buttontype == 'button' ? $this.html('<i class="fa fa-spinner fa-spin"></i> ' + tip.lang.processing) : $this.val(tip.lang.processing);
					$.post(href, {
						ids: selecteds
					}).done(function(data) {
						data = eval("(" + data + ")");
						if (data.status == 1) {
							if ($this.data('toggle') == 'batch-remove') {
								var deferred = $.Deferred(),
									removeHandler = function(def) {
										var c = 0;
										return chks.parents("tr").fadeOut(function() {
											$(this).remove(), c++, chks.length == c && def.resolve()
										}), def
									};
								$.when(removeHandler(deferred)).done(function() {
									batch.attr("disabled", 0 == $('tbody tr td:first-child [type="checkbox"]:checked', div).length), 0 == $("table tbody tr", div).length && window.location.reload()
								})
							} else {
								batch.attr("disabled", 0 == $('table tbody tr td:first-child [type="checkbox"]:checked', div).length);
								tip.msgbox.suc(data.result.message || tip.lang.success, data.result.url)
							}
							buttontype == 'button' ? $this.html(html) : $this.val(html)
						} else {
							buttontype == 'button' ? $this.html(html) : $this.val(html);
							tip.msgbox.err(data.result.message || tip.lang.error)
						}
					}).fail(function() {
						buttontype == 'button' ? $this.html(html) : $this.val(html), tip.msgbox.err(tip.lang.exception)
					})
				};
			if ($this.data('confirm')) {
				tip.confirm($this.data('confirm'), submit, function() {
					$this.removeAttr("disabled", "disabled")
				})
			} else {
				submit()
			}
		}), trigger.on("click", function() {
			var ids = all_selects();
			$(this).data("set", {
				ids: ids.join(',')
			})
		}), $(document).on("change", '.page-table-header input[type="checkbox"]', function(e) {
			e && e.preventDefault();
			var t = $(e.target).parent().next("table"),
				checked = $(e.target).is(":checked");
			$('.page-table-header input[type="checkbox"]', t).prop("checked", checked), batch.add(trigger).attr("disabled", !checked);
			$('tbody tr td:first-child [type="checkbox"]', t).each(function() {
				var $this = $(this);
				if (checked) {
					$this.closest('tr').find('td').css('background', '#f7fbff')
				} else {
					$this.closest('tr').find('td').css('background', 'none')
				}
			})
		}), $(document).on("change", '.table-responsive tfoot td:first [type="checkbox"]', function(e) {
			e && e.preventDefault();
			var t = $(e.target).closest("table"),
				checked = $(e.target).is(":checked");
			$('.page-table-header input[type="checkbox"]', t).prop("checked", checked), batch.add(trigger).attr("disabled", !checked);
			$('tbody tr td:first-child [type="checkbox"]', t).each(function() {
				var $this = $(this);
				if (checked) {
					$this.closest('tr').find('td').css('background', '#f7fbff')
				} else {
					$this.closest('tr').find('td').css('background', 'none')
				}
			})
		}), $(document).on("change", '.table-responsive thead th:first [type="checkbox"]', function(e) {
			e && e.preventDefault();
			var t = $(e.target).closest("table"),
				checked = $(e.target).is(":checked");
			$('.page-table-header input[type="checkbox"]', t).prop("checked", checked), batch.add(trigger).attr("disabled", !checked);
			$('tbody tr td:first-child [type="checkbox"]', t).each(function() {
				var $this = $(this);
				if (checked) {
					$this.closest('tr').find('td').css('background', '#f7fbff')
				} else {
					$this.closest('tr').find('td').css('background', 'none')
				}
			})
		}), $(document).on("change", '.table-responsive tbody td:first-child [type="checkbox"]', function(e) {
			e && e.preventDefault();
			var t = $(e.target).closest("table"),
				checked = $(e.target).is(":checked"),
				chk = $('tbody tr td:first-child  [type="checkbox"]:checked', t);
			$('.page-table-header input[type="checkbox"]').prop("checked", checked && chk.length == checkboxs.length);
			$('tfoot td:first [type="checkbox"]', t).prop("checked", checked && chk.length == checkboxs.length);
			$('thead th:first [type="checkbox"]', t).prop("checked", checked && chk.length == checkboxs.length);
			if (chk.length > 0) {
				batch.add(trigger).removeAttr("disabled")
			} else {
				batch.add(trigger).attr("disabled", "disabled")
			}
		}), $(document).on("click", '.table-responsive tbody [data-toggle="ajaxRemove"]', function(e) {
			e.preventDefault();
			var obj = $(this),
				url = obj.attr('href') || obj.data('href') || obj.data('url'),
				confirm = obj.data('msg') || obj.data('confirm');
			var submit = function() {
					obj.html('<i class="fa fa-spinner fa-spin"></i> ' + tip.lang.processing);
					$.post(url).done(function(data) {
						data = eval("(" + data + ")");
						if (data.status == 1) {
							var tr = obj.parents("tr");
							tr.css({
								"background-color": "#dff0d8"
							}).find("td").css({
								"background-color": "#dff0d8"
							}), tr.fadeOut(function() {
								tr.remove(), 0 == $(obj.closest('tbody')).find('tr').length && window.location.reload()
							})
						} else {
							obj.button('reset'), tip.msgbox.err(data.result.message || tip.lang.error, data.result.url)
						}
					}).fail(function() {
						obj.button('reset');
						tip.msgbox.err(tip.lang.exception)
					})
				};
			if (confirm) {
				tip.confirm(confirm, submit, function() {
					obj.removeAttr("disabled", "disabled")
				})
			} else {
				submit()
			}
		})
	} else {
		batch.attr("disabled", "disabled")
	}
}), $(document).on("click.dropdown-menu", ".dropdown-select > li > a", function(e) {
	e.preventDefault();
	var target = $(e.target);
	if (!target.is('a')) {
		target = target.closest('a')
	}
	var menu = target.closest('.dropdown-menu'),
		label = menu.parent().find('.dropdown-label');
	var input = target.find('input'),
		checked = input.is(':checked');
	if (!input.is(':disabled') && input.attr('type') == 'radio' && checked) {
		menu.find('li').removeClass('active')
	}
	target.parent().removeClass('active');
	if (!checked) {
		target.parent().addClass('active')
	}
	input.prop('checked', !input.prop('checked'));
	var textchange = label.data('change') == 'true' || label.data('change') === undefined;
	if (textchange) {
		var checkedinput = menu.find('li > a >input:checked');
		if (checkedinput.length > 0) {
			var texts = [];
			checkedinput.each(function() {
				var text = $(this).parent().text();
				if (text) {
					texts.push($.trim(text))
				}
			});
			if (texts.length < 6) {
				texts = texts.join(", ")
			} else {
				texts = " 选中" + texts.length + "项"
			}
			label.html(texts)
		} else {
			label.html(label.data('placeholder'))
		}
	}
	input.trigger('change', [input.val()]);
	var change = menu.data('change') || '';
	if (change == 'submit') {
		menu.closest('form').submit()
	}
});