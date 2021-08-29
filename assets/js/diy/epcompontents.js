// 系统内置属性组件
 var resourceHtml = '<div v-show="false"><slot></slot></div>';

//CSS·组件
Vue.component("css", {
	props: ["src"],
	template: resourceHtml,
	created: function () {
		var self = this;
		//内联样式
		if (this.$slots.default) {
			var css = "<style>" + this.$slots.default[0].text + '</style>';
			
			//防止重复加载资源
			if ($("head").html().indexOf(css) == -1) {
				$("head").append(css);
			}
			
			//延迟
			setTimeout(function () {
				self.$parent.data.lazyLoadCss = true;
			}, 10);
		}
		
		//外联样式
		if (this.src) {
			//防止重复加载资源
			if ($("head").html().indexOf(this.src) == -1) {
				var styleNode = createLink(this.src);
				styleOnload(styleNode, function () {
					self.$parent.data.lazyLoadCss = true;
				});
			} else {
				//延迟
				setTimeout(function () {
					self.$parent.data.lazyLoadCss = true;
				}, 10);
			}
		}
		
	}
});

//JavaScript脚本·组件
Vue.component("js", {
	
	props: ["src"],
	template: resourceHtml,
	created: function () {
		var self = this;
		
		//如果JS全部是内部代码，则延迟10毫秒
		//如果JS有内部代码、也有外部JS，则以外部JS加载完成时间为准，同时延迟10毫秒，让外部JS中的组件进行加载
		//如果JS全部是外部代码，则以外部JS加载完成时间为准，同时延迟10毫秒，让外部JS中的组件进行加载
		
		//内联js
		if (this.$slots.default) {
			var script = "<script>" + this.$slots.default[0].text + "</script>";
			$("body").append(script);
			//如果有外部JS，则以外部JS加载完成时间为准
			if (this.$parent.data.outerCountJs == 0) {
				setTimeout(function () {
					self.$parent.data.lazyLoad = true;
				}, 10);
			}
		}
		
		//外联js
		if (this.src) {
			$.getScript(this.src, function (res) {
				setTimeout(function () {
					self.$parent.data.lazyLoad = true;
				}, 10);
			});
		}
	}
});

//[颜色]属性组件
var colorHtml = '<div class="layui-form-item flex">';
colorHtml += 	'<div class="flex_left">';
colorHtml += 		'<label class="layui-form-label sm">{{d.label}}</label>';
colorHtml += 		'<div class="curr_color">{{parent[d.field]}}</div>';
colorHtml += 	'</div>';
colorHtml += 	'<div class="layui-input-block flex_fill">';
colorHtml += 		'<span class="color-selector-reset" v-on:click="reset()">重置</span>';
colorHtml += 		'<div v-bind:id="class_name" class="picker colorSelector"><div v-bind:style="{ background : parent[d.field] }"></div></div>';
// colorHtml += '<div v-bind:id="class_name" v-bind:class="class_name" class="colorSelector"><div v-bind:style="{ background : parent[d.field] }"></div></div>';
colorHtml += 	'</div>';
colorHtml += '</div>';

/**
 * 颜色组件：
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:#333333), 'label' : 文本标签(默认:文字颜色) }
 */
