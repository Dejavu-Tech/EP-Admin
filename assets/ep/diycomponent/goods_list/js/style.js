/**
 * 空的验证组件，后续如果增加业务，则更改组件
 */
var goodsListHtml = '<div class="goods-list-edit layui-form">';

		goodsListHtml += '<div class="layui-form-item">';
			goodsListHtml += '<label class="layui-form-label sm">数据来源</label>';
			goodsListHtml += '<div class="layui-input-block">';
				goodsListHtml += '<div class="source-selected">';
					goodsListHtml += '<div class="source">{{ sourcesText }}</div>';
					goodsListHtml += '<template v-for="(item,index) in goodsSources" v-bind:k="index">';
					goodsListHtml += '<span class="source-item" :title="item.text" v-on:click="data.sources=item.value" v-bind:class="[(data.sources == item.value) ?  \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\' ]"><img v-bind:src="item.selectedIcon" v-if="data.sources == item.value"><img v-bind:src="item.icon" v-else/></span>';
					goodsListHtml += '</template>';
				goodsListHtml += '</div>';
			goodsListHtml += '</div>';
		goodsListHtml += '</div>';
		
		goodsListHtml += '<div class="layui-form-item" v-if="isLoad && data.sources == \'category\'">';
			goodsListHtml += '<label class="layui-form-label sm">商品分类</label>';
			goodsListHtml += '<div class="layui-input-block align-right">';
					goodsListHtml += '<a href="#" class="ns-input-text" @click="selectCategory"><span class="ns-text-color">{{ data.categoryName }}</span><i class="iconfont iconyoujiantou"></i></a>';
			goodsListHtml += '</div>';
		goodsListHtml += '</div>';
		
		goodsListHtml += '<div class="layui-form-item" v-if="isLoad && data.sources == \'diy\'">';
			goodsListHtml += '<label class="layui-form-label sm">手动选择</label>';
			goodsListHtml += '<div class="layui-input-block align-right">';
				goodsListHtml += '<a href="#" class="ns-input-text" v-on:click="addGoods"><span class="ns-text-color">请选择</span><i class="iconfont iconyoujiantou"></i></a>';
			goodsListHtml += '</div>';
		goodsListHtml += '</div>';
			
		goodsListHtml += '<slide v-bind:data="{ field : \'goodsCount\', label: \'商品数量\', max: 20}" v-if="data.sources != \'diy\'"></slide>';

	goodsListHtml += '</div>';
var select_goods_list = []; //配合商品选择器使用
Vue.component("goods-list", {
	template: goodsListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			goodsSources: [
				{
					text: "默认",
					value: "default",
					icon: goodsListResourcePath + "/goods_list/img/default_icon.png",
					selectedIcon: goodsListResourcePath + "/goods_list/img/default_selected_icon.png"
				},
				{
					text: "商品分类",
					value: "category",
					icon: goodsListResourcePath + "/goods_list/img/category_icon.png",
					selectedIcon: goodsListResourcePath + "/goods_list/img/category_selected_icon.png"
				},
				// {
				// 	text : "手动选择",
				// 	value : "diy",
				// 	icon: goodsListResourcePath + "/goods_list/img/diy_icon.png",
				// 	selectedIcon: goodsListResourcePath + "/goods_list/img/diy_selected_icon.png"
				// }
			],
			categoryList: [],
			isLoad: true,
			isShow: false,
			selectIndex: 0,//当前选中的下标
			goodsCount: [6, 12, 18, 24, 30]
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		shopNum: function(){
			if (this.$parent.data.goodsCount.length > 0 && this.$parent.data.goodsCount < 0) {
				layer.msg("商品数量不能小于0");
				this.$parent.data.goodsCount = 0;
			}
			if (this.$parent.data.goodsCount > 50){
				layer.msg("商品数量最多为50");
				this.$parent.data.goodsCount = 50;
			}
		},
		verify : function () {
			var res = { code : true, message : "" };
			if(this.$parent.data.goodsCount.length===0) {
				res.code = false;
				res.message = "请输入商品数量";
			}
			if (this.$parent.data.goodsCount < 0) {
				res.code = false;
				res.message = "商品数量不能小于0";
			}
			if (this.$parent.data.goodsCount > 50){
				res.code = false;
				res.message = "商品数量最多为50";
			}
			if (this.$parent.data.sources == 'category' && this.$parent.data.categoryId == 0){
				res.code = false;
				res.message = "请选择商品分类";
			}
			return res;
		},
		addGoods: function() {
			var self = this;
			goodsSelect(function (res) {
				self.$parent.data.goodsId = res;
			}, self.$parent.data.goodsId, {mode: "spu", disabled: 0, promotion: "module", post: post});
		},
		selectCategory(){
			var self = this;
			layer.open({
				type: 1,
				title: '选择分类',
				area:['630px','430px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .goods-category-layer").html(),
				success: function(layero, index) {
					$("body").on("click", ".layui-layer-content .category-wrap .category-item", function () {
						$(this).addClass("selected ns-border-color").siblings().removeClass("selected ns-border-color");
					});
					$(".layui-layer-content .category-wrap .category-item[data-id='" + self.data.categoryId + "']").click();
				},
				yes: function (index, layero) {
					self.data.categoryName =  $(".layui-layer-content .category-wrap .category-item.selected").text();
					self.data.categoryId = $(".layui-layer-content .category-wrap .category-item.selected").attr('data-id');
					layer.closeAll()
				}
			});
		}
	},
	computed:{
		sourcesText(){
			var sourcesText = '',
				_this = this;
			this.goodsSources.forEach(function(v){
				if (_this.data.sources == v.value) sourcesText = v.text;
			})
			return sourcesText;
		}
	}
});

