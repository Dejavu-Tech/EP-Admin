var html = '<div class="rich-text-list">';
		html += '<div v-bind:id="id" style="width:100%;height:320px;padding-left: 10px; box-sizing: border-box;"></div>';

		html += '<div class="template-edit-title">';
			html += '<h3>其他设置</h3>';
			html += '<i class="layui-icon layui-icon-down" onclick="closeBox(this)"></i>';
		html += '</div>';

		html += '<div class="template-edit-wrap">';
			html += '<color v-bind:data="{ field : \'backgroundColor\', \'label\' : \'背景颜色\' }"></color>';
			html += '<slide v-bind:data="{ field : \'marginTop\', label : \'页面边距\' }"></slide>';
		html += '</div>';
	html += '</div>';

Vue.component("rich-text", {
	template: html,
	data: function () {

		return {
			data : this.$parent.data,
			id: get_math_rant(10),
			editor : null,
			padding : this.$parent.data.padding,
		}
	},
	created: function () {

		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		var self = this;
		setTimeout(function () {

			self.editor = UE.getEditor(self.id, {
					toolbars: [
						[
							'source', //源代码
							'undo', //撤销
							'redo', //重做
							'bold', //加粗
							'indent', //首行缩进
							'italic', //斜体
							'underline', //下划线
							'strikethrough', //删除线
							'forecolor', //字体颜色
							'subscript', //下标
							'superscript', //上标
							'formatmatch', //格式刷
							'blockquote', //引用
							'pasteplain', //纯文本粘贴模式
							'selectall', //全选
							'preview', //预览
							'horizontal', //分隔线
							'removeformat', //清除格式
							'unlink', //取消链接
							'inserttitle', //插入标题
							'cleardoc', //清空文档
							'fontfamily', //字体
							'fontsize', //字号
							'paragraph', //段落格式
							'simpleupload', //单图上传
							'insertimage', //多图上传
							'link', //超链接
							'emotion', //表情
							'spechars', //特殊字符
							'fontborder', //字符边框
							'searchreplace', //查询替换
							'insertvideo', //视频
							'help', //帮助
							'justifyleft', //居左对齐
							'justifyright', //居右对齐
							'justifycenter', //居中对齐
							'justifyjustify', //两端对齐
							'fullscreen', //全屏
							'imagenone', //默认
							'imageleft', //左浮动
							'imageright', //右浮动
							'imagecenter', //居中
							'lineheight', //行间距
							'edittip ', //编辑提示
							'touppercase', //字母大写
							'tolowercase', //字母小写
							'music'//音乐
						]
					],
					serverUrl: "/resource/ueditor/php/controller.php",
					scaleEnabled:true
				});
			self.editor.ready(function () {
				if(self.$parent.data.html) self.editor.setContent(self.$parent.data.html);
			});

			self.editor.addListener("contentChange",function(){
				self.$parent.data.html = self.editor.getContent();
			});

		}, 10);

	},
	methods:{

		verify : function () {
			var res = {code: true, message: ""};
			if (this.$parent.data.html == "") {
				res.code = false;
				res.message = "请输入富文本内容";
			}
			return res;
		}
	}
});
