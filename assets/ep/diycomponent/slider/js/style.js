// 幻灯片·组件
var sliderPreviewHtml = '<div v-bind:id="id" class="carousel" :class="{fillet: data.sliderRadius==\'fillet\'}" @mouseover="stopAuto" @mouseout="autoPlay">';
sliderPreviewHtml += '	<div class="carousel-box" :style="{width:allCount,\'-webkit-transition\':transitionConfig,\'-webkit-transform\':slateX}" ref="carousel">';
sliderPreviewHtml += '		<div class="carousel-item" :style="{\'-webkit-transform\':imgLateX}" v-if="loop">';
sliderPreviewHtml += '			<img :src="list[list.length-1].imageUrl?list[list.length-1].imageUrl:\'/assets/ep/images/diy/crack_figure.png\'" />';
sliderPreviewHtml += '		</div>';
sliderPreviewHtml += '		<div class="carousel-item" v-for="(item,index)  in list" :style="{\'-webkit-transform\':getImgLateX(index)}">';
sliderPreviewHtml += '			<img :src="item.imageUrl?item.imageUrl:\'/assets/ep/images/diy/crack_figure.png\'" />';
sliderPreviewHtml += '		</div>';
sliderPreviewHtml += '		<div class="carousel-item" :style="{\'-webkit-transform\':endImgLateX}" v-if="loop">';
sliderPreviewHtml += '			<img :src="list[0].imageUrl?list[0].imageUrl:\'/assets/ep/images/diy/crack_figure.png\'" />';
sliderPreviewHtml += '		</div>';
sliderPreviewHtml += '	</div>';
sliderPreviewHtml += '	<div class="carousel-dots" v-if="dots">';
sliderPreviewHtml += '		<button v-for="(item,index) in list.length" :key="index" @click="toDots(index)" v-bind:style="{ background: index==dotsIndex?data.textColor:\'#E7E9E7\'}"></button>';
sliderPreviewHtml += '	</div>';
sliderPreviewHtml += '</div>';

Vue.component("slider", {
	data: function () {
		return {
			id: "slider_" + get_math_rant(10),
			data: this.$parent.data,
			list: this.$parent.data.list,
			selectedTemplate: this.$parent.data.selectedTemplate,
			// 图片宽
			imgWidth: 343,
			// 指示器
			dots: true,
			arrow: true,
			// 初始播放位置
			initIndex: 0,
			// 是否循环
			loop: true,
			// 持续时间
			duration: 0.3,
			// 自动播放
			auto: true,
			// 自动播放时间间隔
			autoTime: 2000,
			imgIndex: 0,
			durationTime: 0.2,
			dotsIndex: 0,
			autoer: null,
		}
	},
	computed: {
		allCount() {
			return (this.list.length * this.imgWidth) + 'px';
		},
		slateX() {
			return 'translate3d(' + (-this.imgIndex * this.imgWidth) + 'px,0,0)'
		},
		transitionConfig() {
			return 'all ' + this.durationTime + 's';
		},
		imgLateX() {
			let width = -this.imgWidth
			return 'translate3d(' + (width) + 'px,0,0)'
		},
		endImgLateX() {
			let width = this.list.length
			return 'translate3d(' + (width) + 'px,0,0)'
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify); //加载验证方法

		this.imgIndex = this.dotsIndex = this.initIndex;
		this.durationTime = this.duration;
		if (this.auto) this.autoPlay();
	},
	watch: {
		list : function(val, oldval){
			console.log(val)
			console.log(oldval)
		},
	},
	methods: {
		verify: function () {
			var res = {
				code: true,
				message: ""
			};
			return res;
		},
		getImgLateX(i) {
			let width = this.imgWidth * (i + 1)
			return 'translate3d(' + (width) + 'px,0,0)'
		},
		toLeft() {
			if (this.loop) {
				this.imgIndex--;
				this.dotsIndex--;
				if (this.dotsIndex <= -1) this.dotsIndex = this.list.length - 1;

				if (this.imgIndex <= -2) this.loopFn('left');

			} else {
				if (this.imgIndex == 0) return this.dotsIndex = this.imgIndex = this.list.length - 1;
				this.imgIndex--;
				this.dotsIndex--;
			}
		},
		toRight() {
			if (this.loop) {
				this.imgIndex++;
				this.dotsIndex++;
				if (this.dotsIndex == this.list.length) this.dotsIndex = 0;
				if (this.imgIndex == this.list.length + 1) this.loopFn('right');
			} else {
				this.imgIndex++;
				this.dotsIndex++;
				if (this.imgIndex > this.list.length - 1) return this.dotsIndex = this.imgIndex = 0;
			}
		},
		loopFn(type) {
			const dur = this.durationTime;
			this.durationTime = 0;

			this.imgIndex = type == 'right' ? 0 : this.list.length - 1;

			setTimeout(() => {
				this.$nextTick(function () {
					this.durationTime = dur;

					if (type == 'right') this.imgIndex++;
					else this.imgIndex--;
				})
			}, 30)
		},
		toDots(index) {
			this.dotsIndex = this.imgIndex = index;
		},
		autoPlay() {
			if (this.auto) {
				clearInterval(this.autoer);
				this.autoer = setInterval(() => {
					this.toRight();
				}, this.autoTime)
			}

		},
		stopAuto() {
			if (this.auto) return clearInterval(this.autoer);
		}
	},
	template: sliderPreviewHtml
});


