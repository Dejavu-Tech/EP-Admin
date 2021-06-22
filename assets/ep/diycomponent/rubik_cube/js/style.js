/**
 * [魔方]属性组件
 * 修改时间：2018年9月13日19:08:50
 */

//预览自定义魔方组件
var diyHtml = "<div v-html='html' style='position:relative;padding:1px;background:#ffffff;'></div>";//1px用于解决定位偏差问题
Vue.component("rubik-cube-diy-html",{
	
	props : ["diyHtml"],
	template : diyHtml,
	created : function(){
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
		this.html = this.diyHtml.replace(/&quot;/g,'"');
		// console.log(this.html);
	},
	data : function(){
		
		return {
			html : ""
		};
	},
	watch : {
		diyHtml : function(v){
			this.html = v.toString().replace(/&quot;/g,'"');
			// console.log(this.html);
		}
	},
	methods: {
		verify : function () {
			var res = { code : true, message : "" };
			return res;
		},
	},
	
});

//编辑属性组件
var rubikCubeHtml = '<div class="layui-form-item">';
	
	rubikCubeHtml += '<div class="selected-template-intro layui-form-item">';
		rubikCubeHtml += '<label class="layui-form-label sm">选择模板</label>';
		rubikCubeHtml += '<div class="layui-input-block">';
			rubikCubeHtml += '<span v-for="(item,i) in templateList" v-bind:class="[item.className == $parent.data.selectedTemplate ? \'\' : \'layui-hide\']">{{item.name}}</span>';
		rubikCubeHtml += '</div>';
	rubikCubeHtml += '</div>';
	
	rubikCubeHtml += '<div class="selected-template-list layui-form-item">';
		rubikCubeHtml += '<ul>';
			rubikCubeHtml += '<li v-for="(item,i) in templateList" v-bind:class="[(item.className == $parent.data.selectedTemplate) ?  \'ns-text-color ns-border-color ns-bg-color-diaphaneity\' : \'\' ]" v-on:click="changeTemplateList(i)">';
				rubikCubeHtml += '<img v-if="item.className == $parent.data.selectedTemplate" v-bind:src="item.subsrc" />';
				rubikCubeHtml += '<img v-else v-bind:src="item.src" />';
			rubikCubeHtml += '</li>';
		rubikCubeHtml += '</ul>';
	rubikCubeHtml += '</div>';
	
	rubikCubeHtml += '<div v-if="isShowCustomRubikCube" class="layui-form-item">';
		rubikCubeHtml += '<label class="layui-form-label sm">魔方密度</label>';
		rubikCubeHtml += '<div class="layui-input-block">';
		
			rubikCubeHtml += '<div v-bind:class="{ \'layui-unselect layui-form-select\' : true, \'layui-form-selected\' : isShowRubikCubeDensity }" v-on:click="isShowRubikCubeDensity=!isShowRubikCubeDensity;">';

				rubikCubeHtml += '<div class="layui-select-title">';
					rubikCubeHtml += '<input type="text" placeholder="请选择" v-bind:value="customRubikCubeList[selectIndex].text" readonly="readonly" class="layui-input layui-unselect">';
					rubikCubeHtml += '<i class="layui-edge"></i>';
				rubikCubeHtml += '</div>';
	
				rubikCubeHtml += '<dl class="layui-anim layui-anim-upbit">';
					rubikCubeHtml += '<dd v-for="(item,index) in customRubikCubeList" v-bind:class="{ \'layui-this\' : (customRubikCube==item.value) }" v-on:click.stop="customRubikCube=item.value;isShowRubikCubeDensity=false;selectIndex=index;" >{{item.text}}</dd>';
				rubikCubeHtml += '</dl>';
				
			rubikCubeHtml += '</div>';
			
		rubikCubeHtml += '</div>';
	rubikCubeHtml += '</div>';
	
	rubikCubeHtml += '<div class="layui-form-item">';
		rubikCubeHtml += '<label class="layui-form-label sm">魔方布局</label>';
	rubikCubeHtml += '</div>';
	
	// rubikCubeHtml += '<p class="ns-word-aux">选定布局区域，在下方添加图片，建议添加比例一致的图片</p>';
	
	rubikCubeHtml += '<div class="layui-form-item selected-template-layout">';
		rubikCubeHtml += '<div class="layui-input-block layout" v-for="item in templateList" v-if="(item.className == $parent.data.selectedTemplate) && !isShowCustomRubikCube">';
			rubikCubeHtml += '<ul>';
				rubikCubeHtml += '<li v-for="(li,i) in item.dimensionScale" v-bind:class="[item.className]">';
					rubikCubeHtml += '<div class="empty" v-bind:class="[item.className]" v-show="currentList.length>0 && !currentList[i].imageUrl">';
						rubikCubeHtml += '<p>{{li.name}}</p>';
						rubikCubeHtml += '<p>{{li.desc}}</p>';
						// rubikCubeHtml += '<p>{{li.size}}</p>';
					rubikCubeHtml += '</div>';
					rubikCubeHtml += '<div class="have-preview-image" v-show="currentList.length>0 && currentList[i].imageUrl">';
						rubikCubeHtml += '<img v-bind:src="$parent.$parent.changeImgUrl(currentList[i].imageUrl)"/>';
					rubikCubeHtml += '</div>';
				rubikCubeHtml += '</li>';
			rubikCubeHtml += '</ul>';
			//魔方
			rubikCubeHtml += '<ul v-if="isShowCustomRubikCube">';
				rubikCubeHtml += '<li ref="rubikCube" class="custom-rubik-cube" v-for="(item,i) in customRubikCubeArray" v-bind:class="{ \'ns-border-color\' : item.selected,\'border-left\': (i%customRubikCube==0), \'border-bottom\' : (item.row == customRubikCube ) }" v-on:click="customRubikCubeClick(i)" v-on:mousemove="customRubikCubeMouseMove(i)" v-bind:style="{ width : (100/parseInt(customRubikCube)) + \'%\', height : $parent.data.heightArray[customRubikCube-4], lineHeight : $parent.data.heightArray[customRubikCube-4] }">';
					rubikCubeHtml += '<div v-if="!item.selected">+</div>';
				rubikCubeHtml += '</li>';
			rubikCubeHtml += '</ul>';
			
			//魔方
			rubikCubeHtml += '<template v-if="isShowCustomRubikCube && currentList.length>0">';
				rubikCubeHtml += '<div ref="selectedRubikCube" v-for="(item,i) in selectedRubikCubeArray" class="selected-rubik-cube" v-bind:style="{ top : item.top,left : item.left, width : item.width, height : item.height, lineHeight : item.height + \'px\', zIndex:(item.selected)?1:0, position : \'absolute\',textAlign : \'center\' }" v-bind:class="{ \'ns-border-color\' : item.selected, \'have-image\' : currentList[i].imageUrl }"';
				rubikCubeHtml += ' v-on:click="selectedRubikCubeClick(i)"';
				rubikCubeHtml += ' >';
					rubikCubeHtml += '<div class="image-url" v-show="currentList[i].imageUrl" v-bind:style="{ lineHeight : item.lineHeight, width : \'100%\', height : \'100%\' }">';
						rubikCubeHtml += '<a v-bind:href="(currentList[i].link) ? currentList[i].link.h5_url : \'javascript:;\' "><img v-bind:src="$parent.$parent.changeImgUrl(currentList[i].imageUrl)" style="width: auto;height: auto;max-width: 100%;max-height: 100%;"></a>';
					rubikCubeHtml += '</div>';
					rubikCubeHtml += '<span v-show="!currentList[i].imageUrl">{{item.proportion}}<template v-if="customRubikCube==4">比例</template></span>';
					rubikCubeHtml += '<i class="del" v-on:click.stop="deleteSelectedRubikCubeClick(i)" data-disabled="1">x</i>';
				rubikCubeHtml += '</div>';
			rubikCubeHtml += '</template>';
			
			rubikCubeHtml += '<div class="image-ad-list" v-if="currentList.length>0 && currentIndex<currentList.length">';
				rubikCubeHtml += '<ul>';
					rubikCubeHtml += '<li v-for="(li,i) in item.dimensionScale">';
						rubikCubeHtml += '<img-sec-upload v-bind:data="{ data : currentList[i], callback : refreshDiyHtml }"></img-sec-upload>';
						rubikCubeHtml += '<div class="content-block">';
							rubikCubeHtml += '<span style="padding-left: 2px;">{{li.name}}</span>';
							rubikCubeHtml += '<sel-link v-bind:data="{ field : $parent.data.list[i].link }" v-bind:callback="linkCallBack" v-bind:refresh="$parent.data.list[i].link"></sel-link>';
						rubikCubeHtml += '</div>';
					rubikCubeHtml += '</li>';
					
				rubikCubeHtml += '</ul>';
			rubikCubeHtml += '</div>';

		rubikCubeHtml += '</div>';

	rubikCubeHtml += '</div>';
