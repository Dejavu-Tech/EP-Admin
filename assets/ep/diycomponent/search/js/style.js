
var searchHtml = '<div class="layui-form-item ns-icon-radio">';
	searchHtml += '<label class="layui-form-label sm">{{data.label}}</label>';
	searchHtml +=	 '<div class="layui-input-block">';
	searchHtml += 		 '<template v-for="(item,index) in list" v-bind:k="index">';
	searchHtml += 		 	'<span v-if="parent[data.field]==item.value">{{item.label}}</span>';
	searchHtml += 		 '</template>';
	searchHtml +=	 	'<ul class="ns-icon">';
	searchHtml +=		 	'<template v-for="(item,index) in list" v-bind:k="index">';
	searchHtml +=		 		'<li v-on:click="parent[data.field]=item.value" :class="{\'ns-text-color ns-border-color ns-bg-color-diaphaneity\':parent[data.field]==item.value}">';
	searchHtml +=		 			'<img :src="item.icon_img_active" v-if="parent[data.field]==item.value"/>';
	searchHtml +=		 			'<img :src="item.icon_img" v-else />';
	searchHtml +=		 		'</li>';
	searchHtml +=		 	'</template>';
	searchHtml +=	 	'</ul>';
	searchHtml +=	 '</div>';
	searchHtml += '</div>';
Vue.component("goods-search", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "textAlign",
					label: "文本位置"
				};
			}
		}
	},
	data: function () {
		return {
			list: [
				{
					label: "居左", 
					value: "left",
					icon_img:searchResourcePath + "/search/img/text_left.png",
					icon_img_active:searchResourcePath + "/search/img/text_left_hover.png"
				},
				{
					label: "居中", 
					value: "center",
					icon_img:searchResourcePath + "/search/img/text_right.png",
					icon_img_active:searchResourcePath + "/search/img/text_right_hover.png"
				},
			],
			parent: this.$parent.data,
		};
	},
	created: function () {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
		if (this.data.label == undefined) this.data.label = "文本位置";
		if (this.data.field == undefined) this.data.field = "textAlign";
		
		var self = this;
		setTimeout(function () {
			layui.use(['form'], function() {
				self.form = layui.form;
				self.form.render();
			});
		},10);
		//设置默认logo
		var error_img =  $('input[name="d_elem"]').val()
		if(!this.parent.searchImg){this.parent.searchImg = error_img;}
		
	},
	watch: {
		data: function (val, oldVal) {
			if (val.field == undefined) val.field = oldVal.field;
			if (val.label == undefined) val.label = "文本位置";
		},
	},
	methods: {
		verify : function () {
			var res = { code : true, message : "" };
			return res;
		}
	},
	template: searchHtml
});


var borderHtml = '<div class="layui-form-item ns-icon-radio">';
	borderHtml += 	'<label class="layui-form-label sm">{{data.label}}</label>';
	borderHtml +=	 '<div class="layui-input-block">';
	borderHtml += 		 '<template v-for="(item,index) in list" v-bind:k="index">';
	borderHtml += 		 	'<span v-if="parent[data.field]==item.value">{{item.label}}</span>';
	borderHtml += 		 '</template>';
	borderHtml +=	 	'<ul class="ns-icon">';
	borderHtml +=		 	'<template v-for="(item,index) in list" v-bind:k="index">';
	borderHtml +=		 		'<li v-on:click="parent[data.field]=item.value" :class="{\'ns-text-color ns-border-color ns-bg-color-diaphaneity\':parent[data.field]==item.value}">';
	borderHtml +=		 			'<img :src="item.icon_img_active" v-if="parent[data.field]==item.value"/>';
	borderHtml +=		 			'<img :src="item.icon_img" v-else />';
	borderHtml +=		 		'</li>';
	borderHtml +=		 	'</template>';
	borderHtml +=	 	'</ul>';
	borderHtml +=	 '</div>';
	borderHtml += '</div>';

Vue.component("search-border", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "borderType",
					label: "框体样式"
				};
			}
		}
	},
	data: function () {
		return {
			list: [
				{
					label: "方形", 
					value: 1,
					icon_img:searchResourcePath + "/search/img/border1.png",
					icon_img_active:searchResourcePath + "/search/img/border1_hover.png"
				},
				{
					label: "圆形",
					value: 2,
					icon_img:searchResourcePath + "/search/img/border2.png",
					icon_img_active:searchResourcePath + "/search/img/border2_hover.png"
				},
			],
			parent: this.$parent.data,
		};
	},
	created: function () {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
		if (this.data.label == undefined) this.data.label = "框体样式";
		if (this.data.field == undefined) this.data.field = "borderType";
		
		var self = this;
		setTimeout(function () {
			layui.use(['form'], function() {
				self.form = layui.form;
				self.form.render();
			});
		},10);
	},
	watch: {
		data: function (val, oldVal) {
			if (val.field == undefined) val.field = oldVal.field;
			if (val.label == undefined) val.label = "框体样式";
		},
	},
	methods: {
		verify : function () {
			var res = { code : true, message : "" };
			return res;
		},
	},
	template: borderHtml
});

var typeHtml = '<div class="layui-form-item">';
	typeHtml +=	 	'<label class="layui-form-label sm">{{data.label}}</label>';
	typeHtml +=	 	'<div class="layui-input-block">';
	typeHtml +=			 '<template v-for="(item,index) in list" v-bind:k="index">';
	typeHtml +=				 '<div v-on:click="parent[data.field]=item.value" v-if="parent[data.field]==item.value"><div>{{item.label}}</div></div>';
	typeHtml +=			 '</template>';
	typeHtml +=	 	'</div>';
	typeHtml +=		'<div class="search_type">';
	typeHtml +=			 '<template v-for="(item,index) in list" v-bind:k="index">';
	typeHtml +=		 		'<div class="search_type_left" v-on:click="parent[data.field]=item.value,parent.searchStyle=1" :class="{\'active\':parent[data.field]==item.value}">';
	typeHtml +=		 			'<img :src="item.icon_img_active" v-if="parent[data.field]==item.value"/>';
	typeHtml +=		 			'<img :src="item.icon_img" v-else />';
	typeHtml +=		 		'</div>';
	typeHtml +=			 '</template>';
	typeHtml +=			 '</div>';
	typeHtml +=		 '<div class="search_logo" v-if="parent[data.field] == 2">';
	typeHtml +=		 	'<div class="" ><img-upload v-bind:data="{ data : parent, field : \'searchImg\' }" v-bind:isShow="!1"></img-upload></div>';
	typeHtml +=		 	'<div class="desc" >';
	typeHtml +=		 		'<div class="tip" >最多可添加一张图片</div>';
	typeHtml +=		 		'<div class="spec">85px*30px</div>';
	typeHtml +=	 		'</div>';
	typeHtml +=	 	'</div>';
	
	typeHtml += '</div>';
	