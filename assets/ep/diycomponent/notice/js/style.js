/**
 * 公告·组件
 */
var noticeConHtml = '<div class="goods-list-edit layui-form">';
noticeConHtml += '<div class="layui-form-item ns-icon-radio">';
noticeConHtml += '<label class="layui-form-label sm">公告图片</label>';
noticeConHtml += '<div class="layui-input-block">';
noticeConHtml += '<div class="search_logo">';
noticeConHtml += '<div class="" ><img-sec-upload v-bind:data="{ data: data, field: \'leftImg\' }" v-bind:isShow="!1"></img-sec-upload></div>';
noticeConHtml += '<div class="desc" >';
noticeConHtml += '<div class="tip" >最多可添加一张图片</div>';
noticeConHtml += '<div class="spec">40px*28px</div>';
noticeConHtml += '</div>';
noticeConHtml += '</div>';
noticeConHtml += '</div>';
noticeConHtml += '</div>';

// noticeConHtml += '<div class="layui-form-item ns-icon-radio">';
// noticeConHtml += '<label class="layui-form-label sm">数据来源</label>';
// noticeConHtml += '<div class="layui-input-block">';
// noticeConHtml += '<template v-for="(item, index) in goodsSources" v-bind:k="index">';
// noticeConHtml += '<span :class="[item.value == data.sources ? \'\' : \'layui-hide\']">{{item.text}}</span>';
// noticeConHtml += '</template>';
// noticeConHtml += '<ul class="ns-icon">';
// noticeConHtml += '<li v-for="(item, index) in goodsSources" v-bind:k="index" :class="[item.value == data.sources ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="data.sources=item.value">';
// noticeConHtml += '<img v-if="item.value == data.sources" :src="item.selectedSrc" />'
// noticeConHtml += '<img v-else :src="item.src" />'
// noticeConHtml += '</li>';
// noticeConHtml += '</ul>';
// noticeConHtml += '</div>';
// noticeConHtml += '</div>';

noticeConHtml += '<div class="layui-form-item" v-if="data.sources == \'diy\'">';
noticeConHtml += '<label class="layui-form-label sm">选择公告</label>';
noticeConHtml += '<div class="layui-input-block">';
noticeConHtml += '<div class="ns-input-text selected-style" v-on:click="addNotice">选择 <i class="layui-icon layui-icon-right"></i></div>';
noticeConHtml += '</div>';
noticeConHtml += '</div>';
noticeConHtml += '</div>';

Vue.component("notice-con", {
	template: noticeConHtml,
	data: function () {
		return {
			data: this.$parent.data,
			goodsSources: [{
					text: "手动添加公告",
					value: "default",
					src: noticeResourcePath + "/notice/img/manual.png",
					selectedSrc: noticeResourcePath + "/notice/img/manual_1.png"
				},
				{
					text: "选择系统公告",
					value: "diy",
					src: noticeResourcePath + "/notice/img/goods.png",
					selectedSrc: noticeResourcePath + "/notice/img/goods_1.png"
				}
			],
			isShow: false,
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify); //加载验证方法
	},
	methods: {
		verify: function () {
			var res = {
				code: true,
				message: ""
			};
			return res;
		},
		addNotice: function () {
			var self = this;
			self.noticeSelect(function (res) {
				self.$parent.data.noticeIds = [];
				self.$parent.data.list = [];

				for (var i = 0; i < res.length; i++) {
					self.$parent.data.noticeIds.push(res[i].id);
					self.$parent.data.list[i] = {
						title: res[i].title,
						link: {}
					};
				}

			}, self.$parent.data.noticeIds);
		},
		noticeSelect: function (callback, selectId) {
			var self = this;
			layui.use(['layer'], function () {
				var url = ns.url("shop/notice/noticeselect", {
					select_id: selectId.toString()
				});
				//iframe层-父子操作
				layer.open({
					title: "公告选择",
					type: 2,
					area: ['1000px', '600px'],
					fixed: false, //不固定
					btn: ['保存', '返回'],
					content: url,
					yes: function (index, layero) {
						var iframeWin = window[layero.find('iframe')[0]['name']]; //得到iframe页的窗口对象，执行iframe页的方法：

						iframeWin.selectGoods(function (obj) {
							if (typeof callback == "string") {
								try {
									eval(callback + '(obj)');
									layer.close(index);
								} catch (e) {
									console.error('回调函数' + callback + '未定义');
								}
							} else if (typeof callback == "function") {
								callback(obj);

								layer.close(index);
							}

						});
					}
				});
			});
		}
	}
});

var noticeEditHtml = '<div class="notice-config">';
noticeEditHtml += '<div class="template-edit-wrap">';
noticeEditHtml += '<ul v-if="data.sources == \'default\'">';
noticeEditHtml += '<li v-for="(item,index) in list" v-bind:key="index">';
noticeEditHtml += '<div class="content-block">';
noticeEditHtml += '<div class="layui-form-item" >';
noticeEditHtml += '<label class="layui-form-label sm">公告内容</label>';
noticeEditHtml += '<div class="layui-input-block">';
noticeEditHtml += '<input type="text" name=\'title\' v-model="item.title" class="layui-input" />';
noticeEditHtml += '</div>';
noticeEditHtml += '</div>';
noticeEditHtml += '</div>';

noticeEditHtml += '<i class="del" v-on:click="list.splice(index,1)" data-disabled="1">x</i>';
noticeEditHtml += '<div class="error-msg"></div>';
noticeEditHtml += '</li>';

noticeEditHtml += '<div class="add-item ns-text-color" v-if="data.sources == \'default\'" v-on:click="list.push({ title:\'公告\',link:{} })">';
noticeEditHtml += '<i>+</i>';
noticeEditHtml += '<span>添加一条公告</span>';
noticeEditHtml += '</div>';
noticeEditHtml += '</ul>';
noticeEditHtml += '</div>';
noticeEditHtml += '</div>';

Vue.component("notice-edit", {

	template: noticeEditHtml,
	data: function () {
		return {
			data: this.$parent.data,
			list: this.$parent.data.list,
		};

	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify); //加载验证方法
	},
	methods: {
		verify: function () {
			var res = {
				code: true,
				message: ""
			};
			if (this.list.length > 0) {
				for (var i = 0; i < this.length; i++) {
					if (this.list[i].title == "") {
						res.code = false;
						res.message = "公告内容不能为空";
						break;
					}
				}
			} else {
				res.code = false;
				res.message = "请添加一条公告";
			}

			return res;
		}
	}
});