/**
 * [图片导航的图片]·组件
 */
var sliderListHtml = '<div class="slider-list">';

sliderListHtml += '<div class="template-edit-wrap">';
sliderListHtml += '<ul>';
sliderListHtml += '<p class="hint">建议上传尺寸相同的图片(710px * 320px)</p>';
sliderListHtml += '<li v-for="(item,index) in list" v-bind:key="index">';
sliderListHtml += '<img-sec-upload v-bind:data="{ data: item }" v-bind:condition="$parent.data.selectedTemplate == \'imageNavigation\'"></img-sec-upload>';

sliderListHtml += '<div class="content-block" v-bind:class="$parent.data.selectedTemplate">';

sliderListHtml += '<sel-link v-bind:data="{ field : $parent.data.list[index].link }"></sel-link>';
sliderListHtml += '</div>';

sliderListHtml += '<i class="del" v-on:click="list.splice(index,1)" data-disabled="1">x</i>';
sliderListHtml += '<div class="error-msg"></div>';
sliderListHtml += '</li>';

sliderListHtml += '<div v-if="showAddItem" class="add-item ns-text-color" v-on:click="list.push({ imageUrl : \'\', title : \'\', link : {} })">';
sliderListHtml += '<i>+</i>';
sliderListHtml += '<span>添加一张图片</span>';
sliderListHtml += '</div>';
sliderListHtml += '</ul>';
sliderListHtml += '</div>';

sliderListHtml += '<div class="template-edit-wrap">';
sliderListHtml += '<color v-bind:data="{ defaultcolor: \'#666666\', label : \'选中颜色\' }"></color>';
sliderListHtml += '<slider-radius></slider-radius>';
sliderListHtml += '</div>';

sliderListHtml += '<div class="template-edit-title">';
sliderListHtml += '<h3>其他设置</h3>';
sliderListHtml += '<i class="layui-icon layui-icon-down" onclick="closeBox(this)"></i>';
sliderListHtml += '</div>';

sliderListHtml += '<div class="template-edit-wrap">';
sliderListHtml += '<color v-bind:data="{ field : \'backgroundColor\', label : \'背景颜色\' }"></color>';

sliderListHtml += '<div class="text-slide">';
sliderListHtml += '<slide v-bind:data="{ field : \'marginTop\', label : \'页面间距\' }"></slide>';
sliderListHtml += '</div>';
sliderListHtml += '</div>';

sliderListHtml += '</div>';

