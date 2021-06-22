// 顶部内容组件
var newcomeTopConHtml = '<div>';
	newcomeTopConHtml += 	'<template v-if="data.style == 1">';
	newcomeTopConHtml += 		'<div class="goods-head">';
	newcomeTopConHtml +=			'<div class="title-wrap">';
	// newcomeTopConHtml +=		'<div class="left-icon" v-if="list.imageUrl"><img v-bind:src="$parent.$parent.changeImgUrl(list.imageUrl)" /></div>';
	// newcomeTopConHtml +=		'<span class="name">{{list.title}}</span>';
	newcomeTopConHtml +=				'<template v-for="(item, index) in list" v-if="item.style == 1">';
	newcomeTopConHtml +=					'<div class="left-icon" v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] == \'public\'"><img v-bind:src="imgUrl1" /></div>';
	newcomeTopConHtml +=					'<div class="left-icon" v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] != \'public\'"><img v-bind:src="$parent.$parent.changeImgUrl(item.imageUrl)" /></div>';
	newcomeTopConHtml +=					'<span class="name">{{item.title}}</span>';
	newcomeTopConHtml +=				'</template>';
	newcomeTopConHtml +=			'</div>';
	newcomeTopConHtml +=		'</div>';
	newcomeTopConHtml +=	'</template>';
	
	newcomeTopConHtml +=	'<template v-if="data.style == 2">';
	newcomeTopConHtml +=		'<div class="title-wrap title-wrap-2">';
	newcomeTopConHtml +=			'<div class="title-left">';
	newcomeTopConHtml +=				'<template v-for="(item, index) in list" v-if="item.style == 2">';
	newcomeTopConHtml +=					'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] == \'public\'" :src="imgUrl2" />';
	newcomeTopConHtml +=					'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] != \'public\'" :src="$parent.$parent.changeImgUrl(item.imageUrl)" />';
	newcomeTopConHtml +=				'</template>';
	newcomeTopConHtml +=			'</div>';
	newcomeTopConHtml +=			'<div class="title-right"><span>更多拼团</span><i class="iconfont iconyoujiantou"></i>	</div>';
	newcomeTopConHtml +=		'</div>';
	newcomeTopConHtml +=	'</template>';
	
	newcomeTopConHtml +=	'<template v-if="data.style == 3">';
	newcomeTopConHtml +=		'<div class="title-wrap title-wrap-3">';
	newcomeTopConHtml +=			'<div class="title-left">';
	newcomeTopConHtml +=				'<template v-for="(item, index) in list" v-if="item.style == 3">';
	newcomeTopConHtml +=					'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] == \'public\'" :src="imgUrl3" />';
	newcomeTopConHtml +=					'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] != \'public\'" :src="$parent.$parent.changeImgUrl(item.imageUrl)" />';
	newcomeTopConHtml +=				'</template>';
	newcomeTopConHtml +=			'</div>';
	newcomeTopConHtml +=		'</div>';
	newcomeTopConHtml +=	'</template>';
	/* newcomeTopConHtml +=	'<div class="more ns-red-color" v-if="listMore.title">';
	newcomeTopConHtml +=		'<span v-bind:style="{color: data.moreTextColor?data.moreTextColor:\'rgba(0,0,0,0)\'}">{{listMore.title}}</span>';
	newcomeTopConHtml +=		'<div class="right-icon" v-if="listMore.imageUrl"><img v-bind:src="$parent.$parent.changeImgUrl(listMore.imageUrl)" /></div>';
	newcomeTopConHtml +=		'<i class="iconfont iconyoujiantou" v-else v-bind:style="{color: data.moreTextColor?data.moreTextColor:\'rgba(0,0,0,0)\'}"></i>';
	newcomeTopConHtml +=	'</div>'; */
	newcomeTopConHtml +='</div>';

Vue.component("newcome-top-content", {
	data: function () {
		return {
			data: this.$parent.data,
			list: this.$parent.data.list,
			listMore: this.$parent.data.listMore,
			imgUrl1: "",
			imgUrl2: "",
			imgUrl3: ""
		}
	},
	created: function () {
		this.imgUrl1 = this.list[0].imageUrl;
		this.imgUrl2 = this.list[1].imageUrl;
		this.imgUrl3 = this.list[2].imageUrl;
		
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		verify : function () {
			var res = { code : true, message : "" };
			return res;
		},
	},
	template: newcomeTopConHtml
});

