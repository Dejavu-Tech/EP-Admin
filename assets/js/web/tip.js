define(['jquery'], function($) {
	var tip = {};
	tip.lang = {
		"success": "操作成功",
		"error": "操作失败",
		"exception": "网络异常",
		"processing": "处理中..."
	};
	$('#tip-msgbox').remove();
	$("body", top.window.document).append('<div id="tip-msgbox" class="msgbox"></div>');
	window.msgbox = $("#tip-msgbox", top.window.document);
	tip.confirm = function(msg, callback, cancel_callback) {
		msg = msg.replace(/&lt;/g, "<");
		msg = msg.replace(/&gt;/g, ">");
		myrequire(['jquery.confirm'], function() {
			$.confirm({
				title: '提示',
				content: msg,
				confirmButtonClass: 'btn-primary',
				cancelButtonClass: 'btn-primary',
				confirmButton: '确 定',
				cancelButton: '取 消',
				animation: 'top',
				confirm: function() {
					if (callback && typeof(callback) == 'function') {
						callback()
					}
				},
				cancel: function() {
					if (cancel_callback && typeof(cancel_callback) == 'function') {
						cancel_callback()
					}
				}
			})
		})
	}, tip.prompt = function(msg, options, password) {
		var callback = null;
		var maxlength = null;
		var required = false;
		var input_type = password ? 'password' : 'text';
		if (typeof options == 'function') {
			callback = options
		} else if (typeof options == 'object') {
			maxlength = options.maxlength || null;
			callback = options.callback && typeof options.callback == 'function' ? options.callback : null;
			required = options.required || false
		}
		var inputid = 'prompt_' + (+new Date());
		var max = maxlength ? " maxlength='" + maxlength + "' " : '';
		myrequire(['jquery.confirm'], function() {
			$.alert({
				title: '提示',
				content: "<p>" + msg + "</p><p><input id='" + inputid + "' type='" + input_type + "' class='form-control' name='confirm' placeholder='" + msg + "' " + max + " /></p>",
				confirmButtonClass: 'btn-primary',
				confirmButton: '确 定',
				closeIcon: true,
				animation: 'top',
				keyboardEnabled: true,
				onOpen: function() {
					setTimeout(function() {
						$('#' + inputid).focus()
					}, 100)
				},
				confirm: function() {
					var value = $('#' + inputid).val();
					if ($.trim(value) == '' && required) {
						$('#' + inputid).focus();
						return false
					}
					if (callback && typeof(callback) == 'function') {
						callback(value)
					}
				}
			})
		})
	}, tip.promptlive = function(msg, options, password) {
		var callback = null;
		var maxlength = null;
		var required = false;
		var input_type = password ? 'password' : 'text';
		if (typeof options == 'function') {
			callback = options
		} else if (typeof options == 'object') {
			maxlength = options.maxlength || null;
			callback = options.callback && typeof options.callback == 'function' ? options.callback : null;
			required = options.required || false
		}
		var inputid = 'prompt_' + (+new Date());
		var max = maxlength ? " maxlength='" + maxlength + "' " : '';
		myrequire(['jquery.confirm'], function() {
			$.alert({
				title: '提示',
				content: "<p>" + msg + "</p><p><input id='" + inputid + "' type='" + input_type + "' class='form-control' name='confirm' placeholder='' " + max + " /></p>",
				confirmButtonClass: 'btn-primary',
				confirmButton: '确 定',
				closeIcon: true,
				animation: 'top',
				keyboardEnabled: true,
				onOpen: function() {
					setTimeout(function() {
						$('#' + inputid).focus()
					}, 100)
				},
				confirm: function() {
					var value = $('#' + inputid).val();
					if ($.trim(value) == '' && required) {
						$('#' + inputid).focus();
						return false
					}
					if (callback && typeof(callback) == 'function') {
						callback(value);
						return false
					}
				}
			})
		})
	}, tip.alert = function(msg, callback) {
		msg = msg.replace(/&lt;/g, "<");
		msg = msg.replace(/&gt;/g, ">");
		myrequire(['jquery.confirm'], function() {
			$.alert({
				title: '提示',
				content: msg,
				confirmButtonClass: 'btn-primary',
				confirmButton: '确 定',
				animation: 'top',
				confirm: function() {
					if (callback && typeof(callback) == 'function') {
						callback()
					}
				}
			})
		})
	}, 1;
	var Notify = function(element, options) {
			this.$element = $(element);
			this.options = $.extend({}, $.fn.notify.defaults, options);
			var cls = this.options.type ? "msg-" + this.options.type : "msg-success";
			var $note = '<span class="msg ' + cls + '">' + this.options.message + '</span>';
			this.$element.html($note);
			return this
		};
	Notify.prototype.show = function() {
		this.$element.addClass('in'), this.$element.append(this.$note);
		var autoClose = this.options.autoClose || true;
		if (autoClose) {
			var self = this;
			setTimeout(function() {
				self.close()
			}, this.options.delay || 2000)
		}
	}, Notify.prototype.close = function() {
		var self = this;
		self.$element.removeClass('in').transitionEnd(function() {
			self.$element.empty();
			if (self.options.onClosed) {
				self.options.onClosed(self)
			}
		});
		if (self.options.onClose) {
			self.options.onClose(self)
		}
	}, $.fn.notify = function(options) {
		return new Notify(this, options)
	}, $.fn.notify.defaults = {
		type: "success",
		delay: 3000,
		message: ''
	}, tip.msgbox = {
		show: function(options) {
			if (options.url) {
				options.url = options.url.replace(/&amp;/ig, "&");
				options.onClose = function() {
					redirect(options.url)
				}
			}
			if (options.message && options.message.length > 17) {
				tip.alert(options.message, function() {
					if (options.url) {
						redirect(options.url)
					}
				});
				return
			}
			notify = window.msgbox.notify(options), notify.show()
		},
		suc: function(msg, url, onClose, onClosed) {
			tip.msgbox.show({
				delay: 2000,
				type: "success",
				message: msg,
				url: url,
				onClose: onClose,
				onClosed: onClosed
			})
		},
		err: function(msg, url, onClose, onClosed) {
			tip.msgbox.show({
				delay: 2000,
				type: "error",
				message: msg,
				url: url,
				onClose: onClose,
				onClosed: onClosed
			})
		}
	};
	window.tip = tip
});
