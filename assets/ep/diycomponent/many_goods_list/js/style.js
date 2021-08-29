/**
 * 空的验证组件，后续如果增加业务，则更改组件
 */
var manyGoodsListHtml = '<div class="many-goods-list-edit layui-form">';

		manyGoodsListHtml += '<ul>';
			manyGoodsListHtml += '<li v-for="(item, index) in list" v-bind:key="index">';
				manyGoodsListHtml += '<div class="content-block">';
					manyGoodsListHtml += '<div class="layui-form-item" >';
						manyGoodsListHtml += '<label class="layui-form-label sm">分类名称</label>';
						manyGoodsListHtml += '<div class="layui-input-block">';
							manyGoodsListHtml += '<input type="text" name=\'title\' v-model="item.title" class="layui-input" />';
						manyGoodsListHtml += '</div>';
					manyGoodsListHtml += '</div>';
					
					manyGoodsListHtml += '<div class="layui-form-item" >';
						manyGoodsListHtml += '<label class="layui-form-label sm">副标题</label>';
						manyGoodsListHtml += '<div class="layui-input-block">';
							manyGoodsListHtml += '<input type="text" name=\'desc\' v-model="item.desc" class="layui-input" />';
						manyGoodsListHtml += '</div>';
					manyGoodsListHtml += '</div>';
					
					// manyGoodsListHtml += '<nc-link v-bind:data="{ field : $parent.data.list[index].link }"></nc-link>';
					// manyGoodsListHtml += '<div class="layui-form-item">';
					// 	manyGoodsListHtml += '<label class="layui-form-label sm">列表风格</label>';
					// 	manyGoodsListHtml += '<div class="layui-input-block align-right">';
					// 		manyGoodsListHtml += '<a href="#" class="ns-input-text" v-on:click="selectGoodsListStyle(index)"><span class="ns-text-color">风格{{ item.goodsStyle }}</span><i class="iconfont iconyoujiantou"></i></a>';
					// 	manyGoodsListHtml += '</div>';
					// manyGoodsListHtml += '</div>';
					
					// manyGoodsListHtml += '<div class="layui-form-item">';
					// 	manyGoodsListHtml += '<label class="layui-form-label sm">数据来源</label>';
					// 	manyGoodsListHtml += '<div class="layui-input-block">';
					// 		manyGoodsListHtml += '<div class="source-selected">';
					// 		manyGoodsListHtml += '<template v-for="(sourceItem, sourceIndex) in goodsSources" v-bind:k="index">';
					// 			manyGoodsListHtml += '<div class="source" v-if="sourceItem.value == item.sources">{{ sourceItem.text }}</div>';
					// 		manyGoodsListHtml += '</template>';
					// 			manyGoodsListHtml += '<template v-for="(sourceItem, sourceIndex) in goodsSources" v-bind:k="index">';
					// 				manyGoodsListHtml += '<span class="source-item" :title="sourceItem.text" v-on:click="item.sources=sourceItem.value" v-bind:class="[(item.sources == sourceItem.value) ?  \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\' ]"><img v-bind:src="sourceItem.selectedIcon" v-if="item.sources == sourceItem.value"><img v-bind:src="sourceItem.icon" v-else/></span>';
					// 			manyGoodsListHtml += '</template>';
					// 		manyGoodsListHtml += '</div>';
					// 	manyGoodsListHtml += '</div>';
					// manyGoodsListHtml += '</div>';
					
					manyGoodsListHtml += '<div class="layui-form-item" v-if="isLoad && item.sources == \'category\'">';
						manyGoodsListHtml += '<label class="layui-form-label sm">商品分类</label>';
						manyGoodsListHtml += '<div class="layui-input-block align-right">';
								manyGoodsListHtml += '<a href="#" class="ns-input-text" @click="selectCategory(index)"><span class="ns-text-color">{{ item.categoryName }}</span><i class="iconfont iconyoujiantou"></i></a>';
						manyGoodsListHtml += '</div>';
					manyGoodsListHtml += '</div>';
					
					manyGoodsListHtml += '<div class="layui-form-item" v-if="isLoad && item.sources == \'diy\'">';
						manyGoodsListHtml += '<label class="layui-form-label sm">手动选择</label>';
						manyGoodsListHtml += '<div class="layui-input-block align-right">';
							manyGoodsListHtml += '<a href="#" class="ns-input-text" v-on:click="addGoods(index)">';
								manyGoodsListHtml += '<span v-if="item.goodsId.length == 0" class="ns-text-color">请选择</span>';
								manyGoodsListHtml += '<span v-if="item.goodsId.length > 0" class="ns-text-color">已选{{item.goodsId.length}}个</span>';
								manyGoodsListHtml += '<i class="iconfont iconyoujiantou"></i>';
							manyGoodsListHtml += '</a>';
						manyGoodsListHtml += '</div>';
					manyGoodsListHtml += '</div>';
					
				manyGoodsListHtml += '</div>';
				
				manyGoodsListHtml += '<i class="del" v-on:click="list.splice(index,1)" data-disabled="1">x</i>';
				manyGoodsListHtml += '<div class="error-msg"></div>';
			manyGoodsListHtml += '</li>';
			
			manyGoodsListHtml += '<div class="add-item ns-text-color" v-on:click="list.push({goodsStyle: 1, title: \'分类\', desc: \'分类描述\', link: {}, sources: \'category\', categoryId: 0, categoryName: \'请选择\', goodsId: []})">';
				manyGoodsListHtml += '<i>+</i>';
				manyGoodsListHtml += '<span>添加一个商品组</span>';
			manyGoodsListHtml += '</div>';
		manyGoodsListHtml += '</ul>';

		
			
		// manyGoodsListHtml += '<slide v-bind:data="{ field : \'goodsCount\', label: \'商品数量\', max: 20}" v-if="data.sources != \'diy\'"></slide>';

	manyGoodsListHtml += '</div>';