/**
 * 空的验证组件，后续如果增加业务，则更改组件
 */
var newcomeListHtml = '<div class="goods-list-edit layui-form">';

		newcomeListHtml += '<div class="layui-form-item ns-icon-radio">';
			newcomeListHtml += '<label class="layui-form-label sm">商品来源</label>';
			newcomeListHtml += '<div class="layui-input-block">';
				newcomeListHtml += '<template v-for="(item, index) in goodsSources" v-bind:k="index">';
					newcomeListHtml += '<span :class="[item.value == data.sources ? \'\' : \'layui-hide\']">{{item.text}}</span>';
				newcomeListHtml += '</template>';
				newcomeListHtml += '<ul class="ns-icon">';
					newcomeListHtml += '<li v-for="(item, index) in goodsSources" v-bind:k="index" :class="[item.value == data.sources ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="data.sources=item.value">';
						newcomeListHtml += '<img v-if="item.value == data.sources" :src="item.selectedSrc" />'
						newcomeListHtml += '<img v-else :src="item.src" />'
					newcomeListHtml += '</li>';
				newcomeListHtml += '</ul>';
			
				/* newcomeListHtml += '<template v-for="(item,index) in goodsSources" v-bind:k="index">';
					newcomeListHtml += '<div v-on:click="data.sources=item.value" v-bind:class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (data.sources==item.value) }"><i class="layui-anim layui-icon">&#xe63f;</i><div>{{item.text}}</div></div>';
				newcomeListHtml += '</template>'; */
			newcomeListHtml += '</div>';
		newcomeListHtml += '</div>';
		
		newcomeListHtml += '<div class="layui-form-item" v-if="data.sources == \'diy\'">';
			newcomeListHtml += '<label class="layui-form-label sm">手动选择</label>';
			newcomeListHtml += '<div class="layui-input-block">';
				newcomeListHtml += '<a href="#" class="ns-input-text selected-style" v-on:click="addGoods">选择<i class="layui-icon layui-icon-right"></i></a>';
			newcomeListHtml += '</div>';
		newcomeListHtml += '</div>';
		
		/* newcomeListHtml += '<div class="layui-form-item" v-show="data.sources == \'default\'">';
			newcomeListHtml += '<label class="layui-form-label sm">商品数量</label>';
			newcomeListHtml += '<div class="layui-input-block">';
				newcomeListHtml += '<input type="number" class="layui-input goods-account" v-on:keyup="shopNum" v-model="data.goodsCount"/>';
			newcomeListHtml += '</div>';
		newcomeListHtml += '</div>';
		
		newcomeListHtml += '<div class="layui-form-item" v-show="data.sources == \'default\'">';
			newcomeListHtml += '<label class="layui-form-label sm"></label>';
			newcomeListHtml += '<div class="layui-input-block">';
				newcomeListHtml += '<template v-for="(item,index) in goodsCount" v-bind:k="index">';
					newcomeListHtml += '<div v-on:click="data.goodsCount=item" v-bind:class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (data.goodsCount==item) }"><i class="layui-anim layui-icon">&#xe63f;</i><div>{{item}}</div></div>';
				newcomeListHtml += '</template>';
			newcomeListHtml += '</div>';
		newcomeListHtml += '</div>'; */
		
		newcomeListHtml += '<slide v-bind:data="{ field : \'goodsCount\', label: \'商品数量\', max: 9, min: 1}" v-show="data.sources == \'default\'"></slide>';

		// newcomeListHtml += '<p class="hint">商品数量选择 0 时，前台会自动上拉加载更多</p>';
		
	newcomeListHtml += '</div>';

