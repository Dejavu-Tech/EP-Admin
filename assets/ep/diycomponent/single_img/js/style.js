// 幻灯片·组件
var singleImgPreviewHtml = '<div v-bind:id="id" v-bind:style="{paddingLeft: data.paddingLeftRight+\'px\',paddingRight: data.paddingLeftRight+\'px\'}">';
singleImgPreviewHtml += '		<div class="single-img-item" v-for="(item,index) in data.list" v-bind:style="{marginTop: index?data.imageGap+\'px\':0}">';
singleImgPreviewHtml += '			<img :src="item.imageUrl?item.imageUrl:\'/assets/ep/images/diy/crack_figure.png\'" :style="\'border-radius:\'+data.topRadius+\'px \'+data.topRadius+\'px \'+data.bottomRadius+\'px \'+data.bottomRadius+\'px\'" />';
singleImgPreviewHtml += '		</div>';
singleImgPreviewHtml += '</div>';

Vue.component("single-img", {
	data: function () {
		return {
			id: "slider_" + get_math_rant(10),
			data: this.$parent.data
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
		}
	},
	template: singleImgPreviewHtml
});


/**
 * [图片导航的图片]·组件
 */
var singleImgListHtml = '<div class="single-img-list">';

singleImgListHtml += '<div class="template-edit-wrap">';
singleImgListHtml += '<ul>';
singleImgListHtml += '<p class="hint">建议图片宽度750px</p>';
singleImgListHtml += '<li v-for="(item,index) in list" v-bind:key="index">';
singleImgListHtml += '<img-sec-upload v-bind:data="{ data: item }"></img-sec-upload>';
singleImgListHtml += '<div class="content-block" v-bind:class="$parent.data.selectedTemplate">';
singleImgListHtml += '<sel-link v-bind:data="{ field : $parent.data.list[index].link }"></sel-link>';
singleImgListHtml += '</div>';
singleImgListHtml += '<i class="del" v-on:click="list.splice(index, 1)" data-disabled="1">x</i>';
singleImgListHtml += '<div class="error-msg"></div>';
singleImgListHtml += '</li>';

singleImgListHtml += '<div v-if="showAddItem" class="add-item ns-text-color" v-on:click="list.push({ imageUrl : \'\', title : \'\', link : {} })">';
singleImgListHtml += '	<i>+</i>';
singleImgListHtml += '	<span>添加一张图片</span>';
singleImgListHtml += '</div>';
singleImgListHtml += '</ul>';
singleImgListHtml += '</div>';

singleImgListHtml += '<div class="template-edit-title">';
singleImgListHtml += '<h3>其他设置</h3>';
singleImgListHtml += '<i class="layui-icon layui-icon-down" onclick="closeBox(this)"></i>';
singleImgListHtml += '</div>';

singleImgListHtml += '<div class="template-edit-wrap">';
singleImgListHtml += '	<div class="text-slide">';
singleImgListHtml += '		<slide v-bind:data="{ field : \'marginTop\', label : \'页面间距\' }"></slide>';
singleImgListHtml += '	</div>';
singleImgListHtml += '	<div class="text-slide">';
singleImgListHtml += '		<slide v-bind:data="{ field : \'paddingLeftRight\', label : \'左右间距\' }"></slide>';
singleImgListHtml += '	</div>';
singleImgListHtml += '	<div class="text-slide">';
singleImgListHtml += '		<slide v-bind:data="{ field : \'imageGap\', label : \'图片间距\' }"></slide>';
singleImgListHtml += '	</div>';
singleImgListHtml += '	<div class="text-slide">';
singleImgListHtml += '		<slide v-bind:data="{ field : \'topRadius\', label : \'上边角\' }"></slide>';
singleImgListHtml += '	</div>';
singleImgListHtml += '	<div class="text-slide">';
singleImgListHtml += '		<slide v-bind:data="{ field : \'bottomRadius\', label : \'下边角\' }"></slide>';
singleImgListHtml += '	</div>';
singleImgListHtml += '</div>';

singleImgListHtml += '</div>';

Vue.component("single-img-list", {

	data: function () {
		return {
			data: this.$parent.data,
			showAddItem: true,
			list: this.$parent.data.list,
			maxTip: 5, //最大上传数量提示
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
		//改变按钮的显示隐藏
		changeShowAddItem: function () {
			this.showAddItem = (this.list.length >= this.maxTip) ? false : true;
		},
		verify: function () {

			var res = {
				code: true,
				message: ""
			};
			var _self = this;

			$(".draggable-element[data-index='" + this.data.index + "'] .single_img .single-img-list .template-edit-wrap>ul>li").each(function (index) {
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
			});

			return res;
		}

	},

	template: singleImgListHtml
});
