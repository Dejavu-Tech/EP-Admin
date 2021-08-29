// 图文导航·组件
var graphicNavPreviewHtml = '<div v-bind:id="id" class="graphic-nav">';
		graphicNavPreviewHtml += '<div class="wrap" v-bind:style="{}">';
			graphicNavPreviewHtml += '<div v-for="(item, index) in list" v-if="index<data.showType*2" class="item" :style="{width: 333/data.showType + \'px\'}">';
				graphicNavPreviewHtml += '<a href="javascript:;">';
					graphicNavPreviewHtml += '<div v-show="data.selectedTemplate ==\'imageNavigation\'" style="width: 35px; height: 35px; line-height: 35px; display: inline-block;"><img style="max-width: 100%; max-height: 100%;" v-bind:src="item.imageUrl? $parent.$parent.changeImgUrl(item.imageUrl) : \'' + '' + "/assets/ep/images/diy/crack_figure.png" + '\'" /></div>';
					graphicNavPreviewHtml += '<span v-show="item.title" v-bind:style="{ color: data.textColor, opacity: data.textColor ? 1 : 0, marginTop: data.selectedTemplate ==\'imageNavigation\' ? \'10px\' : \'\' }">{{item.title}}</span>';
				graphicNavPreviewHtml += '</a>';
			graphicNavPreviewHtml += '</div>';
		graphicNavPreviewHtml += '</div>';
	graphicNavPreviewHtml += '</div>';

Vue.component("graphic-nav", {
	data: function () {
		return {
			id: "graphic_nav_" + get_math_rant(10),
			data: this.$parent.data,
			list: this.$parent.data.list,
			selectedTemplate : this.$parent.data.selectedTemplate
		}
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
	},
	template: graphicNavPreviewHtml
});


/**
 * [图片导航的图片]·组件
 */
var graphicNavListHtml = '<div class="graphic-nav-list">';

		graphicNavListHtml += '<div class="layui-form-item ns-icon-radio">';
			graphicNavListHtml += '<label class="layui-form-label sm">选择模板</label>';
			graphicNavListHtml += '<div class="layui-input-block">';
				graphicNavListHtml += '<template v-for="(item, index) in selectedTemplateList" v-bind:k="index">';
					graphicNavListHtml += '<span :class="[item.value == selectedTemplate ? \'\' : \'layui-hide\']">{{item.name}}</span>';
				graphicNavListHtml += '</template>';

				graphicNavListHtml += '<ul class="ns-icon">';
					graphicNavListHtml += '<li v-for="(item, index) in selectedTemplateList" v-bind:k="index" :class="[item.value == selectedTemplate ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="selectedTemplate =item.value">';
						graphicNavListHtml += '<img v-if="item.value == selectedTemplate" :src="item.selectedSrc" />'
						graphicNavListHtml += '<img v-else :src="item.src" />'
					graphicNavListHtml += '</li>';
				graphicNavListHtml += '</ul>';
			graphicNavListHtml += '</div>';
		graphicNavListHtml += '</div>';

		graphicNavListHtml += '<div class="layui-form-item ns-icon-radio">';
			graphicNavListHtml += '<label class="layui-form-label sm">展示形式</label>';
			graphicNavListHtml += '<div class="layui-input-block">';
				graphicNavListHtml += '<template v-for="(item, index) in showTypeList" v-bind:k="index">';
					graphicNavListHtml += '<span :class="[item.value == data.showType ? \'\' : \'layui-hide\']">{{item.name}}</span>';
				graphicNavListHtml += '</template>';

				graphicNavListHtml += '<ul class="ns-icon">';
					graphicNavListHtml += '<li v-for="(item, index) in showTypeList" v-bind:k="index" :class="[item.value == data.showType ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="data.showType = item.value">';
						graphicNavListHtml += '<img v-if="item.value == data.showType" :src="item.selectedSrc" />'
						graphicNavListHtml += '<img v-else :src="item.src" />'
					graphicNavListHtml += '</li>';
				graphicNavListHtml += '</ul>';
			graphicNavListHtml += '</div>';
		graphicNavListHtml += '</div>';

		graphicNavListHtml += '<div class="template-edit-wrap">';
			graphicNavListHtml += '<ul>';
				graphicNavListHtml += '<p class="hint">建议上传尺寸相同的图片(60px * 60px)</p>';
				graphicNavListHtml += '<li v-for="(item,index) in list" v-bind:key="index">';
					graphicNavListHtml += '<img-sec-upload v-bind:data="{ data: item }" v-bind:condition="$parent.data.selectedTemplate == \'imageNavigation\'"></img-sec-upload>';

					graphicNavListHtml += '<div class="content-block" v-bind:class="$parent.data.selectedTemplate">';
						graphicNavListHtml += '<div class="layui-form-item">';
							graphicNavListHtml += '<label class="layui-form-label sm">标题</label>';
							graphicNavListHtml += '<div class="layui-input-block">';
								graphicNavListHtml += '<input type="text" name=\'title\' v-model="item.title" class="layui-input" />';
							graphicNavListHtml += '</div>';
						graphicNavListHtml += '</div>';

						graphicNavListHtml += '<sel-link v-bind:data="{ field : $parent.data.list[index].link }"></sel-link>';
					graphicNavListHtml += '</div>';

					graphicNavListHtml += '<i class="del" v-on:click="list.splice(index,1)" data-disabled="1">x</i>';
					graphicNavListHtml += '<div class="error-msg"></div>';
				graphicNavListHtml += '</li>';

				graphicNavListHtml += '<div class="add-item ns-text-color" v-on:click="list.push({ imageUrl : \'\', title : \'\', link : {} })">';
					graphicNavListHtml += '<i>+</i>';
					graphicNavListHtml += '<span>添加一个图文导航</span>';
				graphicNavListHtml += '</div>';
			graphicNavListHtml += '</ul>';
		graphicNavListHtml += '</div>';

		graphicNavListHtml += '<div class="template-edit-wrap">';
			graphicNavListHtml += '<color v-bind:data="{ defaultcolor: \'#666666\' }"></color>';
			graphicNavListHtml += '<nav-radius></nav-radius>';
		graphicNavListHtml += '</div>';

		graphicNavListHtml += '<div class="template-edit-title">';
			graphicNavListHtml += '<h3>其他设置</h3>';
			graphicNavListHtml += '<i class="layui-icon layui-icon-down" onclick="closeBox(this)"></i>';
		graphicNavListHtml += '</div>';

		graphicNavListHtml += '<div class="template-edit-wrap">';
			graphicNavListHtml += '<color v-bind:data="{ field : \'backgroundColor\', label : \'背景颜色\' }"></color>';

			graphicNavListHtml += '<div class="text-slide">';
				graphicNavListHtml += '<slide v-bind:data="{ field : \'marginTop\', label : \'页面间距\' }"></slide>';
			graphicNavListHtml += '</div>';
		graphicNavListHtml += '</div>';

	graphicNavListHtml += '</div>';