var select_goods_list = []; //配合商品选择器使用
Vue.component("newcome-list", {
	template: newcomeListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			goodsSources: [
				{
					text: "默认",
					value: "default",
					src: newcomeResourcePath + "/new_come/img/goods.png",
					selectedSrc: newcomeResourcePath + "/newcome/img/goods_1.png"
				},
				{
					text : "手动选择",
					value : "diy",
					src: newcomeResourcePath + "/new_come/img/manual.png",
					selectedSrc: newcomeResourcePath + "/new_come/img/manual_1.png"
				}
			],
			categoryList: [],
			isLoad: false,
			isShow: false,
			selectIndex: 0,//当前选中的下标
			goodsCount: [6, 12, 18, 24, 30],
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		shopNum: function () {
			if (this.$parent.data.goodsCount > 50) {
				layer.msg("商品数量最多为50");
				this.$parent.data.goodsCount = 50;
			}
			if (this.$parent.data.goodsCount.length > 0 && this.$parent.data.goodsCount < 1) {
				layer.msg("商品数量不能小于0");
				this.$parent.data.goodsCount = 1;
			}
		},
		verify: function () {
			var res = {code: true, message: ""};
			if (this.data.goodsCount.length === 0) {
				res.code = false;
				res.message = "请输入商品数量";
			}
			if (this.data.goodsCount < 0) {
				res.code = false;
				res.message = "商品数量不能小于0";
			}
			if (this.data.goodsCount > 50) {
				res.message = "商品数量最多为50";
			}
			return res;
		},
		addGoods: function () {
			var self = this;

			goodsSelect(function (res) {

				// if (!res.length) return false;
				// self.$parent.data.goodsId = [];
				// for (var i = 0; i < res.length; i++) {
				// 	self.$parent.data.goodsId.push(res[i]);
				// }
				self.$parent.data.goodsId = res;

			}, self.$parent.data.goodsId, {mode: "spu", promotion: "pintuan", disabled: 0, post: post});
		}
	}
});

var newcomeStyleHtml = '<div class="layui-form-item">';
		newcomeStyleHtml += '<label class="layui-form-label sm">选择风格</label>';
		newcomeStyleHtml += '<div class="layui-input-block">';
			// newcomeStyleHtml += '<span>{{data.styleName}}</span>';
			newcomeStyleHtml += '<div v-if="data.styleName" class="ns-input-text ns-text-color selected-style" v-on:click="selectGroupbuyStyle">{{data.styleName}} <i class="layui-icon layui-icon-right"></i></div>';
			newcomeStyleHtml += '<div v-else class="ns-input-text selected-style" v-on:click="selectGroupbuyStyle">选择 <i class="layui-icon layui-icon-right"></i></div>';
		newcomeStyleHtml += '</div>';
	newcomeStyleHtml += '</div>';

Vue.component("newcome-style", {
	template: newcomeStyleHtml,
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
		selectGroupbuyStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area:['930px','630px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .pintuan-list-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$(".layui-layer-content input[name='style_name']").val(self.data.styleName);
					$("body").on("click", ".layui-layer-content .style-list-con-pintuan .style-li-pintuan", function () {
						$(this).addClass("selected ns-border-color").siblings().removeClass("selected ns-border-color");
						$(".layui-layer-content input[name='style']").val($(this).index() + 1);
						$(".layui-layer-content input[name='style_name']").val($(this).find("span").text());
					});
				},
				yes: function (index, layero) {
					self.data.style = $(".layui-layer-content input[name='style']").val();
					self.data.styleName = $(".layui-layer-content input[name='style_name']").val();
					layer.closeAll()
				}
			});
		},
	}
})

