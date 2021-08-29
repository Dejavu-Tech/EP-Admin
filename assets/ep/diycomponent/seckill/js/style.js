var seckillHtml = '<div class="layui-form-item">';
		seckillHtml += '<label class="layui-form-label sm">选择风格</label>';
		seckillHtml += '<div class="layui-input-block choose-style">';
			seckillHtml += '<div v-if="data.styleName" class="ns-input-text ns-text-color selected-style" v-on:click="selectTestStyle">{{data.styleName}} <i class="layui-icon layui-icon-right"></i></div>';
			seckillHtml += '<div v-else class="ns-input-text selected-style" v-on:click="selectTestStyle">选择 <i class="layui-icon layui-icon-right"></i></div>';
		seckillHtml += '</div>';
	seckillHtml += '</div>';

Vue.component("seckill-style", {
	template: seckillHtml,
	data: function() {
		return {
			data: this.$parent.data,
		}
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
		selectTestStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area:['930px','630px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .seckill-list-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$(".layui-layer-content input[name='style_name']").val(self.data.styleName);
					$("body").on("click", ".layui-layer-content .style-list-con-seckill .style-li-seckill", function () {
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

// 多选
var seckillContentHtml = '<div class="layui-form-item goods-show-box ns-checkbox-wrap">';
	seckillContentHtml +=	'<div class="layui-input-block">';
		seckillContentHtml +=	'<div class="layui-input-inline-checkbox">';
		seckillContentHtml +=		'<span>商品名称</span>';
		seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsName\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodName == 1)}" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
		seckillContentHtml +=	'</div>';
		
		seckillContentHtml +=	'<div class="layui-input-inline-checkbox">';
		seckillContentHtml +=		'<span>商品价格</span>';
		seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsPrice\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodsPrice == 1)}" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
		seckillContentHtml +=	'</div>';
		
		seckillContentHtml +=	'<div class="layui-input-inline-checkbox">';
		seckillContentHtml +=		'<span>商品原价</span>';
		seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsPrimary\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodsPrimary == 1)}" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
		seckillContentHtml +=	'</div>';
	seckillContentHtml +=	'</div>';
	/* seckillContentHtml +=	'<div class="layui-input-inline">';
	seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsName\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodsName == 1)}" lay-skin="primary"><span>商品名称</span><i class="layui-icon layui-icon-ok"></i></div>';
	// seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsDesc\', data.isShowGoodsDesc)" id="isShowGoodsDesc" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodsDesc == 1)}" lay-skin="primary"><span>商品描述</span><i class="layui-icon layui-icon-ok"></i></div>';
	seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsPrice\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodsPrice == 1)}" lay-skin="primary"><span>商品价格</span><i class="layui-icon layui-icon-ok"></i></div>';
	seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsPrimary\')" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodsPrimary == 1)}" lay-skin="primary"><span>商品原价</span><i class="layui-icon layui-icon-ok"></i></div>';
	// seckillContentHtml +=		'<div v-on:click="changeStatus(\'isShowGoodsStock\', data.isShowGoodsStock)" id="isShowGoodsStock" class="layui-unselect layui-form-checkbox" v-bind:class="{\'layui-form-checked\': (data.isShowGoodsStock == 1)}" lay-skin="primary"><span>剩余库存</span><i class="layui-icon layui-icon-ok"></i></div>';
	seckillContentHtml +=	'</div>'; */
	seckillContentHtml += '</div>';

Vue.component("seckill-content", {
	template: seckillContentHtml,
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
		changeStatus: function(field) {
			this.$parent.data[field] = this.$parent.data[field] ? 0 : 1;
		}
	}
});


