// 幻灯片·组件
var videoPreviewHtml = '<div v-bind:id="id" class="videobox" :class="{fillet: data.sliderRadius==\'fillet\'}">';
videoPreviewHtml += '		<div v-for="(item,index)  in list">';
videoPreviewHtml += '			<video :src="item.videoUrl" controls v-if="item.videoUrl" />';
videoPreviewHtml += '			<img src="/assets/ep/images/diy/crack_figure.png" style="height: 150px;" v-else >';
videoPreviewHtml += '		</div>';
videoPreviewHtml += '</div>';

Vue.component("ep-video", {
	data: function () {
		return {
			id: "video_" + get_math_rant(10),
			data: this.$parent.data,
			list: this.$parent.data.list,
			videoWidth: 710
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify); //加载验证方法
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
	},
	template: videoPreviewHtml
});


/**
 * [视频的图片]·组件
 */
var videoListHtml = '<div class="video-list">';

videoListHtml += '<div class="template-edit-wrap">';
videoListHtml += '<ul>';
videoListHtml += '<li v-for="(item,index) in list" v-bind:key="index">';
videoListHtml += '<video-upload v-bind:data="{ data: item }"></video-upload>';

videoListHtml += '<div class="content-block">';

// videoListHtml += '<sel-link v-bind:data="{ field : $parent.data.list[index].link }"></sel-link>';
videoListHtml += '</div>';

videoListHtml += '<i class="del" v-on:click="list.splice(index,1)" data-disabled="1">x</i>';
videoListHtml += '<div class="error-msg"></div>';
videoListHtml += '</li>';

videoListHtml += '</ul>';
videoListHtml += '</div>';

videoListHtml += '<div class="template-edit-wrap">';
videoListHtml += '<video-radius></video-radius>';
videoListHtml += '</div>';

videoListHtml += '<div class="template-edit-title">';
videoListHtml += '<h3>其他设置</h3>';
videoListHtml += '<i class="layui-icon layui-icon-down" onclick="closeBox(this)"></i>';
videoListHtml += '</div>';

videoListHtml += '<div class="template-edit-wrap">';
videoListHtml += '<color v-bind:data="{ field : \'backgroundColor\', label : \'背景颜色\' }"></color>';

videoListHtml += '<div class="text-slide">';
videoListHtml += '<slide v-bind:data="{ field : \'marginTop\', label : \'页面间距\' }"></slide>';
videoListHtml += '</div>';
videoListHtml += '</div>';

videoListHtml += '</div>';

Vue.component("video-list", {

	data: function () {
		return {
			data: this.$parent.data,
			showAddItem: true,
			list: this.$parent.data.list,
			imageScale: this.$parent.data.imageScale,
			padding: this.$parent.data.padding,
			maxTip: 5, //最大上传数量提示
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
				$(this).find("input[name='title']").removeAttr("style"); //清空输入框的样式
				//检测是否有未上传的视频
				if (_self.list[index].videoUrl == "") {
					res.code = false;
					res.message = "请选择视频";
					$(this).find(".error-msg").text("请选择视频").show();
					return res;
				} else {
					$(this).find(".error-msg").text("").hide();
				}
			});

			return res;
		}

	},

	template: videoListHtml
});

var videoRadiusHtml = '<div class="layui-form-item ns-icon-radio">';
videoRadiusHtml += '<label class="layui-form-label sm">圆角展示</label>';
videoRadiusHtml += '<div class="layui-input-block">';
videoRadiusHtml += '<template v-for="(item, index) in sliderRadius" v-bind:k="index">';
videoRadiusHtml += '<span :class="[item.value == data.sliderRadius ? \'\' : \'layui-hide\']">{{item.text}}</span>';
videoRadiusHtml += '</template>';
videoRadiusHtml += '<ul class="ns-icon">';
videoRadiusHtml += '<li v-for="(item, index) in sliderRadius" v-bind:k="index" :class="[item.value == data.sliderRadius ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="data.sliderRadius=item.value">';
videoRadiusHtml += '<img v-if="item.value == data.sliderRadius" :src="item.selectedSrc" />'
videoRadiusHtml += '<img v-else :src="item.src" />'
videoRadiusHtml += '</li>';
videoRadiusHtml += '</ul>';
videoRadiusHtml += '</div>';
videoRadiusHtml += '</div>';

Vue.component("video-radius", {
	template: videoRadiusHtml,
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
