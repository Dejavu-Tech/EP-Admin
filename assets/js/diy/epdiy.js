//最外层组件
var componentHtml = '<div v-show="data.lazyLoadCss && data.lazyLoad">';
componentHtml += '<div class="preview-draggable">';//拖拽区域
	componentHtml += '<slot name="preview"></slot>';
	componentHtml += '<i class="del" v-show="parseInt(data.is_delete) !== 1" v-on:click.stop="$parent.delComponent(data.index)" data-disabled="1">x</i>';
componentHtml += '</div>';

componentHtml += '<div class="edit-attribute" v-bind:data-have-edit="1" v-show="$parent.currentIndex==data.index">';//  && $slots.edit
	componentHtml += '<div class="attr-wrap">';
			componentHtml += '<div class="restore-wrap">';
				componentHtml += '<h2 class="attr-title">{{data.name}}</h2>';
				componentHtml += '<slot name="edit"></slot>';
		componentHtml += '</div>';
	componentHtml += '</div>';
componentHtml += '</div>';

componentHtml += '<div style="display:none;">';
componentHtml += '<slot name="resource"></slot>';
componentHtml += '</div>';
componentHtml += '</div>';

var commonComponent = {
	props: ["data"],
	template: componentHtml,
	created: function () {
		//如果当前添加的组件没有添加过资源
		if (!this.$slots.resource) {
			this.data.lazyLoadCss = true;
			this.data.lazyLoad = true;
		} else {
			//检测是否只添加了JS或者CSS，没有添加默认为true
			var countCss = 0, countJs = 0, outerCountJs = 0;
			for (var i = 0; i < this.$slots.resource.length; i++) {
				if (this.$slots.resource[i].componentOptions) {
					if (this.$slots.resource[i].componentOptions.tag == "css") {
						countCss++;
					} else if (this.$slots.resource[i].componentOptions.tag == "js") {
						countJs++;
						//统计外部JS数量
						if (!$.isEmptyObject(this.$slots.resource[i].componentOptions.propsData)) outerCountJs++;
					}
				}
			}

			if (countCss == 0) this.data.lazyLoadCss = true;
			if (countJs == 0) this.data.lazyLoad = true;

			this.data.outerCountJs = outerCountJs;

		}
	}
};