rubikCubeHtml += '</div>';

Vue.component("rubik-cube",{
	
	props : {},
	
	data : function(){
		return {
			
			//是否显示魔方密度
			isShowCustomRubikCube : false,
			
			//布局密度
			customRubikCube : this.$parent.data.customRubikCube,
			
			//自定义区域集合
			customRubikCubeArray : [],
			
			//已设定好的自定义区域集合
			selectedRubikCubeArray : this.$parent.data.selectedRubikCubeArray,
			
			//当前选中的布局下标
			selectIndex : 0,
			
			//控制魔方密度下拉框的显示隐藏
			isShowRubikCubeDensity : false,
			
			//可选择的魔方模板
			templateList : [],
			
			customRubikCubeList : [
				{value : 4,text : "4x4"},
				{value : 5,text : "5x5"},
				{value : 6,text : "6x6"},
				{value : 7,text : "7x7"}
			],
			
			//当前编辑图片的链接地址
			currentIndex : 0,
			data : this.$parent.data,
			currentList : this.$parent.data.list,
		}
	},
	
	template : rubikCubeHtml,
	
	created : function(){
		this.initCustomRubikCubeArray();
		this.initTemplateList();
		//选中当前编辑的自定义布局下标
		for(var i=0;i<this.selectedRubikCubeArray.length;i++){
			if(this.selectedRubikCubeArray[i].selected){
				this.changeCurrentIndex(i);
				break;
			}
		}
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
	},

	methods : {
		//初始化自定义区域集合
		initCustomRubikCubeArray : function(){
			for(var i=0;i<this.customRubikCube;i++){
				
				for(var j=0;j<this.customRubikCube;j++){
					var obj = {
						coordinates : (i+1) + ":" + (j+1),
						row : (i+1),				//行
						column : (j+1),				//列
						selected : false,			//是否已选择
						start : false,				//开始
						finish : false,				//是否已设定
						ranksNumber : "",			//行列数量
						selectedCoordinates : []	//已选择的行列
					};
					this.customRubikCubeArray.push(obj);
				}
			}
			
//			console.log(JSON.stringify(this.customRubikCubeArray));
		},
		
		//初始化可选择的模板集合
		initTemplateList : function(){
			var prefix = rubikCubeResourcePath + "/rubik_cube/img";
			this.templateList.push({ name : "1行2个", src : prefix + "/rubik_cube_row1_of2.png", subsrc : prefix + "/rubik_cube_row1_of2_2.png", className : "row1-of2", dimensionScale : [{desc: "宽度50%", size: "200px * 200px", name: "图一"}, {desc: "宽度50%", size: "200px * 200px", name: "图二"}], descAux: "选定布局区域，在下方添加图片，建议添加尺寸一致的图片，宽度最小建议为：200px" });
			this.templateList.push({ name : "1行3个", src : prefix + "/rubik_cube_row1_of3.png", subsrc : prefix + "/rubik_cube_row1_of3_2.png", className : "row1-of3", dimensionScale : [{desc: "宽度33.33%", size: "200px * 200px", name: "图一"}, {desc: "宽度33.33%", size: "200px * 200px", name: "图二"}, {desc: "宽度33.33%", size: "200px * 200px", name: "图三"}], descAux: "选定布局区域，在下方添加图片，建议添加尺寸一致的图片，宽度最小建议为：130px" });
			this.templateList.push({ name : "1行4个", src : prefix + "/rubik_cube_row1_of4.png", subsrc : prefix + "/rubik_cube_row1_of4_2.png", className : "row1-of4", dimensionScale : [{desc: "宽度25%", size: "200px * 200px", name: "图一"},{desc: "宽度25%", size: "200px * 200px", name: "图二"},{desc: "宽度25%", size: "200px * 200px", name: "图三"}, {desc: "宽度25%", size: "200px * 200px", name: "图四"},], descAux: "选定布局区域，在下方添加图片，建议添加尺寸一致的图片，宽度最小建议为：100px" });
			this.templateList.push({ name : "2左2右", src : prefix + "/rubik_cube_row2_lt_of2_rt.png", subsrc : prefix + "/rubik_cube_row2_lt_of2_rt_2.png", className : "row2-lt-of2-rt", dimensionScale : [{desc: "宽度50%", size: "200px * 200px", name: "图一"}, {desc: "宽度50%", size: "200px * 200px", name: "图二"}, {desc: "宽度50%", size: "200px * 200px", name: "图三"}, {desc: "宽度50%", size: "200px * 200px", name: "图四"}], descAux: "选定布局区域，在下方添加图片，建议添加尺寸一致的图片，宽度最小建议为：200px" });
			this.templateList.push({ name : "1左2右", src : prefix + "/rubik_cube_row1_lt_of2_rt.png", subsrc : prefix + "/rubik_cube_row1_lt_of2_rt_2.png", className : "row1-lt-of2-rt", dimensionScale : [{desc: "宽度50% * 高度100%", size: "200px * 400px", name: "图一"}, {desc: "宽度50% * 高度50%", size: "200px * 200px", name: "图二"}, {desc: "宽度50% * 高度50%", size: "200px * 200px", name: "图三"}], descAux: "选定布局区域，在下方添加图片，宽度最小建议为：200px，右侧两张图片高度一致，左侧图片高度为右侧两张图片高度之和（例：左侧图片尺寸：200px * 300px，右侧两张图片尺寸：200px * 150px）" });
			this.templateList.push({ name : "1上2下", src : prefix + "/rubik_cube_row1_tp_of2_bm.png", subsrc : prefix + "/rubik_cube_row1_tp_of2_bm_2.png", className : "row1-tp-of2-bm", dimensionScale : [{desc: "宽度100% * 高度50%", size: "400px * 200px", name: "图一"}, {desc: "宽度50% * 高度50%", size: "200px * 200px", name: "图二"}, {desc: "宽度50% * 高度500%", size: "200px * 200px", name: "图三"}], descAux: "选定布局区域，在下方添加图片，上方一张图片的宽度为下方两张图片宽度之和，下放两张图片尺寸一致，高度可根据实际需求自行确定（例：上方图片尺寸：400px * 150px，下方两张图片尺寸：200px * 150px）" });
			this.templateList.push({ name : "1左3右", src : prefix + "/rubik_cube_row1_lt_of1_tp_of2_bm.png", subsrc : prefix + "/rubik_cube_row1_lt_of1_tp_of2_bm_2.png", className : "row1-lt-of1-tp-of2-bm",dimensionScale : [{desc: "宽度50% * 高度100%", size: "200px * 400px", name: "图一"}, {desc: "宽度50% * 高度50%", size: "200px * 200px", name: "图二"}, {desc: "宽度25% * 高度50%", size: "100px * 200px", name: "图三"}, {desc: "宽度25% * 高度50%", size: "100px * 200px", name: "图四"}], descAux: "选定布局区域，在下方添加图片，左右两侧内容宽高相同，右侧上下区域高度各占50%，右侧内容下半部分两张图片的宽度相同，各占右侧内容宽度的50%（例：左侧图片尺寸：200px * 400px，右侧上半部分图片尺寸：200px * 200px，右侧下半部分两张图片尺寸：100px * 200px）" });
			// this.templateList.push({ name : "自定义", src : prefix + "/rubik_cube_diy.png", className : "custom-rubik-cube", dimensionScale : [] });
			
			//初始化加载时
			for(var i=0;i<this.templateList.length;i++){
				if(this.templateList[i].className == this.$parent.data.selectedTemplate){
					//判断当前选中模板是否为自定义区域，然后进行特殊处理
					if(this.templateList[i].dimensionScale.length == 0){
						this.isShowCustomRubikCube = true;//显示自定义
					}
				}
			}
		},
		
		//切换选中模板
		changeTemplateList : function(v){
			this.isShowCustomRubikCube = false;
			for(var i=0;i<this.templateList.length;i++){
				if(i==v){
					this.$parent.data.selectedTemplate = this.templateList[i].className;
					
					//自定义区域特殊处理
					if(this.templateList[i].dimensionScale.length == 0){
						this.isShowCustomRubikCube = true;
						//清空链接地址的数据
						this.currentList.length = 0;
						this.selectedRubikCubeArray.length = 0;
					}else{
						var count = this.templateList[i].dimensionScale.length;
						
						//重置当前编辑的图片集合
						
						//数量不够，进行添加
						if(count>this.currentList.length){
							for(var j=0;j<count;j++){
								if((j + 1) > this.currentList.length) this.currentList.push({ imageUrl : "", link : {} });
							}
						}else{
							//数量不相同时，并且数量超出，减去
							if(count != this.currentList.length){
								for(var j=0;j<this.currentList.length;j++){
									if((j+1)>count){
										// console.log(j);
										this.currentList.splice(j,1);
										j=0;
									}
								}
								//设置当前选中为最后一个
                                this.currentIndex = this.currentList.length - 1;
							}
							
						}
					}
				}
			}
			this.templateList.push({});
			this.templateList.pop();
		},
		
		//选中当前编辑的布局
		changeCurrentIndex : function(index){
			this.currentIndex = index;
		},
		
		//自定义魔方区域点击事件
		customRubikCubeClick : function(position){

			if(this.customRubikCubeArray[position].start && !this.customRubikCubeArray[position].selected){
				// console.log("当前操作还没有结束，不能继续操作哦");
				return;
			}
			
			//再次点击确定
			if(this.customRubikCubeArray[position].selected){
				var ranksNumber = "";
				var selectedCoordinates = "";
				var positionXY = "";

				for(var i=0;i<this.customRubikCubeArray.length;i++){
					if(this.customRubikCubeArray[i].selected && this.customRubikCubeArray[i].start){
						ranksNumber = this.customRubikCubeArray[i].ranksNumber;//行列数量
						selectedCoordinates = this.customRubikCubeArray[i].selectedCoordinates;//已选中的坐标集合
					}
					if(this.customRubikCubeArray[i].selected && !this.customRubikCubeArray[i].finish){
						if(positionXY == "") positionXY = $(this.$refs.rubikCube[i]).position();
					}
				}

				var rowCount = ranksNumber.split(":")[0];//选中的总行数
				var columnCount = ranksNumber.split(":")[1];//选中的总列数

				//按照百分比计算宽高
				var width = (100/this.customRubikCube) * columnCount;
				var height = (100/this.customRubikCube) * rowCount;

                //left值采用百分比计算，公式：(同一行中当前魔方位置之前的宽度总和)

				//公式：(当前列-1) * (100/魔方密度)
                var left = (selectedCoordinates[0].split(":")[1]-1) * (100/this.customRubikCube);

                //1:4 2:4			第四列	left = (4-1)*25 = 75

				//3:3 4:3			第三列	left = (3-1)*25 = 50

				//3:2 3:3 4:2 4:3	第二列、第三列

				//2:3 2:4 3:3 3:4	第三列、第四列

				//1:3 1:4 2:3 2:4 3:3 3:4 4:3 4:4	第三列、第四列

				var selectedRubikCube = {
					proportion : rowCount + "：" + columnCount,
					selected : true,
					top : positionXY.top + "px",
					left : left + "%",
					width : (width + 0.0) + "%",
					height : (height + 0.0) + "%",
					lineHeight :  0 + "px",
					selectedCoordinates : selectedCoordinates
				};
				
				for(var i=0;i<this.selectedRubikCubeArray.length;i++){
					this.selectedRubikCubeArray[i].selected = false;
				}

				this.selectedRubikCubeArray.push(selectedRubikCube);

				for(var i=0;i<this.customRubikCubeArray.length;i++){
					//将当前选中的设置为已完成（表示不可操作了）
					if(this.customRubikCubeArray[i].selected){
						this.customRubikCubeArray[i].finish = true;
					}
					
					//删除开始位置
					if(this.customRubikCubeArray[i].selected && this.customRubikCubeArray[i].start){
						this.customRubikCubeArray[i].start = false;
					}
				}
				
				this.currentList.push({ imageUrl : "", link : {} });
				//默认选择当前添加的下标
				this.changeCurrentIndex(this.currentList.length-1);
				
				//计算当前添加的自定义魔方区域的行高，用于将图片进行垂直居中
				setTimeout(function(){
					selectedRubikCube.lineHeight = $(".draggable-element.selected .rubik-cube .layout div.selected-rubik-cube.selected").outerHeight() + "px";
				},100);
				
			}else{
				//设置当前自定义模块的开始位置，并且设定好它可以如何选中
				for(var i=0;i<this.customRubikCubeArray.length;i++){
					if(i==position){
						this.customRubikCubeArray[i].start = true;
						this.customRubikCubeArray[i].selected = true;
					}else{
						this.customRubikCubeArray[i].start = false;
					}
				}
			}
		},
		
		//自定义魔方区域
		customRubikCubeMouseMove : function(position){
			
			//当前坐标
			var currentMoveCoordinates = this.customRubikCubeArray[position].coordinates;
			var coordinates = "";

			for(var i=0;i<this.customRubikCubeArray.length;i++){
				//找到开始位置
				if(this.customRubikCubeArray[i].selected && this.customRubikCubeArray[i].start){
					coordinates = this.customRubikCubeArray[i].coordinates;
					break;
				}
			}

			//清空
			for(var j=0;j<this.customRubikCubeArray.length;j++){
				if(this.customRubikCubeArray[j].coordinates != coordinates && !this.customRubikCubeArray[j].finish){
					this.customRubikCubeArray[j].selected = false;
				}
			}
			
			if(coordinates != "") {

				//开始行列
				var rowStart = coordinates.split(":")[0];
				var columnStart = coordinates.split(":")[1];

				//结束行列
				var rowEnd = parseInt(currentMoveCoordinates.split(":")[0]);
				var columnEnd = parseInt(currentMoveCoordinates.split(":")[1]);

				var tempRow = rowStart;

				//如果开始行大于结束行，则开始行用结束行，结束行用开始行
				if(rowStart > rowEnd) {
					rowStart = rowEnd;
					rowEnd = tempRow;
				}

				var tempColumn = columnStart;

				//如果开始列大于结束列，则开始列用结束列，结束列用开始列
				if(columnStart > columnEnd) {
					columnStart = columnEnd;
					columnEnd = tempColumn;
				}

				var rowCount = 0; //总行数
				var columnCount = new Array(); //总列数
				var isAdd = true;
				var selectedCoordinates = new Array();

				//遍历行
				for(rowStart; rowStart <= rowEnd; rowStart++) {

					rowCount++;

					//遍历列
					for(var i = columnStart; i <= columnEnd; i++) {

						//当前行列坐标
						var currentCoordinates = rowStart + ":" + i;

						//检测当前的模块是否被占用，如果被占用，那么整个都不能选择
						for(var j=0;j<this.customRubikCubeArray.length;j++){
							if(this.customRubikCubeArray[j].coordinates == currentCoordinates && this.customRubikCubeArray[j].finish){
								isAdd = false;
								break;
							}
						}
						
						if($.inArray(i, columnCount) == -1) columnCount.push(i);
						selectedCoordinates.push(currentCoordinates);
						
						for(var j=0;j<this.customRubikCubeArray.length;j++){
							if(this.customRubikCubeArray[j].coordinates == currentCoordinates){
								this.customRubikCubeArray[j].selected = true;
							}
						}
					}
				}

				if(!isAdd) {
					
					for(var j=0;j<this.customRubikCubeArray.length;j++){
						if(this.customRubikCubeArray[j].selected && !this.customRubikCubeArray[j].finish){
							this.customRubikCubeArray[j].selected = false;
						}
					}

					for(var j=0;j<this.customRubikCubeArray.length;j++){
						if(this.customRubikCubeArray[j].start){
							this.customRubikCubeArray[j].selected = true;
						}
					}
				}
				
				for(var j=0;j<this.customRubikCubeArray.length;j++){
					if(this.customRubikCubeArray[j].selected && this.customRubikCubeArray[j].start){
						this.customRubikCubeArray[j].ranksNumber = rowCount + ":" + columnCount.length;
						this.customRubikCubeArray[j].selectedCoordinates = selectedCoordinates;
					}
				}

				this.customRubikCubeArray.push({});
				this.customRubikCubeArray.pop();
			}
		},
		
		//点击已设定好的自定义区域
		selectedRubikCubeClick : function(position){
			
			for(var i=0;i<this.selectedRubikCubeArray.length;i++){
				if(i==position){
					this.selectedRubikCubeArray[i].selected = true;
				}else{
					this.selectedRubikCubeArray[i].selected = false;
				}
			}
			this.changeCurrentIndex(position);
		},
		
		//删除已设定好的自定义区域
		deleteSelectedRubikCubeClick : function(position){
			
			var selectedCoordinates = this.selectedRubikCubeArray[position].selectedCoordinates;
			for(var i=0;i<selectedCoordinates.length;i++){
				for(var j=0;j<this.customRubikCubeArray.length;j++){
					if(this.customRubikCubeArray[j].coordinates == selectedCoordinates[i]){
						this.customRubikCubeArray[j].selected = false;
						this.customRubikCubeArray[j].finish = false;
						this.customRubikCubeArray[j].ranksNumber = "";
					}
				}
			}
			
			this.currentList.splice(position,1);
			//删除后应该选择最后一个
			this.changeCurrentIndex(this.currentList.length-1);

			for(var i=0;i<this.selectedRubikCubeArray.length;i++){
				if(this.currentIndex == i){
					this.selectedRubikCubeArray[i].selected = true;
					break;
				}
			}
			this.customRubikCubeArray.push({});
			this.customRubikCubeArray.pop();
			this.selectedRubikCubeArray.splice(position,1);
		},
		
		//刷新自定义区域Html代码
		refreshDiyHtml : function(){
			if(this.isShowCustomRubikCube) {
				var self = this;
				setTimeout(function () {
					var height = $(".draggable-element.selected .rubik-cube .layout div.selected-rubik-cube.have-image").parent().outerHeight() + "px";
					var diy = $(".draggable-element.selected .rubik-cube .layout div.selected-rubik-cube.have-image").clone();
					var diyHtml = '<div style="background:#ffffff;height:' + height + ';">';
					diy.each(function () {
						$(this).find(".del").remove();
						$(this).find("span").remove();
						diyHtml += $(this).prop("outerHTML");
					});
					diyHtml += "</div>";
					self.$parent.data.diyHtml = diyHtml.replace(/"/g, "&quot;").replace(/z-index: 1/g, "");
				}, 100);
			}
			
		},
		
		//监听链接地址回调，更新diyHtml
		linkCallBack : function(index){
			this.refreshDiyHtml();
		},
		
		verify :function () {
			var res = {code: true, message: ""};
			if (this.$parent.data.selectedTemplate == "custom-rubik-cube") {
				//自定义魔方，不能有空缺
				var customRubikCubeArray = new Array();
				var selectedCount = 0;//选择数量
				
				for (var i = 0; i < this.customRubikCube; i++) {
					
					for (var j = 0; j < this.customRubikCube; j++) {
						customRubikCubeArray.push((i + 1) + ":" + (j + 1));
					}
				}
				
				for (var i = 0; i < this.customRubikCube; i++) {
					
					for (var j = 0; j < this.customRubikCube; j++) {
						customRubikCubeArray.push((i + 1) + ":" + (j + 1));
					}
				}
				
				for (var i = 0; i < customRubikCubeArray.length; i++) {
					for (var j = 0; j < this.selectedRubikCubeArray.length; j++) {
						var selectedCoordinates = this.selectedRubikCubeArray[j].selectedCoordinates;
						for (var k = 0; k < selectedCoordinates.length; k++) {
							if (customRubikCubeArray[i] == selectedCoordinates[k]) {
								selectedCount++;
							}
						}
					}
				}
				if (selectedCount != customRubikCubeArray.length) {
					res.code = false;
					res.message = "自定义魔方必须填满，不能有空缺哦";
				}
				
			}
			
			for (var i = 0; i < this.currentList.length; i++) {
				var item = this.currentList[i];
				if (!item.imageUrl) {
					res.code = false;
					res.message = "请上传图片";
					break;
				}
			}
			return res;
		}
		
	},
	
	watch : {
		customRubikCube : function(v){

			//自定义区域集合
			this.customRubikCubeArray.splice(0,this.customRubikCubeArray.length);
			
			this.selectedRubikCubeArray.splice(0,this.selectedRubikCubeArray.length);

			this.initCustomRubikCubeArray();
		},
		/**
		 * 监听集合，更新自定义区域Html代码
		 */
		currentList : function(){
			this.refreshDiyHtml();
		}
	}

});