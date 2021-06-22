var emptyHtml = '<div class="text-slide">';
emptyHtml += '<slide v-bind:data="{ field : \'marginTop\', label : \'页面间距\' }"></slide>';
emptyHtml += '</div>';


var styleHtml = '<div class="layui-form-item">';
styleHtml += '<label class="layui-form-label sm">模板样式</label>';
styleHtml += '<div class="layui-input-block" style="margin-left: 100px;">';
styleHtml += '<div v-if="data.styleName" class="ns-input-text ns-text-color selected-style" v-on:click="selectTestStyle">{{data.styleName}} <i class="layui-icon layui-icon-right"></i></div>';
styleHtml += '<div v-else class="ns-input-text selected-style" style="color: #323233;" v-on:click="selectTestStyle">选择 <i class="layui-icon layui-icon-right"></i></div>';
styleHtml += '</div>';
styleHtml += '</div>';

Vue.component("text-empty", {
    template: emptyHtml,
    data: function () {
        return {
            data: this.$parent.data,
            marginTop: this.$parent.data.marginTop,
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
            var _self = this;
            if (this.data.title.length == 0) {
                res.code = false;
                res.message = "文本不能为空";
                setTimeout(function () {
                    $("#title_" + _self.data.index).focus();
                }, 10);
            }
            return res;
        },
    }
});