Vue.component("color", {
    props: {
        data: {
            type: Object,
            default: function () {
                return {
                    field: "textColor",
                    label: "文字颜色",
                    defaultcolor: ""
                };
            }
        },
    },

    data: function () {
        return {
            d: this.data,
            class_name: "",
            parent: (Object.keys(this.$parent.data).length) ? this.$parent.data : this.$parent.global,
        };
    },
    created: function () {
        this.bindColor();
    },
    watch:{
    },
    methods: {
        init:function(){

            if (this.data.field == undefined) this.data.field = "textColor";
            if (this.data.label == undefined) this.data.label = "文字颜色";
            if (this.data.value == undefined) this.data.value = "#333333";
            if (this.data.defaultcolor == undefined) this.data.defaultcolor = "";
            if (this.data.defaultvalue == undefined) this.data.defaultvalue = "";

            if(this.data.global == 1) this.parent = this.$parent.global;

            if (this.parent[this.data.field] == undefined) this.$set(this.parent, this.data.field, this.data.value);
            else this.data.value = this.parent[this.data.field];
            this.parent[this.data.field] = this.data.value;

            this.d = this.data;
        },
        reset: function () {
            try {
                if(this.data.global == 1) this.parent = this.$parent.global;
                this.parent[this.d.field] = this.d.defaultcolor;
            } catch (e) {
                console.log("color reset() ERROR:" + e.message);
            }
        },
        bindColor() {
            this.init();
            this.class_name = "colorSelector_" + (this.data.field ? this.data.field : "textColor") + get_math_rant(10);
            var class_name = "." + this.class_name;
            var $self = this;

            setColorPicker($self.data.value, this.class_name, function (hex) {
                try {
                    if(hex) {
                        $self.parent[$self.d.field] = hex;
                    } else {
                        $self.parent[$self.d.field] = "";
                    }
                } catch (e) {
                    console.log("color ERROR:" + e.message);
                }
            });
        },
        refreshData(){
            // 刷新parent、data
            // console.log("this.parent",this.parent);
            if(this.parent.controller && this.parent.controller != vue.data[vue.currentIndex].controller){
                // 数据发送变动
                this.parent = vue.data[vue.currentIndex];
                this.init();
                // console.log("数据发送变动",this.d);
            }
            return this.parent;
        }
    },

    template: colorHtml
});

/**
 * 生成颜色选择器
 * @param defaultColor
 * @param obj
 * @param callBack
 */
function setColorPicker(defaultColor, name, callBack) {

    setTimeout(function () {
        var obj = document.getElementById("picker");
        var a = Colorpicker.create({
            el: name,
            color: defaultColor,
            change: function (elem, hex) {
                $(elem).find("div").css('background', hex);
                if (callBack) callBack(hex);
            }
        });
        if(defaultColor) $("#" + name).find("div").css('background', defaultColor);

    }, 500);
}


//[图片上传]组件
var imageSecHtml = '<div @click="showImageDialog(this)" v-show="condition" class="img-block layui-form ns-text-color" :id="id" v-bind:class="{ \'has-choose-image\' : (myData.data[myData.field]) }">';
imageSecHtml += '<div>';
imageSecHtml += '<template v-if="myData.data[myData.field]">';
imageSecHtml += '<img v-bind:src="changeImgUrl(myData.data[myData.field])" />';
imageSecHtml += '<span>更换图片</span>';
// imageSecHtml += '<i class="del" v-on:click.stop="del()" data-disabled="1" v-if = "isShow == true">x</i>';
imageSecHtml += '</template>';

imageSecHtml += '<template v-else>';
imageSecHtml += '<i class="add">+</i>';
// imageSecHtml += '<i class="del" v-on:click.stop="del()" data-disabled="1">x</i>';
imageSecHtml += '</template>';

imageSecHtml += '</div>';
imageSecHtml += '</div>';

/**
 * 图片上传
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:14), 'label' : 文本标签(默认:文字大小) }
 */
Vue.component("img-sec-upload", {

    template: imageSecHtml,
    props: {
        data: {
            type: Object,
            default: function () {
                return {
                    data: {},
                    field: "imageUrl",
                    callback: null,
                    text: "添加图片"
                };
            }
        },
        condition: {
            type: Boolean,
            default: true
        },
        currIndex: {
            type: Number,
            default: 0
        },
        isShow:{
            type: Boolean,
            default: true
        }
    },
    data: function () {
        return {
            myData: this.data,
            upload : null,
            id : get_math_rant(10),
            // parent: (Object.keys(this.$parent.data).length) ? this.$parent.data : this.data,
        };
    },
    watch: {
        data: function (val, oldVal) {
            if (val.field == undefined) val.field = oldVal.field;
            if (val.text == undefined) val.text = "添加图片";
            this.myData = val;
        }
    },
    created: function () {
        if (this.data.field == undefined) this.data.field = "imageUrl";
        if (this.data.data[this.data.field] == undefined) this.$set(this.data.data, this.data.field, "");
        if (this.data.text == undefined) this.data.text = "添加图片";
        this.id = get_math_rant(10);
    },
    methods: {
        del: function () {
            // console.log(this.$parent.list)
            // this.$parent.list.splice(this.currIndex,1)
            this.data.data[this.data.field] = "";
        },
        //转换图片路径
        changeImgUrl: function (url) {
            if (url == null || url == "") return '';
            else return url;
        },
        showImageDialog: function(elm, options) {
            var self = this;
            var ipt = $(elm).prev();
            var val = ipt.val();
			require(["util"], function (util) {
				options = {
					'class_extra': 'order_menu_icon',
					'global': false,
					'direct': true,
					'multiple': false,
					'fileSizeLimit': 10240
				};
				util.image(val, function (url) {
					if (url.url) {
                        self.data.data[self.data.field] = url.url;
					}

				}, options);
			})
		}
    }
});


