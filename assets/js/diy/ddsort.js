;(function ($) {
	/**
	 * Author: https://github.com/Barrior
	 *
	 * DDSort: drag and drop sorting.
	 * @param {Object} options
	 *        target[string]:        可选，jQuery事件委托选择器字符串，默认'li'
	 *        cloneStyle[object]:    可选，设置占位符元素的样式
	 *        floatStyle[object]:    可选，设置拖动元素的样式
	 *        down[function]:        可选，鼠标按下时执行的函数
	 *        move[function]:        可选，鼠标移动时执行的函数
	 *        up[function]:        可选，鼠标抬起时执行的函数
	 *        draggableArea[string]:可选，设置可拖拽的区域
	 */
	$.fn.DDSort = function (options) {
		var $doc = $(document),
			fnEmpty = function () {
			},
			
			settings = $.extend(true, {
				
				down: fnEmpty,
				move: fnEmpty,
				up: fnEmpty,
				
				target: 'li',
				cloneStyle: {
					'background-color': '#f7f8fa'
				},
				floatStyle: {
					//用固定定位可以防止定位父级不是Body的情况的兼容处理，表示不兼容IE6，无妨
					'position': 'fixed',
					'box-shadow': '10px 10px 20px 0 #eee',
					/*'webkitTransform': 'rotate(4deg)',
					'mozTransform': 'rotate(4deg)',
					'msTransform': 'rotate(4deg)',
					'transform': 'rotate(4deg)'*/
				},
				draggableArea: ''
				
			}, options);
		
		return this.each(function () {
			
			var that = $(this),
				height = 'height',
				width = 'width';
			
			if (that.css('box-sizing') == 'border-box') {
				height = 'outerHeight';
				width = 'outerWidth';
			}
			
			that.on('mousedown.DDSort', settings.target, function (e) {
				//只允许鼠标左键拖动
				if (e.which != 1) {
					return;
				}
				
				//防止表单元素失效
				var tagName = e.target.tagName.toLowerCase();
				if (tagName == 'input' || tagName == 'textarea' || tagName == 'select') {
					return;
				}
				
				var THIS = this,
					$this = $(THIS),
					offset = $this.offset(),
					disX = e.pageX - offset.left,
					disY = e.pageY - offset.top,
					
					clone = $this.clone()
					.css(settings.cloneStyle)
					.css('height', $this[height]())
					.empty(),
					
					hasClone = 1,
					
					//缓存计算
					thisOuterHeight = $this.outerHeight(),
					thatOuterHeight = that.outerHeight(),
					
					//滚动速度
					upSpeed = thisOuterHeight,
					downSpeed = thisOuterHeight,
					maxSpeed = thisOuterHeight * 3;
				
				if (settings.draggableArea != "") {
					//判断当前点击的DOM是否允许拖拽
					var isDraggable = recursiveQuery($(e.target), settings.draggableArea);
					// 特殊处理：带有该属性的禁用
					if($(e.target).parent().attr("data-disabled") || $(e.target).attr("data-disabled")){
						return;
					}
					if (!isDraggable) {
						return;
					}
				}
				
				settings.down.call(THIS);
				
				$doc.on('mousemove.DDSort', function (e) {
					if (hasClone) {
						$this.before(clone)
						.css('width', $this[width]())
						.css(settings.floatStyle)
						.appendTo($this.parent());
						
						hasClone = 0;
					}
					
					var left = e.pageX - disX,
						top = e.pageY - disY,
						
						prev = clone.prev(),
						next = clone.next().not($this);
					
					var gap = $(window).scrollTop();
					var calculate = top - gap;
					
					//检测是否滚动了
					top = ((top - $(window).scrollTop()) != top) ? calculate : top;
					
					$this.css({
						left: left,
						top: top,
						zIndex: 999
					});
					
					//向上排序
					if (prev.length && top < (prev.offset().top - gap) + prev.outerHeight() / 2) {
						
						clone.after(prev);
						
						//向下排序
					} else if (next.length && top + thisOuterHeight > (next.offset().top - gap) + next.outerHeight() / 2) {
						
						clone.before(next);
						
					}
					
					/**
					 * 处理滚动条
					 * that是带着滚动条的元素，这里默认以为that元素是这样的元素（正常情况就是这样），如果使用者事件委托的元素不是这样的元素，那么需要提供接口出来
					 */
					var thatScrollTop = that.scrollTop(),
						thatOffsetTop = that.offset().top,
						scrollVal;
					
					//向上滚动
					if (top < thatOffsetTop) {
						
						downSpeed = thisOuterHeight;
						upSpeed = ++upSpeed > maxSpeed ? maxSpeed : upSpeed;
						scrollVal = thatScrollTop - upSpeed;
						
						//向下滚动
					} else if (top + thisOuterHeight - thatOffsetTop > thatOuterHeight) {
						
						upSpeed = thisOuterHeight;
						downSpeed = ++downSpeed > maxSpeed ? maxSpeed : downSpeed;
						scrollVal = thatScrollTop + downSpeed;
					}
					
					that.scrollTop(scrollVal);
					
					var index = recursiveQueryIndex($(THIS));
					settings.move.call(THIS, index);
					
				})
				.on('mouseup.DDSort', function () {
					
					$doc.off('mousemove.DDSort mouseup.DDSort');
					
					//click的时候也会触发mouseup事件，加上判断阻止这种情况
					if (!hasClone) {
						clone.before($this.removeAttr('style')).remove();
						var index = recursiveQueryIndex($(THIS));
						settings.up.call(THIS, index);
					}
				});
				
				return false;
			});
		});
	};
	
	//当前递归次数
	var currentRecursiveCount = 0;
	
	//最大递归次数
	var recursiveMaxCount = 20;
	
	/**
	 * 递归查询当前区域是否允许拖拽
	 * 创建时间：2018年7月3日18:18:01
	 */
	function recursiveQuery(o, draggableArea) {
		if (o.hasClass(draggableArea)) {
			//允许拖拽，清空递归次数
			currentRecursiveCount = 0;
			// console.log($(o));
			return true;
		} else {
			if (currentRecursiveCount <= recursiveMaxCount) {
				currentRecursiveCount++;
				return recursiveQuery(o.parent(), draggableArea);
			} else {
				//清空递归次数
//				console.log("清空递归次数");
				currentRecursiveCount = 0;
				return false;
			}
		}
	}
	
	/**
	 * 递归查询当前拖拽的下标
	 * 创建时间：2018年7月3日18:18:01
	 */
	function recursiveQueryIndex(o) {
		if (o.hasClass("draggable-element")) {
			//允许拖拽，清空递归次数
			currentRecursiveCount = 0;
			return $(o).attr("data-index");
		} else {
			if (currentRecursiveCount <= recursiveMaxCount) {
				currentRecursiveCount++;
				return recursiveQueryIndex(o.parent());
			} else {
				//清空递归次数
				// console.log("清空递归次数");
				currentRecursiveCount = 0;
				return -1;
			}
		}
	}
	
})(jQuery);