//cm-construct
var vue = new Vue({
    el: "#modify-view",
    data: {
        //当前编辑的组件位置
        currentIndex: -99,
        changeIndex: -1,
        isAdd: false,
        globalLazyLoad: false, //全局设置是为了页面设置这个独特组件
        //全局属性
        global: global ? global : {
            title: "页面名称",
            bgColor: "#f6f6f6",
            topNavColor: "#ffffff",
            topNavbg: false,
            textNavColor: "#000000",
            topNavImg: "",
            moreLink: {},
            //是否显示底部导航标识
            openBottomNav: false,
            navStyle: 1,
            textImgStyleLink: '1',
            textImgPosLink: 'center',
            mpCollect: false,
            bgUrl: '',
            // 弹框形式，不弹出 -1，首次弹出 1，每次弹出 0
            popWindow: {
                imageUrl: "",
                count: -1,
                link: {},
                imgWidth: '',
                imgHeight: ''
            },
        },
        textImgPositionList: [{
                text: "居左",
                value: "left",
                src: "/assets/ep/images/diy/nav/text_left.png",
                selectedSrc: "/assets/ep/images/diy/nav/text_left_hover.png"
            },
            {
                text: "居中",
                value: "center",
                src: "/assets/ep/images/diy/nav/text_right.png",
                selectedSrc: "/assets/ep/images/diy/nav/text_right_hover.png"
            }
        ],
        data: [],
        loaded: false
    },
    components: {
        'cm-construct': commonComponent, //剥离当前data. 循环时这么处理佳
    },
    created:function(){
		console.log(this.data)
	},
    mounted: function () {
		this.refreshSort();
        this.loaded = true;
	},
    methods: {
        /**
         *
         * @param {*} obj 组件参数数据
         * @param {*} options 其他属性
         * @returns
         */
        addepComponent: function (obj, options) {
            obj.index = 0;
			obj.sort = 0;
			obj.lazyLoadCss = false;
			obj.lazyLoad = false;
			obj.outerCountJs = 0;

            if (options) {
				obj.addon_name = options.addon_name;
				obj.type = options.name;
				obj.name = options.title;
				obj.controller = options.controller;
				obj.is_delete = options.is_delete;
			}

            //1、检测是否添加到最大数量
            if (options && !this.checkComponentIsAdd(obj.type, options.max_count)) {
                this.autoSelected(obj.type); //自动选中这个元素
                return;
            }

            this.data.push(obj);
            //选中最后一个对象作为当前索引
            if (options) {
                this.currentIndex = this.data.length - 1;
            }
            this.isAdd = true;

            this.refreshSort();
            var self = this;
            $(".edit-attribute-placeholder").show();
            setTimeout(function () {
                $(".edit-attribute-placeholder").hide();
                if (obj.controller == "FloatBtn") {} else {
                    if (self.changeIndex == -1 || (self.changeIndex != self.currentIndex)) {
                        $(".preview-div .preview-screen .view-wrap").scrollTop($(".screen_center").height());
                    }
                }

            }, 60);
            // console.log(this.data)

        },
        checkComponentIsAdd: function (type, max_count) {
            //判断添加数量是否最大
            //max_count为0时不处理
            if (max_count == 0) return true;

            var count = 0;

            //遍历已添加的自定义组件，检测是否超出数量
            for (var i in this.data) {
                if (this.data[i].type == type) count++;
            }

            if (count >= max_count) { return false; } else { return true; }
        },
        //改变当前编辑的组件选中
        changeCurrentIndex: function (sort) {
            this.currentIndex = parseInt(sort);
            this.changeIndex = this.currentIndex;
            this.isAdd = false;
            this.refreshSort();
        },
        autoSelected(type) {
            //选中这个类型的对象
            for (var i in this.data) {
                if (this.data[i].type == type) {
                    this.changeCurrentIndex(this.data[i].index);
                    var element = $('.preview-box .preview-div [data-index="' + this.data[i].index + '"]'),
                        warp = $(".preview-box .preview-div .preview-screen"),
                        warpTop = warp.offset().top,
                        warpBottom = warpTop + warp.height(),
                        elementTop = element.offset().top,
                        elementBottom = elementTop + element.height(),
                        scrollTop = $(".preview-box .preview-div .preview-screen").scrollTop();

                    if (elementBottom > warpBottom) {
                        scrollTop += (elementBottom - warpBottom) + 2;
                    } else if (warpTop > elementTop) {
                        scrollTop -= (warpTop - elementTop);
                    }
                    $(".preview-box .preview-div .preview-screen").animate({
                        scrollTop: scrollTop
                    }, 300);
                    return;
                }
            }
        },
        refreshSort: function () {
            var self = this;

            setTimeout(function () {

                $(".draggable-element").each(function (i) {
                    $(this).attr("data-sort", i);
                });

                for (var i = 0; i < self.data.length; i++) {
                    self.data[i].index = $(".draggable-element[data-index=" + i + "]").attr("data-index");
                    self.data[i].sort = $(".draggable-element[data-index=" + i + "]").attr("data-sort");
                }

                self.data.push({});
                self.data.pop();

                //如果当前编辑的组件不存在了，则选中最后一个
                if (parseInt(self.currentIndex) >= self.data.length) self.currentIndex--;

                $(".draggable-element[data-index=" + self.currentIndex + "] .edit-attribute .attr-wrap").css("height", ($(window).height() - 140) + "px");

                if (self.isAdd && self.changeIndex > -1 && (self.changeIndex != self.currentIndex) && self.changeIndex < (self.data.length - 1)) {
                    var curr = $(".draggable-element[data-index=" + self.changeIndex + "]");
                    var last = $(".draggable-element[data-index=" + (self.data.length - 1) + "]");

                    curr.after(last);
                    self.changeIndex = self.currentIndex;
                }

                // 显示插件添加的数量，防止一进入看到代码
                $(".js-component-add-count").show();

            }, 50);
        },
        //改变当前的删除弹出框的显示状态
        delComponent: function (i) {
            var self = this;
            layer.confirm('确定要删除吗?', {
                title: '操作提示'
            }, function (index) {
                self.data.splice(i, 1);
                //删除当前组件后，选中最后一个组件进行编辑
                if (self.data[self.data.length - 1]) {
                    self.currentIndex = $(".draggable-element:last").attr("data-index");
                    self.refreshSort();
                }
                layer.close(index);

            });
        },
        submitVerify: function () {

			if (this.global.title == "") {
				layer.msg('请输入页面名称');
				this.currentIndex = -99;
				this.refreshSort();
				return false;
			} else if (this.global.title.length > 50) {
				layer.msg('页面名称最多50个字符');
				this.currentIndex = -99;
				this.refreshSort();
				return false;
			}

			// if (this.global.popWindow.count != -1 && this.global.popWindow.imageUrl == '') {
			// 	layer.msg('请上传弹框广告');
			// 	this.currentIndex = -99;
			// 	this.refreshSort();
			// 	return false;
			// }

			for (var i = 0; i < this.data.length; i++) {
				try {
					if (this.data[i].verify) {
						for (var j = 0; j < this.data[i].verify.length; j++) {
							var res = this.data[i].verify[j]();
							if (!res.code) {
								this.currentIndex = i;
								this.refreshSort();
								layer.msg(res.message);
								return false;
							}
						}
					}
				} catch (e) {
					console.log("verify Error:" + e, i, this.data[i]);
				}
			}
			return true;
		},
        //转换图片路径
		changeImgUrl: function (url) {
			if (url == null || url == "") { return ''; }
			else return url;
		}
    }
})

// 拖拽事件
$('.screen_center').DDSort({
    //拖拽数据源
    target: '.draggable-element',
    //拖拽时显示的样式
    floatStyle: {
        'border': '1px solid #FF6A00',
        'background-color': '#ffffff'
    },
    //设置可拖拽区域
    draggableArea: "preview-draggable",
    //拖拽中，隐藏右侧编辑属性栏
    move: function (index) {
        if ($(".draggable-element[data-index='" + index + "'] .edit-attribute").attr("data-have-edit") == 1)
            $(".draggable-element[data-index='" + index + "'] .edit-attribute").hide();
    },

    //拖拽结束后，选择当前拖拽，并且显示右侧编辑属性栏，刷新数据
    up: function (index) {
        if ($(".draggable-element[data-index='" + index + "'] .edit-attribute").attr("data-have-edit") == 1) {
            vue.currentIndex = index;
            $(".draggable-element[data-index='" + index + "'] .edit-attribute").show();
        }
        vue.refreshSort();
    }
});