var select_goods_list = []; //配合商品选择器使用
Vue.component("many-goods-list", {
	template: manyGoodsListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			list: this.$parent.data.list,
			goodsSources: [
				// {
				// 	text: "默认",
				// 	value: "default",
				// 	icon: goodsListResourcePath + "/goods_list/img/default_icon.png",
				// 	selectedIcon: goodsListResourcePath + "/goods_list/img/default_selected_icon.png"
				// },
				{
					text: "商品分类",
					value: "category",
					icon: goodsListResourcePath + "/many_goods_list/img/category_icon.png",
					selectedIcon: goodsListResourcePath + "/many_goods_list/img/category_selected_icon.png"
				},
				{
					text : "手动选择",
					value : "diy",
					icon: goodsListResourcePath + "/many_goods_list/img/diy_icon.png",
					selectedIcon: goodsListResourcePath + "/many_goods_list/img/diy_selected_icon.png"
				}
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
		verify : function () {
			var res = { code : true, message : "" };
			
			for (var i=0; i<this.$parent.data.list.length; i++) {
				if (this.$parent.data.list[i].title == ''){
					res.code = false;
					res.message = "请输入分类名称";
				}
				if (this.$parent.data.list[i].sources == 'category' && this.$parent.data.list[i].categoryId == 0){
					res.code = false;
					res.message = "请选择商品分类";
				}
				if (this.$parent.data.list[i].sources == 'diy' && this.$parent.data.list[i].goodsId.length == 0){
					res.code = false;
					res.message = "请选择商品";
				}
			}
			return res;
		},
		addGoods: function(index) {
			var self = this;
			goodsSelect(function (res) {
				self.$parent.data.list[index].goodsId = res;
				// for (var i = 0; i < res.length; i++) {
				// 	self.$parent.data.goodsId.push(res[i].goods_id);
				// }

			}, self.$parent.data.list[index].goodsId, {mode: "spu", disabled: 0, promotion: "module", post: post});
		},
		selectCategory(i){
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
					$(".layui-layer-content .category-wrap .category-item[data-id='" + self.data.list[i].categoryId + "']").click();
				},
				yes: function (index, layero) {
					self.data.list[i].categoryName =  $(".layui-layer-content .category-wrap .category-item.selected").text();
					self.data.list[i].categoryId = $(".layui-layer-content .category-wrap .category-item.selected").attr('data-id');
					layer.closeAll()
				}
			});
		},
		// selectGoodsListStyle(i) {
		// 	var self = this;
		// 	layer.open({
		// 		type: 1,
		// 		title: '模板样式',
		// 		area:['930px','630px'],
		// 		btn: ['确定', '返回'],
		// 		content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .goods-list-style").html(),
		// 		success: function(layero, index) {
		// 			$(".layui-layer-content .goods-list-style input[name='style']").val(self.data.list[i].style);
		// 			$("body").on("click", ".layui-layer-content .style-list-con-goods .style-li-goods", function () {
		// 				$(this).addClass("selected ns-border-color").siblings().removeClass("selected ns-border-color");
		// 				$(".layui-layer-content .goods-list-style input[name='style']").val($(this).index() + 1);
		// 			});
		// 		},
		// 		yes: function (index, layero) {
		// 			self.data.list[i].style = $(".layui-layer-content .goods-list-style input[name='style']").val();
		// 			layer.closeAll()
		// 		}
		// 	});
		// }
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

