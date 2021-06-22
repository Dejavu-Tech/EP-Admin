function closeBox(obj) {
    var elem = $(obj).parents(".template-edit-title").next();
    if ($(elem).hasClass("layui-hide")) {
        $(elem).removeClass("layui-hide");
        $(obj).removeClass("closed-right");
    } else {
        $(elem).addClass("layui-hide");
        $(obj).addClass("closed-right");
    }
}

function get_math_rant(len) {
    return Number(Math.random().toString().substr(3, len) + Date.now()).toString(36);
}

var show_link_box_flag = true;

function select_link(link, support_diy_view, callback) {
    var url = linkUrl;
    if (show_link_box_flag) {
        show_link_box_flag = false;
        $.post(url, {link: JSON.stringify(link), support_diy_view: support_diy_view}, function (str) {
            window.linkIndex = layer.open({
                type: 1,
                title: "选择链接",
                content: str,
                btn: [],
                area: ['850px'], //宽高
                maxWidth: 1920,
                cancel: function (index, layero) {
                    show_link_box_flag = true;
                },
                end: function () {
                    if (window.linkData) {
                        if (callback) callback(window.linkData);
                        delete window.linkData;// 清空本次选择
                    }
                    show_link_box_flag = true;
                }
            });
        });
    }
};


/**
 * 数据表格
 * layui官方文档：https://www.layui.com/doc/modules/table.html
 * @param options
 * @constructor
 */
 function Table(options) {
	
	if (!options) return;
	var _self = this;
	
	options.parseData = options.parseData || function (data) {
		return {
			"code": data.code,
			"msg": data.message,
			"count": data.data.count,
			"data": data.data.list
		};
	};
	
	options.request = options.request || {
		limitName: 'page_size' //每页数据量的参数名，默认：limit
	};
	
	if (options.page == undefined) {
		options.page = {
			layout: ['count', 'limit', 'prev', 'page', 'next'],
			limit: 10
		};
	}
	
	options.defaultToolbar = options.defaultToolbar || [];//'filter', 'print', 'exports'
	
	options.toolbar = options.toolbar || "";//头工具栏事件
	
	options.skin = options.skin || 'line';
	options.size = options.size || 'lg';
	options.async = (options.async != undefined) ? options.async : true;
	options.done = function (res, curr, count) {
		//加载图片放大
		loadImgMagnify();
		if (options.callback) options.callback(res, curr, count);
	};
	
	layui.use('table', function () {
		_self._table = layui.table;
		_self._table.render(options);
	});
	
	this.filter = options.filter || options.elem.replace(/#/g, "");
	this.elem = options.elem;
	
	
	//获取当前选中的数据
	this.checkStatus = function () {
		return this._table.checkStatus(_self.elem.replace(/#/g, ""));
	};
}

/**
 * 监听头工具栏事件
 * @param callback 回调
 */
Table.prototype.toolbar = function (callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on('toolbar(' + _self.filter + ')', function (obj) {
				var checkStatus = _self._table.checkStatus(obj.config.id);
				obj.data = checkStatus.data;
				obj.isAll = checkStatus.isAll;
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 监听底部工具栏事件
 * @param callback 回调
 */
Table.prototype.bottomToolbar = function (callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on('bottomToolbar(' + _self.filter + ')', function (obj) {
				var checkStatus = _self._table.checkStatus(obj.config.id);
				obj.data = checkStatus.data;
				obj.isAll = checkStatus.isAll;
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 绑定layui的on事件
 * @param name
 * @param callback
 */
Table.prototype.on = function (name, callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on(name + '(' + _self.filter + ')', function (obj) {
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};


/**
 * //监听行工具事件
 * @param callback 回调
 */
Table.prototype.tool = function (callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on('tool(' + _self.filter + ')', function (obj) {
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 刷新数据
 * @param options 参数，参考layui数据表格参数
 */
Table.prototype.reload = function (options) {
	options = options || {
		page: {
			curr: 1
		}
	};
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.reload(_self.elem.replace(/#/g, ""), options);
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 自定义分页
 * @param options
 * @constructor
 */
 function Page(options) {
	
	if (!options) return;
	var _self = this;
	
	options.elem = options.elem.replace(/#/g, "");// 注意：这里不能加 # 号
	options.count = options.count || 0;// 数据总数。一般通过服务端得到
	options.limit = options.limit || 10;// 每页显示的条数。laypage将会借助 count 和 limit 计算出分页数。
	options.limits = options.limits || [];// 每页条数的选择项。如果 layout 参数开启了 limit，则会出现每页条数的select选择框
	options.curr = location.hash.replace('#!page=', '');// 起始页。一般用于刷新类型的跳页以及HASH跳页
	// options.hash = options.hash || 'page';// 开启location.hash，并自定义 hash 值。如果开启，在触发分页时，会自动对url追加：#!hash值={curr} 利用这个，可以在页面载入时就定位到指定页
	options.groups = options.groups || 5;// 连续出现的页码个数
	options.prev = options.prev || '<i class="layui-icon layui-icon-left"></i>';// 自定义“上一页”的内容，支持传入普通文本和HTML
	options.next = options.next || '<i class="layui-icon layui-icon-right"></i>';// 自定义“下一页”的内容，同上
	options.first = options.first || 1;// 自定义“首页”的内容，同上
	
	// 自定义排版。可选值有：count（总条目输区域）、prev（上一页区域）、page（分页区域）、next（下一页区域）、limit（条目选项区域）、refresh（页面刷新区域。注意：layui 2.3.0 新增） 、skip（快捷跳页区域）
	options.layout = options.layout || ['count', 'prev', 'page', 'next'];
	
	options.jump = function (obj, first) {
		
		//首次不执行，一定要加此判断，否则初始时会无限刷新
		if (!first) {
			obj.page = obj.curr;
			if(options.callback) options.callback.call(this, obj);
		}
	};
	
	layui.use('laypage', function () {
		_self._page = layui.laypage;
		_self._page.render(options);
	});
	
}

$(function () {
	loadImgMagnify();
});

//图片最大递归次数
var IMG_MAX_RECURSIVE_COUNT = 6;
var count = 0;

/**
 * //加载图片放大
 */
function loadImgMagnify() {
	setTimeout(function () {
		try {
			if (layer) {
				$("img[src!=''][layer-src]").each(function () {
					var id = getId($(this).parent());
					layer.photos({
						photos: "#" + id,
						anim: 5
					});
					count = 0;
				});
			}
		} catch (e) {
		
		}
	}, 200);
}

function getId(o) {
	count++;
	var id = o.attr("id");
	if (id == undefined && count < IMG_MAX_RECURSIVE_COUNT) {
		id = getId(o.parent());
	}
	if (id == undefined) {
		id = get_math_rant(10);
		o.attr("id", id);
	}
	return id;
}