// 顶部内容组件
var seckillTopConHtml = '<div>';
	seckillTopConHtml += 	'<template v-if="data.style == 1">';
	seckillTopConHtml += 		'<div class="goods-head">';
	seckillTopConHtml +=			'<div class="title-wrap">';
	// seckillTopConHtml +=				'<div class="left-icon" v-if="list.imageUrl"><img v-bind:src="$parent.$parent.changeImgUrl(list.imageUrl)" /></div>';
	seckillTopConHtml +=				'<template v-for="(item, index) in list" v-if="item.style == 1">';
	seckillTopConHtml +=					'<div class="left-icon" v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] == \'public\'"><img v-bind:src="imgUrl1" /></div>';
	seckillTopConHtml +=					'<div class="left-icon" v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] != \'public\'"><img v-bind:src="$parent.$parent.changeImgUrl(item.imageUrl)" /></div>';
	seckillTopConHtml +=				'</template>';
	// seckillTopConHtml +=				'<span class="name" v-bind:style="{color: data.titleTextColor?data.titleTextColor:\'rgba(0,0,0,0)\'}">{{list.title}}</span>';
	seckillTopConHtml +=				'<div class="time">距离结束<span class="hour">02</span>:<span class="minute">00</span>:<span class="second">00</span></div>';
	seckillTopConHtml +=			'</div>';
	
	/* seckillTopConHtml +=	'<div class="more ns-red-color" v-if="listMore.title">';
	seckillTopConHtml +=		'<span v-bind:style="{color: data.moreTextColor?data.moreTextColor:\'rgba(0,0,0,0)\'}">更多秒杀</span>';
	seckillTopConHtml +=		'<div class="right-icon" v-if="listMore.imageUrl"><img v-bind:src="$parent.$parent.changeImgUrl(listMore.imageUrl)" /></div>';
	seckillTopConHtml +=		'<i class="iconfont iconyoujiantou" v-else v-bind:style="{color: data.moreTextColor?data.moreTextColor:\'rgba(0,0,0,0)\'}"></i>';
	seckillTopConHtml +=	'</div>'; */
	
	seckillTopConHtml +=			'<div class="more violet" v-if="data.bgSelect==\'violet\'">';
	seckillTopConHtml +=				'<span>';
	seckillTopConHtml +=					'<span style="color: #8662FD;">更多</span>';
	seckillTopConHtml +=					'<span style="color: #627BFD;">秒杀</span>';
	seckillTopConHtml +=				'</span>';
	seckillTopConHtml +=				'<i class="iconfont iconyoujiantou" style="color: #627BFD;"></i>';
	seckillTopConHtml +=			'</div>';
	
	seckillTopConHtml +=			'<div class="more red" v-if="data.bgSelect==\'red\'">';
	seckillTopConHtml +=				'<span>';
	seckillTopConHtml +=					'<span style="color: #FF7B91;">更多</span>';
	seckillTopConHtml +=					'<span style="color: #FF5151;">秒杀</span>';
	seckillTopConHtml +=				'</span>';
	seckillTopConHtml +=				'<i class="iconfont iconyoujiantou" style="color: #FF5151;"></i>';
	seckillTopConHtml +=			'</div>';
	
	seckillTopConHtml +=			'<div class="more blue" v-if="data.bgSelect==\'blue\'">';
	seckillTopConHtml +=				'<span>';
	seckillTopConHtml +=					'<span style="color: #12D0AE;">更多</span>';
	seckillTopConHtml +=					'<span style="color: #0ECFD3;">秒杀</span>';
	seckillTopConHtml +=				'</span>';
	seckillTopConHtml +=				'<i class="iconfont iconyoujiantou" style="color: #0ECFD3;"></i>';
	seckillTopConHtml +=			'</div>';
	
	seckillTopConHtml +=			'<div class="more yellow" v-if="data.bgSelect==\'yellow\'">';
	seckillTopConHtml +=				'<span>';
	seckillTopConHtml +=					'<span style="color: #FEB632;">更多</span>';
	seckillTopConHtml +=					'<span style="color: #FE6232;">秒杀</span>';
	seckillTopConHtml +=				'</span>';
	seckillTopConHtml +=				'<i class="iconfont iconyoujiantou" style="color: #FE6232;"></i>';
	seckillTopConHtml +=			'</div>';
	seckillTopConHtml +=		'</div>';
	seckillTopConHtml +=	'</template>';
	
	seckillTopConHtml +=	'<template v-if="data.style == 2">';
	seckillTopConHtml +=		'<div class="title-wrap title-wrap-2">';
	seckillTopConHtml +=			'<div class="title-left">';
	seckillTopConHtml +=				'<template v-for="(item, index) in list" v-if="item.style == 2">';
	seckillTopConHtml +=					'<div class="img">';
	seckillTopConHtml +=						'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] == \'public\'" :src="imgUrl2" />';
	seckillTopConHtml +=						'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] != \'public\'" :src="$parent.$parent.changeImgUrl(item.imageUrl)" />';
	seckillTopConHtml +=					'</div>';
	seckillTopConHtml +=				'</template>';
	seckillTopConHtml +=				'<div class="time">';
	seckillTopConHtml +=					'<label class="font">疯抢中</label>';
	seckillTopConHtml +=					'<span class="hour">02</span>:<span class="minute">00</span>';
	seckillTopConHtml +=				'</div>';
	seckillTopConHtml +=			'</div>';
	seckillTopConHtml +=			'<div class="title-right"><span>更多秒杀</span><i class="iconfont iconyoujiantou"></i></div>';
	seckillTopConHtml +=		'</div>';
	seckillTopConHtml +=	'</template>';
	
	seckillTopConHtml +=	'<template v-if="data.style == 3">';
	seckillTopConHtml +=		'<div class="title-wrap title-wrap-3">';
	seckillTopConHtml +=			'<div class="title-left">';
	seckillTopConHtml +=				'<template v-for="(item, index) in list" v-if="item.style == 3">';
	seckillTopConHtml +=					'<div class="img">';
	seckillTopConHtml +=						'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] == \'public\'" :src="imgUrl3" />';
	seckillTopConHtml +=						'<img v-if="item.imageUrl && item.imageUrl.split(\'/\')[0] != \'public\'" :src="$parent.$parent.changeImgUrl(item.imageUrl)" />';
	seckillTopConHtml +=					'</div>';
	seckillTopConHtml +=				'</template>';
	seckillTopConHtml +=				'<div class="time">';
	seckillTopConHtml +=					'<label class="font">疯抢中</label>';
	seckillTopConHtml +=					'<span class="hour">02</span>:<span class="minute">00</span>';
	seckillTopConHtml +=				'</div>';
	seckillTopConHtml +=			'</div>';
	seckillTopConHtml +=		'</div>';
	seckillTopConHtml +=	'</template>';
	seckillTopConHtml +='</div>';