//滑块属性组件 用于间距等
var sliderHtml = '<div class="layui-form-item slide-component">';
sliderHtml += '<label class="layui-form-label sm">{{data.label}}</label>';
sliderHtml += '<div class="layui-input-block">';
sliderHtml += '<div v-bind:id="id" class="side-process"></div>';
sliderHtml += '<span class="slide-prompt-text">{{parent[data.field]}}</span>';
sliderHtml += '</div>';
sliderHtml += '</div>';

Vue.component("slide", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "height",
					label: "空白高度",
					max: 100,
					min: 0
				};
			}
		}
	},
	created: function () {
		if (this.data.label == undefined) this.data.label = "空白高度";
		if (this.data.field == undefined) this.data.field = "height";
		if (this.data.max == undefined) this.data.max = 100;
		if (this.data.min == undefined) this.data.min = 0;
		var _self = this;
		setTimeout(function () {
			layui.use('slider', function(){
				var slider = layui.slider;
				var ins = slider.render({
					elem: '#'+_self.id,
					max: _self.data.max,
					min: _self.data.min,
					tips: false,
					theme: '#FF6A00',
					value : _self.parent[_self.data.field],
					change: function(value){
						_self.parent[_self.data.field] = value;
					}
				});

			});
		},10);
		
	},
	watch: {
		data: function (val, oldVal) {

			if (val.field == undefined) val.field = oldVal.field;
			if (val.label == undefined) val.label = "空白高度";
			if (val.max == undefined) val.max = 100;
			if (val.min == undefined) val.min = 0;
		},
	},
	template: sliderHtml,
	data: function () {
		return {
			id : "slide_" + get_math_rant(10),
			parent: this.$parent.data,
		};
	}
});

//链接属性组件
var linkHtml = '<div class="layui-form-item component-links">';
		linkHtml += '<label class="layui-form-label sm">{{myData[0].label}}</label>';
		linkHtml += '<div class="layui-input-block">';
			linkHtml += '<span style="display: none;" v-if="myData[0].field.title" v-bind:title="myData[0].field.title"></span>';
			linkHtml += '<span v-if="myData[0].field.title" v-for="(item,index) in myData[0].operation" class="sm ns-text-color" v-on:click="selected(item.key,item.method)"><span :title="myData[0].field.title">{{myData[0].field.title}}</span><i class="layui-icon layui-icon-right"></i></span>';
			linkHtml += '<span v-else v-for="(item,index) in myData[0].operation" class="sm" style="color: #323233;" v-on:click="selected(item.key,item.method)"><span :title="item.label">{{item.label}}</span><i class="layui-icon layui-icon-right"></i></span>';
		linkHtml += '</div>';
	linkHtml += '</div>';

/**
 * 链接组件：
 * 参数说明：data：当前链接对象, click：绑定事件，触发回调
 */