var goodsListStyleHtml = '<div class="layui-form-item">';
		goodsListStyleHtml += '<label class="layui-form-label sm">模板样式</label>';
		goodsListStyleHtml += '<div class="layui-input-block align-right">';
			goodsListStyleHtml += '<a href="#" class="ns-input-text" v-on:click="selectGoodsStyle"><span class="ns-text-color">风格{{ data.style }}</span><i class="iconfont iconyoujiantou"></i></a>';
		goodsListStyleHtml += '</div>';
	goodsListStyleHtml += '</div>';

Vue.component("goods-list-style", {
	template: goodsListStyleHtml,
	data: function() {
		return {
			data: this.$parent.data,
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		verify: function () {
			var res = { code: true, message: "" };
			return res;
		},
		selectGoodsStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '模板样式',
				area:['930px','630px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .goods-list-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$("body").on("click", ".layui-layer-content .style-list-con-goods .style-li-goods", function () {
						$(this).addClass("selected ns-border-color").siblings().removeClass("selected ns-border-color");
						$(".layui-layer-content input[name='style']").val($(this).index() + 1);
					});
				},
				yes: function (index, layero) {
					self.data.style = $(".layui-layer-content input[name='style']").val();
					layer.closeAll()
				}
			});
		},
	}
});

// 是否启用更多按钮设置
var carBtnHtml = '<div class="layui-form-item ns-checkbox-wrap">';
	carBtnHtml +=	 '<label class="layui-form-label sm">是否启用</label>';
	carBtnHtml +=	 '<div class="layui-input-block">';
	carBtnHtml +=		 '<span v-if="data.isShowCart == 1">是</span>';
	carBtnHtml +=		 '<span v-else>否</span>';
	carBtnHtml +=		 '<div v-on:click="changeState()" class="layui-unselect layui-form-checkbox" v-bind:class="{ \'layui-form-checked\': (data.isShowCart==1) }"  lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
	carBtnHtml +=	 '</div>';
	carBtnHtml += '</div>';

Vue.component("goods-list-more-btn", {
	template: carBtnHtml,
	data: function () {
		return {
			data: this.$parent.data,
		};
	},
	created: function () {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		verify : function () {
			var res = { code : true, message : "" };
			return res;
		},
		changeState: function() {
			this.data.isShowCart = this.data.isShowCart ? 0 : 1;
		}
	},
});


// 购物车按钮
var cartStyleHtml = '<div class="layui-form-item">';
		cartStyleHtml += '<label class="layui-form-label sm">选择风格</label>';
		cartStyleHtml += '<div class="layui-input-block align-right">';
			cartStyleHtml += '<div class="ns-input-text selected-style" v-on:click="selectTestStyle">请选择<i class="iconfont"></i></div>';
		cartStyleHtml += '</div>';
	cartStyleHtml += '</div>';

Vue.component("cart-style", {
	template: cartStyleHtml,
	data: function() {
		return {
			data: this.$parent.data,
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		verify: function () {
			var res = { code: true, message: "" };
			return res;
		},
		selectTestStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area:['930px','630px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .cart-list-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='cart_style']").val(self.data.style);
					$("body").on("click", ".layui-layer-content .cart-list-con .cart-li", function () {
						$(this).addClass("selected ns-border-color").siblings().removeClass("selected ns-border-color");
						$(".layui-layer-content input[name='cart_style']").val($(this).index() + 1);
					});
				},
				yes: function (index, layero) {
					self.data.cartStyle = $(".layui-layer-content input[name='cart_style']").val();
					layer.closeAll()
				}
			});
		},
	}
});