// 图片上传
var newcomeTopHtml = '<ul class="fenxiao-addon-title">';
		newcomeTopHtml += '<li>';
			newcomeTopHtml += '<template v-if="data.style == 1">';
				newcomeTopHtml += '<div class="layui-form-item">';
					newcomeTopHtml += '<label class="layui-form-label sm">左侧图标</label>';
					newcomeTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
						newcomeTopHtml += '<div class="layui-input-block ns-img-upload">';
							newcomeTopHtml += '<img-sec-upload v-bind:data="{ data : item, text: \'\' }"></img-sec-upload>';
						newcomeTopHtml += '</div>';
					newcomeTopHtml += '</template>';
					newcomeTopHtml += '<div class="ns-word-aux ns-diy-word-aux">建议上传图标大小：125px * 30px</div>';
				newcomeTopHtml += '</div>';
			newcomeTopHtml += '</template>';
			
			newcomeTopHtml += '<template v-if="data.style == 2">';
				newcomeTopHtml += '<div class="layui-form-item">';
					newcomeTopHtml += '<label class="layui-form-label sm">左侧图标</label>';
					newcomeTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
						newcomeTopHtml += '<div class="layui-input-block ns-img-upload">';
							newcomeTopHtml += '<img-sec-upload v-bind:data="{ data : item, text: \'\' }"></img-sec-upload>';
						newcomeTopHtml += '</div>';
					newcomeTopHtml += '</template>';
					newcomeTopHtml += '<div class="ns-word-aux ns-diy-word-aux">建议上传图片大小：131px * 37px</div>';
				newcomeTopHtml += '</div>';
			newcomeTopHtml += '</template>';
			
			newcomeTopHtml += '<template v-if="data.style == 3">';
				newcomeTopHtml += '<div class="layui-form-item">';
					newcomeTopHtml += '<label class="layui-form-label sm">顶部图标</label>';
					newcomeTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
						newcomeTopHtml += '<div class="layui-input-block ns-img-upload">';
							newcomeTopHtml += '<img-sec-upload v-bind:data="{ data : item, text: \'\' }"></img-sec-upload>';
						newcomeTopHtml += '</div>';
					newcomeTopHtml += '</template>';
					newcomeTopHtml += '<div class="ns-word-aux ns-diy-word-aux">建议上传图片大小：174px * 37px</div>';
				newcomeTopHtml += '</div>';
			newcomeTopHtml += '</template>';

			newcomeTopHtml += '<template v-if="data.style == 4">';
				newcomeTopHtml += '<div class="layui-form-item">';
					newcomeTopHtml += '<label class="layui-form-label sm">左侧图标</label>';
					newcomeTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
						newcomeTopHtml += '<div class="layui-input-block ns-img-upload">';
							newcomeTopHtml += '<img-sec-upload v-bind:data="{ data : item, text: \'\' }"></img-sec-upload>';
						newcomeTopHtml += '</div>';
					newcomeTopHtml += '</template>';
					newcomeTopHtml += '<div class="ns-word-aux ns-diy-word-aux">建议上传图标大小：125px * 30px</div>';
				newcomeTopHtml += '</div>';
				
				newcomeTopHtml += '<div class="content-block">';
					newcomeTopHtml += '<div class="layui-form-item">';
						newcomeTopHtml += '<label class="layui-form-label sm">标题内容</label>';
						newcomeTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
							newcomeTopHtml += '<div class="layui-input-block">';
								newcomeTopHtml += '<input type="text" name=\'title\' v-model="item.title" class="layui-input" />';
							newcomeTopHtml += '</div>';
						newcomeTopHtml += '</template>';
					newcomeTopHtml += '</div>';
				newcomeTopHtml += '</div>';
			newcomeTopHtml += '</template>';
			// newcomeTopHtml += '<color v-bind:data="{ field : \'titleTextColor\', label : \'标题颜色\', defaultcolor: \'#000\' }"></color>';
		newcomeTopHtml += '</li>';
		
		/* newcomeTopHtml += '<li>';
			newcomeTopHtml += '<div class="content-block">';
				newcomeTopHtml += '<div class="layui-form-item">';
					newcomeTopHtml += '<label class="layui-form-label sm">文本内容</label>';
					newcomeTopHtml += '<div class="layui-input-block">';
						newcomeTopHtml += '<input type="text" name=\'title\' v-model="listMore.title" class="layui-input" />';
					newcomeTopHtml += '</div>';
				newcomeTopHtml += '</div>';
				newcomeTopHtml += '<color v-bind:data="{ field : \'moreTextColor\', defaultcolor: \'#858585\' }"></color>';
				
			newcomeTopHtml += '</div>';
		newcomeTopHtml += '</li>'; */
	newcomeTopHtml += '</ul>';