Vue.component("slider-list", {

	data: function () {
		return {
			data: this.$parent.data,
			showAddItem: true,
			list: this.$parent.data.list,
			imageScale: this.$parent.data.imageScale,
			padding: this.$parent.data.padding,
			maxTip: 5, //最大上传数量提示
			selectedTemplate: this.$parent.data.selectedTemplate,
			scrollSettingList: [{
				name: "固定",
				value: "fixed",
				max: 5
			}],
		};
	},

	created: function () {
		this.changeShowAddItem();
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify); //加载验证方法
	},

	watch: {
		list: function () {
			this.changeShowAddItem();
		}
	},

	methods: {

		//改变图文导航按钮的显示隐藏
		changeShowAddItem: function () {
			if (this.list.length >= this.scrollSettingList[0].max) this.showAddItem = false;
			else this.showAddItem = true;

			this.maxTip = this.scrollSettingList[0].max;
		},

		//改变上下边距
		changePadding: function (event) {
			var v = event.target.value;
			if (v != "") {
				if (v >= 0 && v <= 100) {
					this.padding = v;
					this.$parent.data.padding = this.padding; //更新父级对象
				} else {
					layer.msg("请输入合法数字0~100");
				}
			} else {
				layer.msg("请输入合法数字0~100");
			}
		},
		verify: function () {

			var res = {
				code: true,
				message: ""
			};
			var _self = this;

			$(".draggable-element[data-index='" + this.data.index + "'] .slider .slider-list .template-edit-wrap>ul>li").each(function (index) {
				if (_self.selectedTemplate == "imageNavigation") {
					$(this).find("input[name='title']").removeAttr("style"); //清空输入框的样式
					//检测是否有未上传的图片
					if (_self.list[index].imageUrl == "") {
						res.code = false;
						res.message = "请选择一张图片";
						$(this).find(".error-msg").text("请选择一张图片").show();
						return res;
					} else {
						$(this).find(".error-msg").text("").hide();
					}
				} else {
					if (_self.list[index].title == "") {
						res.code = false;
						res.message = "请输入标题";
						$(this).find("input[name='title']").attr("style", "border-color:red !important;").focus();
						$(this).find(".error-msg").text("请输入标题").show();
						return res;
					} else {
						$(this).find("input[name='title']").removeAttr("style");
						$(this).find(".error-msg").text("").hide();
					}
				}
			});

			return res;
		}

	},

	template: sliderListHtml
});

var sliderRadiusHtml = '<div class="layui-form-item ns-icon-radio">';
sliderRadiusHtml += '<label class="layui-form-label sm">圆角展示</label>';
sliderRadiusHtml += '<div class="layui-input-block">';
sliderRadiusHtml += '<template v-for="(item, index) in sliderRadius" v-bind:k="index">';
sliderRadiusHtml += '<span :class="[item.value == data.sliderRadius ? \'\' : \'layui-hide\']">{{item.text}}</span>';
sliderRadiusHtml += '</template>';
sliderRadiusHtml += '<ul class="ns-icon">';
sliderRadiusHtml += '<li v-for="(item, index) in sliderRadius" v-bind:k="index" :class="[item.value == data.sliderRadius ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="data.sliderRadius=item.value">';
sliderRadiusHtml += '<img v-if="item.value == data.sliderRadius" :src="item.selectedSrc" />'
sliderRadiusHtml += '<img v-else :src="item.src" />'
sliderRadiusHtml += '</li>';
sliderRadiusHtml += '</ul>';
sliderRadiusHtml += '</div>';
sliderRadiusHtml += '</div>';

Vue.component("slider-radius", {
	template: sliderRadiusHtml,
	data: function () {
		return {
			data: this.$parent.data,
			sliderRadius: [{
					text: "直角",
					value: "right-angle",
					src: sliderResourcePath + "/graphic_nav/img/right-angle.png",
					selectedSrc: sliderResourcePath + "/graphic_nav/img/right-angle_1.png"
				},
				{
					text: "圆角",
					value: "fillet",
					src: sliderResourcePath + "/graphic_nav/img/fillet.png",
					selectedSrc: sliderResourcePath + "/graphic_nav/img/fillet_1.png"
				}
			],
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
	},

});