Vue.component("sel-link", {
	//data：链接对象，callback：回调，refresh：刷新filed
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					//链接对象
					field: null,
					//文本
					label: "链接地址",
					//批量操作对象
					operation: [
						{key: "system", method: '', label: "请选择链接"}
					],
					supportDiyView: "",
                    supportToApplet: 1
				};
			}
		},
		callback: null,
		refresh: null,
	},
	template: linkHtml,
	data: function () {
		return {
			myData: [this.data],//此处用数组的目的是触发变异方法，进行视图更新
		};
	},
	created: function () {
		if (this.data.supportToApplet == undefined) this.data.supportToApplet = 1;
		if (this.data.supportDiyView == undefined) this.data.supportDiyView = "";
		if (this.data.label == undefined) this.data.label = "链接地址";
		if (this.data.operation == undefined) this.data.operation = [{ key : "system", method : '' , label: "请选择链接" }];
	},
	watch: {
		data: function (val, oldVal) {
			if (this.data.supportDiyView == undefined) this.data.supportDiyView = "";
			if (this.data.label == undefined) this.data.label = "链接地址";
			if (this.data.operation == undefined) this.data.operation = [{ key : "system", method : '' , label: "请选择链接" }];
			this.myData[0].field= this.data.field;
		},
		refresh:function (val,oldVal) {
			this.myData[0].field = val;
			this.set(val);
		}
	},
	methods: {
		//设置链接地址
		set: function (link) {
			//由于Vue2.0是单向绑定的：子组件无法修改父组件，但是可以修改单个属性，循环遍历属性赋值
			if (this.data.field) {
				for (var k in link) {
					this.data.field[k] = link[k];
				}
			}

			//触发变异方法，进行视图更新
			this.myData.push({});
			this.myData.pop();
		},
		selected: function (key,method) {
			var $self = this;
			if(key == "system") {
				//系统链接
				select_link($self.myData[0].field,$self.myData[0].supportDiyView, function (data) {
					$self.set(data);
					if ($self.callback) $self.callback.call(this, data);
				}, $self.data.supportToApplet);
			}else {
				//插件自定义链接
				ns[method]($self.myData[0].field, $self.myData[0].supportDiyView, function (data) {
					$self.set(data);
					if ($self.callback) $self.callback.call(this, data);
				}, $self.data.supportToApplet);
			}
		}
	}
});


//[视频上传]组件
var videoSecHtml = '<div @click="showVideoDialog(this)" v-show="condition" class="img-block layui-form ns-text-color" :id="id" v-bind:class="{ \'has-choose-image\' : (myData.data[myData.field]) }">';
videoSecHtml += '<div>';
videoSecHtml += '<template v-if="myData.data[myData.field]">';
videoSecHtml += '<video v-bind:src="changeVideoUrl(myData.data[myData.field])" style="width: 55px;height: 55px;" />';
videoSecHtml += '<span>更换视频</span>';
videoSecHtml += '<i class="del" v-on:click.stop="del()" data-disabled="1" v-if = "isShow == true">x</i>';
videoSecHtml += '</template>';

videoSecHtml += '<template v-else>';
videoSecHtml += '<i class="add">+</i>';
videoSecHtml += '<i class="del" v-on:click.stop="del()" data-disabled="1">x</i>';
videoSecHtml += '</template>';

videoSecHtml += '</div>';
videoSecHtml += '</div>';

/**
 * 视频上传
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:14), 'label' : 文本标签(默认:文字大小) }
 */
 Vue.component("video-upload", {

    template: videoSecHtml,
    props: {
        data: {
            type: Object,
            default: function () {
                return {
                    data: {},
                    field: "videoUrl",
                    callback: null,
                    text: "添加视频"
                };
            }
        },
        condition: {
            type: Boolean,
            default: true
        },
        currIndex: {
            type: Number,
            default: 0
        },
        isShow:{
            type: Boolean,
            default: true
        }
    },
    data: function () {
        return {
            myData: this.data,
            upload : null,
            id : get_math_rant(10),
        };
    },
    watch: {
        data: function (val, oldVal) {
            if (val.field == undefined) val.field = oldVal.field;
            if (val.text == undefined) val.text = "添加视频";
            this.myData = val;
        }
    },
    created: function () {
        if (this.data.field == undefined) this.data.field = "videoUrl";
        if (this.data.data[this.data.field] == undefined) this.$set(this.data.data, this.data.field, "");
        if (this.data.text == undefined) this.data.text = "添加视频";
        this.id = get_math_rant(10);
    },
    methods: {
        del: function () {
            this.data.data[this.data.field] = "";
        },
        //转换图片路径
        changeVideoUrl: function (url) {
            if (url == null || url == "") return '';
            else return url;
        },
        showVideoDialog: function(elm, options) {
            var self = this;
            var ipt = $(elm).prev();
            var val = ipt.val();
			require(["util"], function (util) {
				options = { type: 'video' };
				util.audio(val, function (url) {
					if (url.url) {
                        self.data.data[self.data.field] = url.url;
					}

				}, options);
			})
		}
    }
});