Vue.component("newcome-top-list",{
	template : newcomeTopHtml,
	data : function(){
		return {
            data : this.$parent.data,
			list : this.$parent.data.list,
			listMore: this.$parent.data.listMore
		};
	},
	created : function(){
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	watch : {

	},
	methods : {
		verify:function () {
			var res = { code : true, message : "" };
			var _self = this;
			$(".draggable-element[data-index='" + this.data.index + "'] .graphic-navigation .graphic-nav-list>ul>li").each(function(index){
				
				if(_self.selectedTemplate == "imageNavigation"){
					$(this).find("input[name='title']").removeAttr("style");//清空输入框的样式
					//检测是否有未上传的图片
					if(_self.list[index].imageUrl == ""){
						res.code = false;
						res.message = "请选择一张图片";
						$(this).find(".error-msg").text("请选择一张图片").show();
						return res;
					}else{
						$(this).find(".error-msg").text("").hide();
					}
				}else{
					if(_self.list[index].title == ""){
						res.code = false;
						res.message = "请输入标题";
						$(this).find("input[name='title']").attr("style","border-color:red !important;").focus();
						$(this).find(".error-msg").text("请输入标题").show();
						return res;
					}else{
						$(this).find("input[name='title']").removeAttr("style");
						$(this).find(".error-msg").text("").hide();
					}
				}
			});
			return res;
		}
	}
});


// 背景颜色可选
var newcomeColorHtml = '<div class="layui-form-item ns-bg-select">';
	newcomeColorHtml +=	 '<label class="layui-form-label sm">背景颜色</label>';
	newcomeColorHtml +=	 '<div class="layui-input-block">';
	newcomeColorHtml +=		 '<ul class="ns-bg-select-ul">';
	newcomeColorHtml +=			 '<li v-for="(item, index) in colorList" v-bind:k="index" :class="[item.className == data.bgSelect ? \'ns-text-color ns-border-color\' : \'\']" @click="data.bgSelect = item.className">';
	newcomeColorHtml +=				 '<div :style="{background: item.color}"></div>';
	newcomeColorHtml +=			 '</li>';
	newcomeColorHtml +=		 '</ul>';
	newcomeColorHtml +=	 '</div>';
	newcomeColorHtml += '</div>';

Vue.component("newcome-color", {
	template: newcomeColorHtml,
	data: function () {
		return {
			data: this.$parent.data,
			colorList: [
				{name: "红", className: "red", color: "#FFD7D7"},
				{name: "蓝", className: "blue", color: "#D7FAFF"},
				{name: "黄", className: "yellow", color: "#FFF4E0"},
				{name: "紫", className: "violet", color: "#F9E5FF"}
			]
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
		}
	},
});	


// 切换方式
var changeType = '<div class="layui-form-item ns-icon-radio">';
		changeType += '<label class="layui-form-label sm">滑动方式</label>';
		changeType += '<div class="layui-input-block align-right">';
			changeType += '<template v-for="(item,index) in changeTypeList" v-bind:k="index">';
				changeType += '<div v-on:click="data.changeType=item.value" v-bind:class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (data.changeType==item.value) }"><i class="layui-anim layui-icon">&#xe63f;</i><div>{{item.name}}</div></div>';
			changeType += '</template>';
		changeType += '</div>';
	/* changeType +=	 '<label class="layui-form-label sm">滑动方式</label>';
	changeType +=	 '<div class="layui-input-block">';
	changeType +=		 '<template v-for="(item, index) in changeTypeList" v-bind:k="index">';
	changeType +=			 '<span :class="[item.value == data.changeType ? \'\' : \'layui-hide\']">{{item.name}}</span>';
	changeType +=		 '</template>';
	changeType +=		 '<ul class="ns-icon">';
	changeType +=			 '<li v-for="(item, index) in changeTypeList" v-bind:k="index" :class="[item.value == data.changeType ? \'ns-text-color ns-border-color\' : \'\']" @click="data.changeType = item.value">';
	changeType +=				 '<img v-if="item.value == data.changeType" :src="item.selectedSrc" />'
	changeType +=				 '<img v-else :src="item.src" />'
	changeType +=			 '</li>';
	changeType +=		 '</ul>';
	changeType +=	 '</div>'; */
	changeType += '</div>';

Vue.component("change-type", {
	template: changeType,
	data: function () {
		return {
			data: this.$parent.data,
			changeTypeList: [
				{name: "平移滑动", value: 1, src: pintuanResourcePath + "/pintuan/img/manual.png", selectedSrc: pintuanResourcePath + "/pintuan/img/manual_1.png"},
				{name: "切屏滑动", value: 2, src: pintuanResourcePath + "/pintuan/img/manual.png", selectedSrc: pintuanResourcePath + "/pintuan/img/manual_1.png"},
			]
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
		}
	},
});