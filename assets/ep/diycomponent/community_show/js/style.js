var storeShowStyle = '<div>'
		storeShowStyle += '<div class="layui-form-item">';
			storeShowStyle += '<label class="layui-form-label sm">选择风格</label>';
			storeShowStyle += '<div class="layui-input-block">';
				storeShowStyle += '<div v-if="data.styleName" class="ns-input-text ns-text-color selected-style" v-on:click="selectGoodsStyle">{{data.styleName}} <i class="layui-icon layui-icon-right"></i></div>';
				storeShowStyle += '<div v-else class="ns-input-text selected-style" v-on:click="selectGoodsStyle">选择 <i class="layui-icon layui-icon-right"></i></div>';
			storeShowStyle += '</div>';
		storeShowStyle += '</div>';
		storeShowStyle += '<color v-bind:data="{ defaultcolor: \'#333333\' }"></color>';
		storeShowStyle += '<color v-bind:data="{ field : \'backgroundColor\', label : \'背景颜色\' }"></color>';
	storeShowStyle += '</div>';

Vue.component("store-show-style", {
	template: storeShowStyle,
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
				title: '风格选择',
				area:['930px','630px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .goods-list-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$(".layui-layer-content input[name='style_name']").val(self.data.styleName);
					$("body").on("click", ".layui-layer-content .style-list-con-store .style-li-goods", function () {
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
});