Vue.component("seckill-top-content", {
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
	template: seckillTopConHtml
});


// 图片上传
var seckillTopHtml = '<ul class="fenxiao-addon-title">';
		seckillTopHtml += '<li>';
			seckillTopHtml += '<template v-if="data.style == 1">';
				seckillTopHtml += '<div class="layui-form-item">';
					seckillTopHtml += '<label class="layui-form-label sm">秒杀图标</label>';
					// seckillTopHtml += '<div class="layui-input-block ns-img-upload">';
					// 	seckillTopHtml += '<img-sec-upload v-bind:data="{ data : list }"></img-sec-upload>';
					// seckillTopHtml += '</div>';
					seckillTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
						seckillTopHtml += '<div class="layui-input-block ns-img-upload">';
							seckillTopHtml += '<img-sec-upload v-bind:data="{ data : item, text: \'\' }"></img-sec-upload>';
						seckillTopHtml += '</div>';
					seckillTopHtml += '</template>';
					seckillTopHtml += '<div class="ns-word-aux ns-diy-word-aux">建议上传图标大小：125px * 30px</div>';
				seckillTopHtml += '</div>';
			seckillTopHtml += '</template>';
			
			seckillTopHtml += '<template v-if="data.style == 2">';
				seckillTopHtml += '<div class="layui-form-item">';
					seckillTopHtml += '<label class="layui-form-label sm">秒杀图标</label>';
					seckillTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
						seckillTopHtml += '<div class="layui-input-block ns-img-upload">';
							seckillTopHtml += '<img-sec-upload v-bind:data="{ data : item, text: \'\' }"></img-sec-upload>';
						seckillTopHtml += '</div>';
					seckillTopHtml += '</template>';
					seckillTopHtml += '<div class="ns-word-aux ns-diy-word-aux">建议上传图标大小：103px * 16px</div>';
				seckillTopHtml += '</div>';
			seckillTopHtml += '</template>';
			
			seckillTopHtml += '<template v-if="data.style == 3">';
				seckillTopHtml += '<div class="layui-form-item">';
					seckillTopHtml += '<label class="layui-form-label sm">秒杀图标</label>';
					seckillTopHtml += '<template v-for="(item, index) in list" v-if="item.style == data.style">';
						seckillTopHtml += '<div class="layui-input-block ns-img-upload">';
							seckillTopHtml += '<img-sec-upload v-bind:data="{ data : item, text: \'\' }"></img-sec-upload>';
						seckillTopHtml += '</div>';
					seckillTopHtml += '</template>';
					seckillTopHtml += '<div class="ns-word-aux ns-diy-word-aux">建议上传图标大小：174px * 17px</div>';
				seckillTopHtml += '</div>';
			seckillTopHtml += '</template>';
			/* seckillTopHtml += '<div class="content-block">';
				seckillTopHtml += '<div class="layui-form-item">';
					seckillTopHtml += '<label class="layui-form-label sm">标题</label>';
					seckillTopHtml += '<div class="layui-input-block">';
						seckillTopHtml += '<input type="text" name=\'title\' v-model="list.title" class="layui-input" />';
					seckillTopHtml += '</div>';
				seckillTopHtml += '</div>';
			seckillTopHtml += '</div>'; */
			
			// seckillTopHtml += '<color v-bind:data="{ field : \'titleTextColor\', label : \'标题颜色\', defaultcolor: \'#000\' }"></color>';
		seckillTopHtml += '</li>';
		
		/* seckillTopHtml += '<li>';
			// seckillTopHtml += '<div class="layui-form-item">';
			// 	seckillTopHtml += '<label class="layui-form-label sm">右侧图标</label>';
			// 	seckillTopHtml += '<div class="layui-input-block">';
			// 		seckillTopHtml += '<img-upload v-bind:data="{ data : item }"></img-upload>';
			// 	seckillTopHtml += '</div>';
			// seckillTopHtml += '</div>';
			
			seckillTopHtml += '<div class="content-block">';
				seckillTopHtml += '<div class="layui-form-item">';
					seckillTopHtml += '<label class="layui-form-label sm">文本内容</label>';
					seckillTopHtml += '<div class="layui-input-block">';
						seckillTopHtml += '<input type="text" name=\'title\' v-model="listMore.title" class="layui-input" />';
					seckillTopHtml += '</div>';
				seckillTopHtml += '</div>';
				seckillTopHtml += '<color v-bind:data="{ field : \'moreTextColor\', defaultcolor: \'#858585\' }"></color>';
				
				// seckillTopHtml += '<nc-link v-bind:data="{ field : $parent.data.list[index].link }"></nc-link>';
				
			seckillTopHtml += '</div>';
		seckillTopHtml += '</li>'; */
	seckillTopHtml += '</ul>';