var manyGoodsListStyleHtml = '<div class="">';
		manyGoodsListStyleHtml += '<div class="layui-form-item">';
			manyGoodsListStyleHtml += '<label class="layui-form-label sm">商品组名称</label>';
			manyGoodsListStyleHtml += '<div class="layui-input-block">';
				manyGoodsListStyleHtml += '<span>{{data.title}}</span>';
			manyGoodsListStyleHtml += '</div>';
			// manyGoodsListStyleHtml += '<div style="font-size: 12px; color: #909399; padding-left: 80px; margin-top: 10px;">用来关联商品列表，商品组名称需与要关联的商品列表商品组名称一致</div>';
		manyGoodsListStyleHtml += '</div>';
	
		// manyGoodsListStyleHtml += '<div class="layui-form-item">';
		// 	manyGoodsListStyleHtml += '<label class="layui-form-label sm">分组风格</label>';
		// 	manyGoodsListStyleHtml += '<div class="layui-input-block align-right">';
		// 		manyGoodsListStyleHtml += '<a href="#" class="ns-input-text" v-on:click="selectGoodsStyle"><span class="ns-text-color">风格{{ data.style }}</span><i class="iconfont iconyoujiantou"></i></a>';
		// 	manyGoodsListStyleHtml += '</div>';
		// manyGoodsListStyleHtml += '</div>';
	manyGoodsListStyleHtml += '</div>';

Vue.component("many-goods-list-style", {
	template: manyGoodsListStyleHtml,
	data: function() {
		return {
			data: this.$parent.data,
			parentList: this.$parent.$parent.data,
			goodsGroupNum: 1,
			goodsGroupTitle: ''
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},
	methods: {
		verify: function () {
			var res = { code: true, message: "" };
			
			console.log(this.data.title);
			
			var _self = this;
			if (_self.data.title.length == 0) {
				res.code = false;
				res.message = "商品组名称不能为空";
				setTimeout(function(){
					$("#title_" + _self.data.index).focus();
				},10);
			} else {
				console.log(_self.parentList);
				for (var i=0; i<_self.parentList.length; i++) {
					if (_self.parentList[i].controller == "ManyGoodsList" && _self.data.title == _self.parentList[i].title && _self.parentList[i].index != _self.data.index) {
						res.code = false;
						res.message = "商品组名称不能重复";
						setTimeout(function(){
							$("#title_" + _self.data.index).focus();
						},10);
					}
				}
			}
			
			return res;
		},
		selectGoodsStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '模板样式',
				area:['930px','630px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .goods-cate-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content .goods-cate-style input[name='style']").val(self.data.style);
					$("body").on("click", ".layui-layer-content .style-cate-con-goods .style-li-goods", function () {
						$(this).addClass("selected ns-border-color").siblings().removeClass("selected ns-border-color");
						$(".layui-layer-content .goods-cate-style input[name='style']").val($(this).index() + 1);
					});
				},
				yes: function (index, layero) {
					self.data.style = $(".layui-layer-content .goods-cate-style input[name='style']").val();
					layer.closeAll()
				}
			});
		},
	}
});