Vue.component("graphic-nav-list",{

	data : function(){
		return {
            data : this.$parent.data,
			showAddItem : true,
			list : this.$parent.data.list,
			scrollSettingList : [{
				name : "固定",
				value : "fixed",
				max : 5
			},{
				name : "横向滚动",
				value : "horizontal-scroll",
				max : 20
			}],
			scrollSetting : this.$parent.data.scrollSetting,
			imageScale : this.$parent.data.imageScale,
			padding : this.$parent.data.padding,
			selectedTemplate : this.$parent.data.selectedTemplate,
			maxTip : 5,//最大上传数量提示
			selectedTemplateList : [{
				name : '图片导航',
				value : 'imageNavigation',
				src : graphicNavResourcePath + "/graphic_nav/img/img.png",
				selectedSrc: graphicNavResourcePath + "/graphic_nav/img/img_1.png"
			},{
				name : '文字导航',
				value : 'textNavigation',
				src : graphicNavResourcePath + "/graphic_nav/img/font.png",
				selectedSrc: graphicNavResourcePath + "/graphic_nav/img/font_1.png"
			}],
			showTypeList: [{
				name : '一行三个',
				value : '3',
				src : graphicNavResourcePath + "/graphic_nav/img/three.png",
				selectedSrc: graphicNavResourcePath + "/graphic_nav/img/three_1.png"
			}, {
				name : '一行四个',
				value : '4',
				src : graphicNavResourcePath + "/graphic_nav/img/four.png",
				selectedSrc: graphicNavResourcePath + "/graphic_nav/img/four_1.png"
			}, {
				name : '一行五个',
				value : '5',
				src : graphicNavResourcePath + "/graphic_nav/img/five.png",
				selectedSrc: graphicNavResourcePath + "/graphic_nav/img/five_1.png"
			}]
		};
	},

	created : function(){
		this.changeShowAddItem();
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},

	watch : {
		list : function(){
			this.changeShowAddItem();
		},
		scrollSetting : function(){
			//更新父级对象
			this.$parent.data.scrollSetting = this.scrollSetting;

			//当前滚动方式切换到固定时，要检测当前集合是否超过最多限制max
			if(this.scrollSetting == this.scrollSettingList[0].value && this.list.length>this.scrollSettingList[0].max){
				this.list.splice(5,this.scrollSettingList[0].max);
			}
			this.changeShowAddItem();

		},
		selectedTemplate : function(){
			this.$parent.data.selectedTemplate = this.selectedTemplate;
			if(this.selectedTemplate == "imageNavigation"){
				$(".draggable-element[data-index='" + this.data.index + "'] .graphic-navigation .graphic-nav-list>ul>li input[name='title']").removeAttr("style");
			}else{
				$(".draggable-element[data-index='" + this.data.index + "'] .graphic-navigation .graphic-nav-list>ul>li .error-msg").text("").hide();
			}
		}
	},

	methods : {

		//改变图文导航按钮的显示隐藏
		changeShowAddItem : function(){

			if(this.scrollSetting == this.scrollSettingList[0].value){

				if(this.list.length >= this.scrollSettingList[0].max) this.showAddItem = false;
				else this.showAddItem = true;

				this.maxTip =  this.scrollSettingList[0].max;

			}else if(this.scrollSetting == this.scrollSettingList[1].value){

				if(this.list.length >= this.scrollSettingList[1].max) this.showAddItem = false;
				else this.showAddItem = true;

				this.maxTip =  this.scrollSettingList[1].max;

			}
		},

		//改变图片比例
		changeImageScale : function(event){
			var v = event.target.value;
			if(v != ""){
				if(v > 0 && v <= 100){
					this.imageScale = v;
					this.$parent.data.imageScale =  this.imageScale;//更新父级对象
				}else{
					layer.msg("请输入合法数字1~100");
				}
			}else{
				layer.msg("请输入合法数字1~100");
			}
		},

		//改变上下边距
		changePadding : function(event){
			var v = event.target.value;
			if(v != ""){
				if(v >= 0 && v <= 100){
					this.padding = v;
					this.$parent.data.padding =  this.padding;//更新父级对象
				}else{
					layer.msg("请输入合法数字0~100");
				}
			}else{
				layer.msg("请输入合法数字0~100");
			}
		},
		verify:function () {

			var res = { code : true, message : "" };
			var _self = this;

			$(".draggable-element[data-index='" + this.data.index + "'] .graphic-navigation .graphic-nav-list .template-edit-wrap>ul>li").each(function(index){
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

	},

	template : graphicNavListHtml
});

var navRadiusHtml = '<div class="layui-form-item ns-icon-radio">';
		navRadiusHtml += '<label class="layui-form-label sm">圆角展示</label>';
		navRadiusHtml += '<div class="layui-input-block">';
			navRadiusHtml += '<template v-for="(item, index) in navRadius" v-bind:k="index">';
				navRadiusHtml += '<span :class="[item.value == data.navRadius ? \'\' : \'layui-hide\']">{{item.text}}</span>';
			navRadiusHtml += '</template>';
			navRadiusHtml += '<ul class="ns-icon">';
				navRadiusHtml += '<li v-for="(item, index) in navRadius" v-bind:k="index" :class="[item.value == data.navRadius ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="data.navRadius=item.value">';
					navRadiusHtml += '<img v-if="item.value == data.navRadius" :src="item.selectedSrc" />'
					navRadiusHtml += '<img v-else :src="item.src" />'
				navRadiusHtml += '</li>';
			navRadiusHtml += '</ul>';
		navRadiusHtml += '</div>';
	navRadiusHtml += '</div>';

Vue.component("nav-radius",{
	template : navRadiusHtml,
	data : function(){
		return {
			data : this.$parent.data,
			navRadius: [
				{
					text: "直角",
					value: "right-angle",
					src: graphicNavResourcePath + "/graphic_nav/img/right-angle.png",
					selectedSrc: graphicNavResourcePath + "/graphic_nav/img/right-angle_1.png"
				},
				{
					text: "圆角",
					value: "fillet",
					src: graphicNavResourcePath + "/graphic_nav/img/fillet.png",
					selectedSrc: graphicNavResourcePath + "/graphic_nav/img/fillet_1.png"
				}
			],
		}
	},
	created : function(){
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods : {
		verify :function () {
			var res = {code: true, message: ""};
			return res;
		},
	},

});