Vue.component("seckill-top-list",{
	template : seckillTopHtml,
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
var seckillColorHtml = '<div class="layui-form-item ns-bg-select">';
	seckillColorHtml +=	 '<label class="layui-form-label sm">背景颜色</label>';
	seckillColorHtml +=	 '<div class="layui-input-block">';
	seckillColorHtml +=		 '<ul class="ns-bg-select-ul">';
	seckillColorHtml +=			 '<li v-for="(item, index) in colorList" v-bind:k="index" :class="[item.className == data.bgSelect ? \'ns-text-color ns-border-color\' : \'\']" @click="data.bgSelect = item.className">';
	seckillColorHtml +=				 '<div :style="{background: item.color}"></div>';
	seckillColorHtml +=			 '</li>';
	seckillColorHtml +=		 '</ul>';
	seckillColorHtml +=	 '</div>';
	seckillColorHtml += '</div>';

Vue.component("seckill-color", {
	template: seckillColorHtml,
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
var seckillChangeType = '<div class="layui-form-item ns-icon-radio">';
		seckillChangeType += '<label class="layui-form-label sm">滑动方式</label>';
		seckillChangeType += '<div class="layui-input-block align-right">';
			seckillChangeType += '<template v-for="(item,index) in changeTypeList" v-bind:k="index">';
				seckillChangeType += '<div v-on:click="data.changeType=item.value" v-bind:class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (data.changeType==item.value) }"><i class="layui-anim layui-icon">&#xe63f;</i><div>{{item.name}}</div></div>';
			seckillChangeType += '</template>';
		seckillChangeType += '</div>';

	/* seckillChangeType +=	 '<label class="layui-form-label sm">滑动方式</label>';
	seckillChangeType +=	 '<div class="layui-input-block">';
	seckillChangeType +=		 '<template v-for="(item, index) in changeTypeList" v-bind:k="index">';
	seckillChangeType +=			 '<span :class="[item.value == data.changeType ? \'\' : \'layui-hide\']">{{item.name}}</span>';
	seckillChangeType +=		 '</template>';
	seckillChangeType +=		 '<ul class="ns-icon">';
	seckillChangeType +=			 '<li v-for="(item, index) in changeTypeList" v-bind:k="index" :class="[item.value == data.changeType ? \'ns-text-color ns-border-color\' : \'\']" @click="data.changeType = item.value">';
	seckillChangeType +=				 '<img v-if="item.value == data.changeType" :src="item.selectedSrc" />'
	seckillChangeType +=				 '<img v-else :src="item.src" />'
	seckillChangeType +=			 '</li>';
	seckillChangeType +=		 '</ul>';
	seckillChangeType +=	 '</div>'; */
	seckillChangeType += '</div>';

Vue.component("seckill-change-type", {
	template: seckillChangeType,
	data: function () {
		return {
			data: this.$parent.data,
			changeTypeList: [
				{name: "平移滑动", value: 1, src: seckillResourcePath + "/seckill/img/manual.png", selectedSrc: seckillResourcePath + "/seckill/img/manual_1.png"},
				{name: "切屏滑动", value: 2, src: seckillResourcePath + "/seckill/img/manual.png", selectedSrc: seckillResourcePath + "/seckill/img/manual_1.png"},
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