// 多选
var showContentHtml = '<div class="layui-form-item goods-show-box ns-checkbox-wrap">';
	showContentHtml +=		'<div class="layui-input-block">';
		showContentHtml +=		'<div class="layui-input-inline-checkbox">';
		showContentHtml +=			'<span>商品名称</span>';
		showContentHtml +=			'<div v-on:click="changeStatus(\'isShowGoodName\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodName == 1)}" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
		showContentHtml +=		'</div>';
		
		showContentHtml +=		'<div class="layui-input-inline-checkbox">';
		showContentHtml +=			'<span>副标题</span>';
		showContentHtml +=			'<div v-on:click="changeStatus(\'isShowGoodSubTitle\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodSubTitle == 1)}" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
		showContentHtml +=		'</div>';
		
		showContentHtml +=		'<div class="layui-input-inline-checkbox">';
		showContentHtml +=			'<span>划线市场价</span>';
		showContentHtml +=			'<div v-on:click="changeStatus(\'isShowMarketPrice\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowMarketPrice == 1)}" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
		showContentHtml +=		'</div>';
		
		showContentHtml +=		'<div class="layui-input-inline-checkbox">';
		showContentHtml +=			'<span>商品销量</span>';
		showContentHtml +=			'<div v-on:click="changeStatus(\'isShowGoodSaleNum\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodSaleNum == 1)}" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
		showContentHtml +=		'</div>';
	showContentHtml +=		'</div>';
	showContentHtml += '</div>';

Vue.component("show-content", {
	template: showContentHtml,
	data: function () {
		return {
			data: this.$parent.data,
			isShowGoodName: this.$parent.data.isShowGoodName,
			isShowMarketPrice: this.$parent.data.isShowMarketPrice,
		};
	},
	created: function () {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		verify :function () {
			var res = { code: true, message: "" };
			return res;
		},
		changeStatus: function(field) {
			this.$parent.data[field] = this.$parent.data[field] ? 0 : 1;
		}
	}
});

var goodsTagStyleHtml = '<div class="goods-tag-component">'; 
		goodsTagStyleHtml += '<div class="layui-form-item">';
			goodsTagStyleHtml += '<label class="layui-form-label sm">角标</label>';
			goodsTagStyleHtml += '<div class="layui-input-block align-right">';
				goodsTagStyleHtml += '<template v-for="(item,index) in styleList" v-bind:k="index">';
					goodsTagStyleHtml += '<div v-on:click="data.goodsTag=item.value" v-bind:class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (data.goodsTag==item.value) }"><i class="layui-anim layui-icon">&#xe63f;</i><div>{{item.text}}</div></div>';
				goodsTagStyleHtml += '</template>';
			goodsTagStyleHtml += '</div>'; 
		goodsTagStyleHtml += '</div>'; 

		goodsTagStyleHtml += '<div class="layui-form-item goods-tag-img" v-if="data.goodsTag == \'diy\'">';
				goodsTagStyleHtml += '<img-sec-upload v-bind:data="{ data : data.tagImg }"></img-sec-upload>';
			goodsTagStyleHtml += '<div class="upload-tip"><p>请上传角标的图片</p><p class="ns-text-color">推荐使用 100X100像素的.png的图片</p></div>';
		goodsTagStyleHtml += '</div>';
		goodsTagStyleHtml += '<div class="error-msg"></div>';
	goodsTagStyleHtml += '</div>';

Vue.component("goods-tag-style", {
	template: goodsTagStyleHtml,
	data: function() {
		return {
			data: this.$parent.data,
			styleList: [
				{
					text: "默认",
					value: "default"
				},
				{
					text: "不显示",
					value: "notshow"
				},
				{
					text: "自定义",
					value: "diy"
				}
			]
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		verify :function () {
			var res = {code: true, message: ""};
			var _self = this;
			if (_self.data.goodsTag == 'diy' && _self.data.tagImg.imageUrl == '') {
				res.code = false;
				res.message = "请添加图片";
				$('.goods-tag-component .error-msg').text("请添加图片").show();
			} else {
				$('.goods-tag-component .error-msg').text("请添加图片").hide();
			}
			return res;
		}
	}
});