Vue.component("text-style", {
    template: styleHtml,
    data: function () {
        return {
            data: this.$parent.data,
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
        selectTestStyle: function () {
            var self = this;
            layer.open({
                type: 1,
                title: '风格选择',
                area: ['930px', '630px'],
                btn: ['确定', '返回'],
                content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .style-list-box").html(),
                success: function (layero, index) {
                    $(".layui-layer-content input[name='style']").val(self.data.style);
                    $(".layui-layer-content input[name='sub']").val(self.data.sub);
                    $(".layui-layer-content input[name='style_name']").val(self.data.styleName);
                    $("body").on("click", ".layui-layer-content .style-list-con .style-li", function () {
                        $(this).addClass("selected ns-border-color").siblings().removeClass("selected ns-border-color ns-bg-color-after");
                        $(".layui-layer-content input[name='style']").val($(this).index() + 1);
                        $(".layui-layer-content input[name='sub']").val($(this).find("input").val());
                        $(".layui-layer-content input[name='style_name']").val($(this).find("span").text());
                    });
                },
                yes: function (index, layero) {
                    self.data.style = $(".layui-layer-content input[name='style']").val();
                    self.data.sub = $(".layui-layer-content input[name='sub']").val();
                    self.data.styleName = $(".layui-layer-content input[name='style_name']").val();
                    layer.closeAll()
                }
            });
        },
    }
});

// 主标题文字粗细
var fontWeightHtml = '<div class="layui-form-item ns-icon-radio">';
fontWeightHtml += '<label class="layui-form-label sm">文字粗细</label>';
fontWeightHtml += '<div class="layui-input-block">';
fontWeightHtml += '<template v-for="(item, index) in alignStyleList" v-bind:k="index">';
fontWeightHtml += '<span :class="[item.className == data.fontWeight ? \'\' : \'layui-hide\']">{{item.name}}</span>';
fontWeightHtml += '</template>';
fontWeightHtml += '<ul class="ns-icon">';
fontWeightHtml += '<li v-for="(item, index) in alignStyleList" v-bind:k="index" :class="[item.className == data.fontWeight ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" @click="changeSelectedStyle(index)">';
fontWeightHtml += '<img v-if="item.className == data.fontWeight" :src="item.selectedSrc" />'
fontWeightHtml += '<img v-else :src="item.src" />'
fontWeightHtml += '</li>';
fontWeightHtml += '</ul>';
fontWeightHtml += '</div>';
fontWeightHtml += '</div>';

Vue.component("font-weight", {
    template: fontWeightHtml,
    data: function () {
        return {
            data: this.$parent.data,
            alignStyleList: [] // 对齐方式集合
        };
    },
    created: function () {
        if (!this.$parent.data.verify) this.$parent.data.verify = [];
        this.$parent.data.verify.push(this.verify); //加载验证方法

        this.initAlignStyleList();
    },
    methods: {
        verify: function () {
            var res = {
                code: true,
                message: ""
            };
            return res;
        },
        initAlignStyleList: function () {
            var prefix = textResourcePath + "/text/img";

            this.alignStyleList.push({
                name: "粗",
                src: prefix + "/blod.png",
                selectedSrc: prefix + "/blod_1.png",
                className: "600"
            });
            this.alignStyleList.push({
                name: "细",
                src: prefix + "/normal.png",
                selectedSrc: prefix + "/normal_1.png",
                className: "500"
            });
        },
        changeSelectedStyle: function (index) {
            for (var i = 0; i < this.alignStyleList.length; i++) {
                if (i == index) {
                    this.data.fontWeight = this.alignStyleList[i].className;
                }
            }
        }
    },
});

// 文字大小
var textFontSizeHtml = '<div class="layui-form-item ns-icon-radio">';
textFontSizeHtml += '<label class="layui-form-label sm">{{data.label}}</label>';
textFontSizeHtml += '<div class="layui-input-block">';
textFontSizeHtml += '<template v-for="(item, index) in list" v-bind:k="index">';
textFontSizeHtml += '<span :class="[item.value == parent[data.field] ? \'\' : \'layui-hide\']">{{item.value}}px</span>';
textFontSizeHtml += '</template>';
textFontSizeHtml += '<ul class="ns-icon">';
textFontSizeHtml += '<li v-for="(item, index) in list" v-bind:k="index" :class="[item.value == parent[data.field] ? \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\']" :title="item.value + \'px\'" @click="parent[data.field] = item.value">';
textFontSizeHtml += '<img v-if="item.value == parent[data.field]" :src="item.selectedSrc" />'
textFontSizeHtml += '<img v-else :src="item.src" />'
textFontSizeHtml += '</li>';
textFontSizeHtml += '</ul>';
textFontSizeHtml += '</div>';
textFontSizeHtml += '</div>';

Vue.component("text-font-size", {
    template: textFontSizeHtml,
    props: {
        data: {
            type: Object,
            default: function () {
                return {
                    field: "fontSize",
                    label: "文字大小",
                    max: 16
                };
            }
        }
    },
    data: function () {
        return {
            list: [],
            parent: this.$parent.data,
        };
    },
    created: function () {
        if (this.data.label == undefined) this.data.label = "文字大小";
        if (this.data.field == undefined) this.data.field = "fontSize";
        if (this.data.max == undefined) this.data.max = "16";

        if (this.data.max == 12) {
            this.list = [{
                label: "小",
                value: "12",
                src: textResourcePath + "/text/img/font_12.png",
                selectedSrc: textResourcePath + "/text/img/font_12_1.png"
            }];
        } else if (this.data.max == 14) {
            this.list = [{
                    label: "小",
                    value: "12",
                    src: textResourcePath + "/text/img/font_12.png",
                    selectedSrc: textResourcePath + "/text/img/font_12_1.png"
                },
                {
                    label: "中",
                    value: "14",
                    src: textResourcePath + "/text/img/font_14.png",
                    selectedSrc: textResourcePath + "/text/img/font_14_1.png"
                }
            ]
        } else {
            this.list = [{
                    label: "小",
                    value: "12",
                    src: textResourcePath + "/text/img/font_12.png",
                    selectedSrc: textResourcePath + "/text/img/font_12_1.png"
                },
                {
                    label: "中",
                    value: "14",
                    src: textResourcePath + "/text/img/font_14.png",
                    selectedSrc: textResourcePath + "/text/img/font_14_1.png"
                },
                {
                    label: "大",
                    value: "16",
                    src: textResourcePath + "/text/img/font_16.png",
                    selectedSrc: textResourcePath + "/text/img/font_16_1.png"
                }
            ];
        }
    },
    watch: {
        data: function (val, oldVal) {
            if (val.field == undefined) val.field = oldVal.field;
            if (val.label == undefined) val.label = "文字大小";
            if (val.max == undefined) val.max = "16";
        },
    }
});