<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Model;

class AllformModel{
	/**
	 * @desc 获取万能表单信息
	 * @param $id
	 * @return mixed
	 */
	public function getFormsById($id){
		$forms_data = M('eaterplanet_ecommerce_forms')->where( array('id' => $id) )->find();
		return $forms_data;
	}

	/**
	 * @desc 通过条件获取表单信息
	 * @param $condition
	 * @return mixed
	 */
	public function getFormsByWhere($condition){
		$forms_data = M('eaterplanet_ecommerce_forms')->where($condition)->find();
		return $forms_data;
	}

	/**
	 * @desc 表单类型名称
	 * @param $form_type
	 * @return string
	 */
	public function getFormTypeName($form_type){
		$form_type_text = "";
		switch ($form_type){
			case 'goods':
				$form_type_text = '商品表单';
				break;
			case 'order':
				$form_type_text = '下单表单';
				break;
			case 'apply':
				$form_type_text = '申请表单';
				break;
		}
		return $form_type_text;
	}

	/**
	 * @desc 获取已收集的表单数
	 * @param $form_id
	 * @return mixed
	 */
	public function getFormInfoCountByFormId($form_id){
		$count = M('eaterplanet_ecommerce_form_info')->where( array('form_id' => $form_id) )->count();
		return $count;
	}

	/**
	 * @desc 删除表单信息
	 * @param $form_id
	 * @return mixed
	 */
	public function deleteForm($form_id){
		$result = [];
		$res = M('eaterplanet_ecommerce_forms')->where( array('id' => $form_id) )->delete();
		if($res !== false){
			$result['code'] = 1;
		}else{
			$result['code'] = 0;
			$result['message'] = '删除失败';
		}
		return $result;
	}

	public function queryList($gpc){
		$page =  isset($gpc['page']) ? intval($gpc['page']) : 1;
		$page = max(1, $page);
		$page_size = 10;

		$keyword = $gpc['keyword'];
		$type = $gpc['type'];
		$is_ajax = $gpc['is_ajax'];
		$template = $gpc['template'];
		$is_ajax = !empty($is_ajax) ? intval($is_ajax) : 0;

		$condition = " ";
		if(!empty( $keyword )){
			$keyword = trim($keyword);
			$condition .= " and form_name like '%".$keyword."%'";
		}
		if(!empty($type)){
			$condition .= " and form_type = '".$type."'";
		}
		$sql = 'SELECT * FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_forms WHERE 1 ' . $condition .
				' order by id desc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size;
		$list = M()->query($sql);

		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX').'eaterplanet_ecommerce_forms WHERE 1 ' . $condition );

		$total = $total_arr[0]['count'];

		$ret_html = "<tr>
						<th style=\"color:#000;\">表单名称</th>
						<th style=\"color:#000;\">表单类型</th>
						<th style=\"color:#000;\">操作</th>
					</tr>";
		foreach ($list as &$value) {
			$value['form_type_name'] = $this->getFormTypeName($value['form_type']);
			$ret_html .= '<tr>';
			$ret_html .= '	<td>'. $value['form_name'].'</td>';

			$ret_html .= '	<td>'.$this->getFormTypeName($value['form_type']).'</td>';

			if ( isset($template)  && $template == 'mult' ) {
				$ret_html.='  <td style="width:80px;"><a href="javascript:;" class="choose_mult_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
			}else{
				$ret_html.='  <td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
			}
			$ret_html .= '</tr>';
		}
		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));

		if( $is_ajax == 1 )
		{
			return ['html' => $ret_html, 'pager' => $pager ];
		}else{
			return ['list' => $list, 'pager' => $pager ];
		}
	}

	public function getDataList($gpc){
		$page =  isset($gpc['page']) ? intval($gpc['page']) : 1;
		$page_size = 10;
		//客户昵称
		$keyword = $gpc['keyword'];
		//表单id
		$id = $gpc['id'];

		$condition = " and f.form_id = ".$id;

		$sqlcondition = "";

		if (!empty( $keyword )) {
			$keyword = trim($keyword);
			$condition .= ' AND (locate('.$keyword.',m.username) > 0 ) and f.user_id > 0 ';
			$sqlcondition .= ' left join ' .  C('DB_PREFIX') . 'eaterplanet_ecommerce_member m on m.member_id = f.user_id ';
		}
		$sql = 'SELECT f.* FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_form_info as f '.$sqlcondition.' WHERE 1 ' . $condition .
				' order by f.id desc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size;

		$list = M()->query($sql);

		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX').'eaterplanet_ecommerce_form_info as f '.$sqlcondition.' WHERE 1 ' . $condition );
		$total = $total_arr[0]['count'];



		if( !empty($list) )
		{
			foreach( $list as $k=>$item )
			{
				$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $item['user_id'] ) )->find();
				if(!empty($member_info)){
					$list[$k]['username'] = $member_info['username'];
				}
				$list[$k]['addtime'] = date('Y-m-d H:i:s',$item['addtime']);
				$list[$k]['form_type_name'] = $this->getFormTypeName($item['form_type']);
			}
		}

		$pager = pagination2($total, $page, $page_size);

		//表单信息
		$form_data = $this->getFormsById($id);

		$need_data = ['list' => $list, 'pager' => $pager, 'form_info'=>$form_data ];

		return $need_data;
	}

	/**
	 * @desc  获取已收集的表单数据列表
	 * @param $form_id
	 * @return mixed
	 */
	public function getFormInfoListByFormId($form_id){
		$form_info_list = M('eaterplanet_ecommerce_form_info')->where(array('form_id' => $form_id))->select();
		return $form_info_list;
	}

	/**
	 * @desc  获取已收集的表单数据项列表
	 * @param $form_id
	 * @return mixed
	 */
	public function getFormItemListByFormId($form_id){
		$form_item_list = M('eaterplanet_ecommerce_form_item')->where(array('form_id' => $form_id))->order('id asc')->select();
		return $form_item_list;
	}

	/**
	 * @desc 获取导出表单数据列表
	 * @param $form_id
	 * @return array
	 */
	public function getExportFormDataList($form_id){
		$need_data = [];
		//万能表单数据
		$form_data = $this->getFormsById($form_id);

		$title_list = [];
		$form_list = $this->getFormInfoListByFormId($form_id);

		if(!empty($form_list)){
			foreach($form_list as $k=>$v){
				$form_list[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
				$form_item_list = $this->getFormItemListByFormId($v['id']);
				if(!empty($form_item_list)){
					foreach($form_item_list as $ik=>$iv){
						if(!in_array($iv['item_name'],$title_list) && $iv['type'] != 'image'){
							array_push($title_list,$iv['item_name']);
						}
						$form_list[$k][$iv['item_name']] = $iv['item_val'];
					}
				}
				$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $v['user_id'] ) )->find();
				if(!empty($member_info)){
					$form_list[$k]['username'] = $member_info['username'];
				}else{
					$form_list[$k]['username'] = "";
				}
			}
		}
		$need_data['form_list'] = $form_list;
		$need_data['title_list'] = $title_list;
		$need_data['form_data'] = $form_data;
		return $need_data;
	}

	/**
	 * @desc 添加万能表单
	 * @return array
	 */
	public function addOrUpdateForm()
	{
		$_GPC = I('request.');
		$id = $_GPC['id'];
		//表单名称
		$form_name = trim($_GPC['form_name']);
		//表单类型
		$form_type = trim($_GPC['form_type']);
		if( empty($form_name))
		{
			return ['code' => 0, 'message' => '请填写表单名称'];
		}
		if( empty($form_type))
		{
			return ['code' => 0, 'message' => '请选择表单类型'];
		}
		$condition = " 1=1 ";
		if(!empty($id)){
			$condition .= " and id != ".$id;
		}
		$condition .= " and form_name = '".$form_name."' ";
		$forms_data = $this->getFormsByWhere($condition);
		if(!empty($forms_data)){
			return ['code' => 0, 'message' => '表单名称已存在，请修改'];
		}
		$title_array = [];
		$random_code_list = trim($_GPC['random_code']);
		if(!empty($random_code_list) && count($random_code_list) > 0){
			foreach($random_code_list as $k=>$v){
				$title = request()->input('title_'.$v, '');
				$title_array[] = trim($title);
			}
		}
		$title_list = array_unique($title_array);
		if(count($title_list) < count($title_array)){
			return ['code' => 0, 'message' => '标题不能重复'];
		}

		$form_content = $this->getFormContent();
		$form_data = array();
		$form_data['form_name'] = $form_name;
		$form_data['form_type'] = $form_type;
		$form_data['form_content'] = $form_content;
		if( $id > 0 )
		{
			M('eaterplanet_ecommerce_forms')->where( array('id' => $id) )->save($form_data);
		}else{
			$form_data['addtime'] = time();
			$id = M('eaterplanet_ecommerce_forms')->add($form_data);
		}
		return ['code' => 1];
	}

	/**
	 * @desc 获取万能表单项数据
	 * @return string
	 */
	public function getFormContent(){
		$content = "";
		$content_array = [];
		$random_code_list = I('request.random_code');
		if(!empty($random_code_list) && count($random_code_list) > 0){
			foreach($random_code_list as $k=>$v){
				$content_array[$k] = $this->getFormItemValue($v);
			}
			$content = serialize($content_array);
		}
		return $content;
	}

	/**
	 * @desc 获取万能表单项信息
	 * @param $random_code
	 * @return array
	 */
	public function getFormItemValue($random_code){
		$form_data = [];
		$_GPC = I('request.');
		$form_type = $_GPC['form_type_'.$random_code];
		$form_data['type'] = $form_type;
		$form_data['random_code'] = $random_code;
		if($form_type == 'image'){//图片
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//最大上传数量
			$max_count = $_GPC['max_count_'.$random_code];
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['max_count'] = $max_count;
		}else if($form_type == 'text'){//单行文本
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示
			$hint = $_GPC['hint_'.$random_code];
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
		}else if($form_type == 'textarea'){//多行文本
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示
			$hint = $_GPC['hint_'.$random_code];
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
		}else if($form_type == 'select'){//下拉选项
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示
			$hint = $_GPC['hint_'.$random_code];
			//选项
			$option_array = [];
			$option_vals = $_GPC['option_val_'.$random_code];
			if(!empty($option_vals) && count($option_vals) > 0){
				foreach($option_vals as $pk=>$pv){
					$option_array[$pk] = $pv;
				}
			}
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
			$form_data['option_val'] = $option_array;
		}else if($form_type == 'radio'){//单选
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//选项
			$option_array = [];
			$option_vals = $_GPC['option_val_'.$random_code];
			if(!empty($option_vals) && count($option_vals) > 0){
				foreach($option_vals as $pk=>$pv){
					$option_array[$pk] = $pv;
				}
			}
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['option_val'] = $option_array;
		}else if($form_type == 'checked'){//多选
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//选项
			$option_array = [];
			$option_vals = $_GPC['option_val_'.$random_code];
			if(!empty($option_vals) && count($option_vals) > 0){
				foreach($option_vals as $pk=>$pv){
					$option_array[$pk] = $pv;
				}
			}
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['option_val'] = $option_array;
		}else if($form_type == 'area'){//地区
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示语
			$hint = $_GPC['hint_'.$random_code];
			//填写条件
			$area_type = $_GPC['area_type_'.$random_code];
			//默认省份
			$province_id = $_GPC['province_id_'.$random_code];
			//默认城市
			$city_id = $_GPC['city_id_'.$random_code];
			//默认区
			$country_id = $_GPC['country_id_'.$random_code];

			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
			$form_data['area_type'] = $area_type;
			$form_data['province_id'] = $province_id;
			$form_data['city_id'] = $city_id;
			$form_data['country_id'] = $country_id;
		}else if($form_type == 'date'){//日期
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示语
			$hint = $_GPC['hint_'.$random_code];
			//日期默认类型
			$date_type = $_GPC['date_type_'.$random_code];
			//指定日期
			$appoint_date = $_GPC['appoint_date_'.$random_code];

			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
			$form_data['date_type'] = $date_type;
			$form_data['appoint_date'] = $appoint_date;
		}else if($form_type == 'date_range'){//日期范围
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//开始日期类型
			$begin_date_type = $_GPC['begin_date_type_'.$random_code];
			//开始日期提示语
			$begin_hint = $_GPC['begin_hint_'.$random_code];
			//开始日期指定日期
			$begin_appoint_date = $_GPC['begin_appoint_date_'.$random_code];

			//结束日期类型
			$end_date_type = $_GPC['end_date_type_'.$random_code];
			//结束日期提示语
			$end_hint = $_GPC['end_hint_'.$random_code];
			//结束日期指定日期
			$end_appoint_date = $_GPC['end_appoint_date_'.$random_code];

			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['begin_date_type'] = $begin_date_type;
			$form_data['begin_hint'] = $begin_hint;
			$form_data['begin_appoint_date'] = $begin_appoint_date;

			$form_data['end_date_type'] = $end_date_type;
			$form_data['end_hint'] = $end_hint;
			$form_data['end_appoint_date'] = $end_appoint_date;
		}else if($form_type == 'idcard'){//身份证号码
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示
			$hint = $_GPC['hint_'.$random_code];
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
		}else if($form_type == 'time'){//时间
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示
			$hint = $_GPC['hint_'.$random_code];
			//时间类型
			$time_type = $_GPC['time_type_'.$random_code];
			//指定时间
			$appoint_time = $_GPC['appoint_time_'.$random_code];

			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
			$form_data['time_type'] = $time_type;
			$form_data['appoint_time'] = $appoint_time;
		}else if($form_type == 'time_range'){//时间范围
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];

			//开始时间类型
			$begin_time_type = $_GPC['begin_time_type_'.$random_code];
			//开始指定时间
			$begin_appoint_time = $_GPC['begin_appoint_time_'.$random_code];
			//开始时间提示语
			$begin_hint = $_GPC['begin_hint_'.$random_code];

			//开始时间类型
			$end_time_type = $_GPC['end_time_type_'.$random_code];
			//开始指定时间
			$end_appoint_time = $_GPC['end_appoint_time_'.$random_code];
			//开始时间提示语
			$end_hint = $_GPC['end_hint_'.$random_code];

			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['begin_time_type'] = $begin_time_type;
			$form_data['begin_appoint_time'] = $begin_appoint_time;
			$form_data['begin_hint'] = $begin_hint;

			$form_data['end_time_type'] = $end_time_type;
			$form_data['end_appoint_time'] = $end_appoint_time;
			$form_data['end_hint'] = $end_hint;
		}else if($form_type == 'telephone'){//手机号码
			//标题
			$title = $_GPC['title_'.$random_code];
			//说明
			$remark = $_GPC['remark_'.$random_code];
			//提示
			$hint = $_GPC['hint_'.$random_code];
			$form_data['title'] = $title;
			$form_data['remark'] = $remark;
			$form_data['hint'] = $hint;
		}
		//是否必填
		$required = $_GPC['required_'.$random_code];
		if($required == 'on'){//必填
			$required = 1;
		}else{
			$required = 0;
		}
		$form_data['required'] = $required;
		return $form_data;
	}

	/**
	 * @desc	获取表单项内容
	 * @param $type
	 * @return array
	 */
	public function getFormItem($type){
		$need_data = [];
		$item_li = "";
		$phone_item = "";
		$random_code = random(10,false);
		if($type == 'image'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="image"/>
                                <div class="action-title"><span class="title">图片</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="图片" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value=""  update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">最大上传数量</div>
                                        <input class="form-control" name="max_count_'.$random_code.'" type="text" placeholder="请输入最大上传数量" value="5">
                                        <div class="input-group-addon">张</div>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                                <div class="template_item">
                                    <span class="item_title" id="text_title_'.$random_code.'">图片</span>
                                    <span class="item_required" id="text_required_'.$random_code.'">*</span>
                                </div>
                                <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                                <div class="tempalte_content">
                                    <div class="image-add">
                                        <div class="add">
                                            <span class="iconfont-m- icon-m-jiahao">+</span>
                                        </div>
                                        <div class="add-text"> 添加图片 </div>
                                        <input type="file" style="display: none;">
                                    </div>
                                </div>
                            </div>';
		}else if($type == 'text'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="text"/>
                                <div class="action-title"><span class="title">单行文本</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="单行文本" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">提示语</div>
                                        <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="请输入" update_id="#text_val_'.$random_code.'" update_type="tip">
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">单行文本</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <input type="text" placeholder="请输入" id="text_val_'.$random_code.'" class="item-input">
                            </div>
                        </div>';
		}else if($type == 'textarea'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="textarea"/>
                                <div class="action-title"><span class="title">多行文本</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="多行文本"  update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">提示语</div>
                                        <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="请输入" update_id="#text_val_'.$random_code.'" update_type="tip">
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class=\'layui-btn layui-btn-xs deleteBtn\' href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">多行文本</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <textarea type="text" placeholder="请输入" id="text_val_'.$random_code.'" class="item-input item-input-textarea"></textarea>
                            </div>
                        </div>';
		}else if($type == 'select'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="select"/>
                                <div class="action-title"><span class="title">下拉选项</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="下拉选项" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="请选择" update_id="#text_val_'.$random_code.'" update_type="select_tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">1</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="选项1" style="width: 430px;">
                                            <a class=\'delOptionBtn\' href="javascript:;">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">2</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="选项2" style="width: 430px;">
                                            <a class="delOptionBtn" href="javascript:;">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <a class="addOptionBtn" href="javascript:;" random_code="'.$random_code.'" type="select">
                                            +添加选项
                                        </a>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">下拉选项</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <select class="item-input" placeholder="请选择" disabled id="text_val_'.$random_code.'"></select>
                            </div>
                        </div>';
		}else if($type == 'radiao'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="radio"/>
                                <div class="action-title"><span class="title">单选</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="单选" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">1</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="选项1" style="width: 430px;" update_id="#radio-item1_'.$random_code.'" update_type="radio">
                                            <a class="delOptionBtn" href="javascript:;" num="1" random_code="'.$random_code.'">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">2</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="选项2" style="width: 430px;" update_id="#radio-item2_'.$random_code.'" update_type="radio">
                                            <a class="delOptionBtn" href="javascript:;" num="2" random_code="'.$random_code.'">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <a class="addOptionBtn" href="javascript:;" random_code="'.$random_code.'" type="radio">
                                            +添加选项
                                        </a>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">单选</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <div class="m-radio-box" id="m-radio-box_'.$random_code.'">
                                    <div class="radio-item" id="radio-item1_'.$random_code.'">
                                        <input type="radio" class="radio_option" name="radio" value="选项1" title="选项1">
                                    </div>
                                    <div class="radio-item" id="radio-item2_'.$random_code.'">
                                        <input type="radio" class="radio_option" name="radio" value="选项2" title="选项2">
                                    </div>
                                </div>
                            </div>
                        </div>';
		}else if($type == 'checked'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="checked"/>
                                <div class="action-title"><span class="title">多选</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="多选" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">1</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="选项1" style="width: 430px;" update_id="#radio-item1_'.$random_code.'" update_type="radio">
                                            <a class="delOptionBtn" href="javascript:;" num="1" random_code="'.$random_code.'">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">2</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="选项2" style="width: 430px;" update_id="#radio-item2_'.$random_code.'" update_type="radio">
                                            <a class="delOptionBtn" href="javascript:;" num="2" random_code="'.$random_code.'">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <a class="addOptionBtn" href="javascript:;" random_code="'.$random_code.'" type="checkbox">
                                            +添加选项
                                        </a>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">多选</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <div class="m-radio-box" id="m-radio-box_'.$random_code.'">
                                    <div class="radio-item" id="radio-item1_'.$random_code.'">
                                        <input type="checkbox" class="radio_option" name="checkbox" title="选项1" value="选项1" lay-skin="primary">
                                    </div>
                                    <div class="radio-item" id="radio-item2_'.$random_code.'">
                                        <input type="checkbox" class="radio_option" name="checkbox" title="选项2" value="选项2" lay-skin="primary">
                                    </div>
                                </div>
                            </div>
                        </div>';
		}else if($type == 'area'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="area"/>
                                <div class="action-title"><span class="title">地区</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="地区" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="请选择地区" update_id="#address_'.$random_code.'" random_code="'.$random_code.'" update_type="select_area_tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <label class="label_title">填写条件：</label>
                                            <label class="radio-inline">
                                                <input type="radio" title="省份" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="province" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="省市" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="city">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="省市区" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="country">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="input-middle-item flex" id="areaParent_'.$random_code.'">
                                        <div class="input-group province_select">
                                            <div class="input-group-addon">默认值&nbsp;省</div>
                                            <select class="sel-province" name="province_id_'.$random_code.'" onChange="selectCity(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group city_select" style="display:none;">
                                            <div class="input-group-addon">默认值&nbsp;市</div>
                                            <select class="sel-city" name="city_id_'.$random_code.'" onChange="selectcounty(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group country_select" style="display:none;">
                                            <div class="input-group-addon">默认值&nbsp;区</div>
                                            <select class="sel-area" name="country_id_'.$random_code.'"  onChange="updateArea(\''.$random_code.'\')"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <script>
                                            layui.use("form", function() {
                                                var form = layui.form;
                                                form.on("switch(required_'.$random_code.')", function(data){
                                                    var update_id = data.elem.attributes["update_id"].nodeValue;
                                                    if(data.elem.checked){
                                                        $(update_id).show();
                                                    }else{
                                                        $(update_id).hide();
                                                    }
                                                });
                                            });
                                            cascdeInit("0","0","areaParent_'.$random_code.'","","","");
                                            $(".area_type_'.$random_code.'").click(function(){
                                                var type = $(this).val();
                                                var obj = $(this).parent().parent().parent().parent();
                                                if (type == "province") {
                                                    obj.find(".city_select").hide();
                                                    obj.find(".country_select").hide();
                                                } else if(type == "city") {
                                                    obj.find(".city_select").show();
                                                    obj.find(".country_select").hide();
                                                } else if(type == "country") {
                                                    obj.find(".city_select").show();
                                                    obj.find(".country_select").show();
                                                }
                                                updateArea("'.$random_code.'");
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">地区</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <select class="item-input" id="address_'.$random_code.'">
                                    <option value="" selected disabled style="display: none;">请选择地区</option>
                                </select>
                            </div>
                        </div>';
		}else if($type == 'date'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="date"/>
                                <div class="action-title"><span class="title">日期</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="日期" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="选择日期" update_id="#date_val_'.$random_code.'" random_code="'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <label style="font-size: 14px;font-weight: bold;line-height: 20px;">默认：</label>
                                            <label class="radio-inline">
                                                <input type="radio" title="不默认" name="date_type_'.$random_code.'" lay-filter="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当天" name="date_type_'.$random_code.'" lay-filter="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="same_day">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定日期" name="date_type_'.$random_code.'" lay-filter="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="appoint_date">
                                            </label>
                                            <div class="radio-inline appoint_date_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="appoint_date_'.$random_code.'" name="appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["laydate","form"], function() {
                                        var laydate = layui.laydate;
                                        var form = layui.form;
                                        //常规用法
                                        laydate.render({
                                            elem: "#appoint_date_'.$random_code.'",
                                            done:function(value, date, endDate){
                                                $("#date_val_'.$random_code.'").val(value);
                                            }
                                        });

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });

                                        form.on("radio(date_type_'.$random_code.')", function (data) {
                                            var obj = $(this).parent().parent();
                                    　　    if(data.value == "no_default"){
                                                $("#date_val_'.$random_code.'").val("");
                                                obj.find(".appoint_date_txt_'.$random_code.'").hide();
                                            }else if(data.value == "same_day"){
                                                $("#date_val_'.$random_code.'").val("");
                                                obj.find(".appoint_date_txt_'.$random_code.'").hide();
                                            }else if(data.value == "appoint_date"){
                                                obj.find(".appoint_date_txt_'.$random_code.'").show();
                                                var appoint_date = $("#appoint_date_'.$random_code.'").val();
                                                $("#date_val_'.$random_code.'").val(appoint_date);
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">日期</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <div class="date_item">
                                    <input type="text" placeholder="选择日期"  id="date_val_'.$random_code.'" class="item-input" readonly value="">
                                    <div class="date_icon"><i class="layui-icon layui-icon-date" style="font-size: 25px;"></i></div>
                                </div>
                            </div>
                        </div>';
		}else if($type == 'date_range'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="date_range"/>
                                <div class="action-title"><span class="title">日期范围</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="日期范围" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 600px;">
                                            <div class="input-group" style="margin-bottom: 5px;">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="begin_hint_'.$random_code.'" type="text" placeholder="请输入" value="请选择开始日期" style="width: 500px;" update_id="#begin_date_val_'.$random_code.'" random_code="'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group"><label class="label_title">开始日期</label></div>
                                        <div class="input-group" style="margin-bottom: 5px;">
                                            <label class="label_title">默认：</label>
                                            <label class="radio-inline">
                                                <input type="radio" title="不默认" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当天" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="same_day">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定日期" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="appoint_date">
                                            </label>
                                            <div class="radio-inline begin_appoint_date_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="begin_appoint_date_'.$random_code.'" name="begin_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group"><label class="label_title">结束日期</label></div>
                                        <div class="input-group" style="margin-bottom: 5px;">
                                            <label  class="label_title">默认：</label>
                                            <label class="radio-inline">
                                                <input type="radio" title="不默认" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当天" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="same_day">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定日期" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="appoint_date">
                                            </label>
                                            <div class="radio-inline end_appoint_date_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="end_appoint_date_'.$random_code.'" name="end_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["laydate","form"], function() {
                                        var laydate = layui.laydate;
                                        var form = layui.form;
                                        //常规用法
                                        laydate.render({
                                            elem: "#begin_appoint_date_'.$random_code.'",
                                            done:function(value, date, endDate){
                                                $("#begin_date_val_'.$random_code.'").val(value);
                                            }
                                        });
                                        laydate.render({
                                            elem: "#end_appoint_date_'.$random_code.'",
                                            done:function(value, date, endDate){
                                                $("#end_date_val_'.$random_code.'").val(value);
                                            }
                                        });

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                    $(".begin_date_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            obj.find(".begin_appoint_date_txt_'.$random_code.'").hide();
													$("#begin_date_val_'.$random_code.'").val("");
                                        } else if(date_type == "same_day") {
                                            obj.find(".begin_appoint_date_txt_'.$random_code.'").hide();
													$("#begin_date_val_'.$random_code.'").val("");
                                        } else if(date_type == "appoint_date") {
                                            obj.find(".begin_appoint_date_txt_'.$random_code.'").show();
                                            var begin_appoint_date = $("#begin_appoint_date_'.$random_code.'").val();
													$("#begin_date_val_'.$random_code.'").val(begin_appoint_date);
                                        }
                                    });
                                    $(".end_date_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            obj.find(".end_appoint_date_txt_'.$random_code.'").hide();
													$("#end_date_val_'.$random_code.'").val("");
                                        } else if(date_type == "same_day") {
                                            obj.find(".end_appoint_date_txt_'.$random_code.'").hide();
                                            $("#end_date_val_'.$random_code.'").val("");
                                        } else if(date_type == "appoint_date") {
                                            obj.find(".end_appoint_date_txt_'.$random_code.'").show();
                                            var end_appoint_date = $("#end_appoint_date_'.$random_code.'").val();
                                            $("#end_date_val_'.$random_code.'").val(end_appoint_date);
                                        }
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                                <div class="template_item">
                                    <span class="item_title" id="text_title_'.$random_code.'">日期范围</span>
                                    <span class="item_required" id="text_required_'.$random_code.'">*</span>
                                </div>
                                <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                                <div class="tempalte_content">
                                    <div class="date_item">
                                        <input type="text" placeholder="请选择日期范围"  id="begin_date_val_'.$random_code.'" class="item-input" readonly value="">
                                        <div class="date_icon"><i class="layui-icon layui-icon-date" style="font-size: 25px;"></i></div>
                                    </div>
                                </div>
                            </div>';
		}else if($type == 'idcard'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="idcard"/>
                                <div class="action-title"><span class="title">身份证号码</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="身份证号码" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">提示语</div>
                                        <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="请填写18位身份证号码" update_id="#text_val_'.$random_code.'" update_type="tip">
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                                <div class="template_item">
                                    <span class="item_title" id="text_title_'.$random_code.'">身份证号码</span>
                                    <span class="item_required" id="text_required_'.$random_code.'">*</span>
                                </div>
                                <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                                <div class="tempalte_content">
                                    <input type="text" name="" placeholder="请填写18位身份证号码" id="text_val_'.$random_code.'" class="item-input">
                                </div>
                            </div>';
		}else if($type == 'time'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="time"/>
                                <div class="action-title"><span class="title">时间</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="时间" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="请选择日期"  update_id="#date_val_'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <label style="font-size: 14px;font-weight: bold;line-height: 20px;">默认：</label>
                                            <label class="radio-inline">
                                                <input type="radio" title="不默认" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当时" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="same_time">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定时间" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="appoint_time">
                                            </label>
                                            <div class="radio-inline appoint_time_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="appoint_time_'.$random_code.'" name="appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["laydate","form"], function() {
                                        var laydate = layui.laydate;
                                        var form = layui.form;
                                        //常规用法
                                        laydate.render({
                                            elem: "#appoint_time_' . $random_code . '"
                                            ,type: "time"
                                            ,done:function(value, date, endDate){
                                                $("#date_val_' . $random_code . '").val(value);
                                            }
                                        });

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });

                                        $(".time_type_'.$random_code.'").click(function(){
                                            var date_type = $(this).val();
                                            var obj = $(this).parent().parent();
                                            if (date_type == "no_default") {
                                                obj.find(".appoint_time_txt_'.$random_code.'").hide();
                                                $("#date_val_'.$random_code.'").val("");
                                            } else if(date_type == "same_time") {
                                                obj.find(".appoint_time_txt_'.$random_code.'").hide();
                                                $("#date_val_'.$random_code.'").val("");
                                            } else if(date_type == "appoint_time") {
                                                obj.find(".appoint_time_txt_'.$random_code.'").show();
														var appoint_time = $("#appoint_time_'.$random_code.'").val();
                                                $("#date_val_'.$random_code.'").val(appoint_time);
                                            }
                                        });
                                    });

                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">时间</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <div class="date_item">
                                    <input type="text" placeholder="请选择时间"  id="date_val_'.$random_code.'" class="item-input" readonly>
                                    <div class="date_icon"><i class="layui-icon layui-icon-time" style="font-size: 25px;"></i></div>
                                </div>
                            </div>
                        </div>';
		}else if($type == 'time_range'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="time_range"/>
                                <div class="action-title"><span class="title">时间范围</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="时间范围" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 600px;">
                                            <div class="input-group" style="margin-bottom: 5px;">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="begin_hint_'.$random_code.'" type="text" placeholder="请输入" value="选择开始时间" style="width: 500px;" update_id="#begin_time_val_'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group"><label class="label_title">开始时间</label></div>
                                        <div class="input-group" style="margin-bottom: 5px;">
                                            <label class="label_title">默认：</label>
                                            <label class="radio-inline">
                                                <input type="radio" title="不默认" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当时" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="same_time">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定时间" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="appoint_time">
                                            </label>
                                            <div class="radio-inline begin_appoint_time_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="begin_appoint_time_'.$random_code.'" name="begin_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group"><label class="label_title">结束时间</label></div>
                                        <div class="input-group" style="margin-bottom: 5px;">
                                            <label  class="label_title">默认：</label>
                                            <label class="radio-inline">
                                                <input type="radio" title="不默认" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当天" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="same_time">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定日期" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="appoint_time">
                                            </label>
                                            <div class="radio-inline end_appoint_time_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="end_appoint_time_'.$random_code.'" name="end_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["laydate","form"], function() {
                                        var laydate = layui.laydate;
                                        var form = layui.form;
                                        //常规用法
                                        laydate.render({
                                            elem: "#begin_appoint_time_'.$random_code.'"
                                            ,type: "time"
                                            ,done:function(value, date, endDate){
                                                $("#begin_time_val_' . $random_code . '").val(value);
                                            }
                                        });
                                        laydate.render({
                                            elem: "#end_appoint_time_'.$random_code.'"
                                            ,type: "time"
                                            ,done:function(value, date, endDate){
                                                $("#end_time_val_' . $random_code . '").val(value);
                                            }
                                        });

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                    $(".begin_time_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            obj.find(".begin_appoint_time_txt_'.$random_code.'").hide();
                                            $("#begin_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "same_time") {
                                            obj.find(".begin_appoint_time_txt_'.$random_code.'").hide();
                                            $("#begin_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "appoint_time") {
                                            var appoint_time = $("#begin_appoint_time_'.$random_code.'").val();
                                            obj.find(".begin_appoint_time_txt_'.$random_code.'").show();
                                            $("#begin_time_val_'.$random_code.'").val(appoint_time);
                                        }
                                    });
                                    $(".end_time_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            obj.find(".end_appoint_time_txt_'.$random_code.'").hide();
                                            $("#end_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "same_time") {
                                            obj.find(".end_appoint_time_txt_'.$random_code.'").hide();
                                            $("#end_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "appoint_time") {
                                            obj.find(".end_appoint_time_txt_'.$random_code.'").show();
                                            var appoint_time = $("#end_appoint_time_'.$random_code.'").val();
                                            $("#end_time_val_'.$random_code.'").val(appoint_time);
                                        }
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">时间范围</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <div class="date_item">
                                    <input type="text" placeholder="请选择时间范围"  id="begin_time_val_'.$random_code.'" class="item-input" readonly>
                                    <div class="date_icon"><i class="layui-icon layui-icon-time" style="font-size: 25px;"></i></div>
                                </div>
                            </div>
                        </div>';
		}else if($type == 'telephone'){
			$item_li = '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="telephone"/>
                                <div class="action-title"><span class="title">手机号码</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="手机号码"  update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="" update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">提示语</div>
                                        <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="请填写11位手机号码" update_id="#text_val_'.$random_code.'" update_type="tip">
                                    </div>
                                </div>
                                <div class="input-required">
                                    <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["form"], function() {
                                        var form = layui.form;

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
			$phone_item = '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">手机号码</span>
                                <span class="item_required" id="text_required_'.$random_code.'">*</span>
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'"></div>
                            <div class="tempalte_content">
                                <input type="text" placeholder="请填写11位手机号码" id="text_val_'.$random_code.'" class="item-input">
                            </div>
                        </div>';
		}
		$need_data['item_li'] = $item_li;
		$need_data['phone_item'] = $phone_item;
		$need_data['random_code'] = $random_code;
		return $need_data;
	}

	/**
	 * @desc 获取万能表单信息
	 * @param $item
	 * @return array
	 */
	public function setFormsContent($item){
		$form_content = unserialize( $item['form_content'] );
		$item_li = "";
		$phone_item = "";
		if(!empty($form_content) && count($form_content) > 0){
			foreach($form_content as $k=>$v){
				$random_code = random(10,false);
				$required = $v['required'];
				$type = $v['type'];
				if($type == 'image'){//图片
					$title = $v['title'];
					$remark = $v['remark'];
					$max_count = $v['max_count'];
					$item_li = $item_li. '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                                    <div class="action-items">
                                        <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                        <input type="hidden" name="form_type_'.$random_code.'" value="image"/>
                                        <div class="action-title"><span class="title">图片</span></div>
                                        <div class="input-title">
                                            <div class="input-group">
                                                <div class="input-group-addon">标题</div>
                                                <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'"  update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">最大上传数量</div>
                                                <input class="form-control" name="max_count_'.$random_code.'" type="text" placeholder="请输入最大上传数量" value="'.$max_count.'">
                                                <div class="input-group-addon">张</div>
                                            </div>
                                        </div>
                                        <div class="input-required">';
					if($required == 1){
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li . '</div>
                                    <div class="input-delete">
                                        <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                            <i class="layui-icon">&#xe640;</i>删除
                                        </a>
                                    </div>
                                    <script>
                                        layui.use("form", function() {
                                            var form = layui.form;
                                            form.on("switch(required_'.$random_code.')", function(data){
                                                var update_id = data.elem.attributes["update_id"].nodeValue;
                                                if(data.elem.checked){
                                                    $(update_id).show();
                                                }else{
                                                    $(update_id).hide();
                                                }
                                            });
                                        });
                                    </script>
                                </div>
                            </li>';
					$phone_item = $phone_item . '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                                <div class="template_item">
                                    <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item . '<span class="item_required" id="text_required_'.$random_code.'" >*</span>';
					}else{
						$phone_item = $phone_item . '<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item . '
                                        </div>
                                        <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                                        <div class="tempalte_content">
                                            <div class="image-add">
                                                <div class="add">
                                                    <span class="iconfont-m- icon-m-jiahao">+</span>
                                                </div>
                                                <div class="add-text"> 添加图片 </div>
                                                <input type="file" style="display: none;">
                                            </div>
                                        </div>
                                    </div>';
				}else if($type == 'text'){//单行文本
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$item_li = $item_li . '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                                    <div class="action-items">
                                        <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                        <input type="hidden" name="form_type_'.$random_code.'" value="text"/>
                                        <div class="action-title"><span class="title">单行文本</span></div>
                                        <div class="input-title">
                                            <div class="input-group">
                                                <div class="input-group-addon">标题</div>
                                                <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'" update_id="#text_val_'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                        <div class="input-required">';
					if($required == 1){
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li . '</div>
                                        <div class="input-delete">
                                            <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                                <i class="layui-icon">&#xe640;</i>删除
                                            </a>
                                        </div>
                                        <script>
                                            layui.use("form", function() {
                                                var form = layui.form;
                                                form.on("switch(required_'.$random_code.')", function(data){
                                                    var update_id = data.elem.attributes["update_id"].nodeValue;
                                                    if(data.elem.checked){
                                                        $(update_id).show();
                                                    }else{
                                                        $(update_id).hide();
                                                    }
                                                });
                                            });
                                        </script>
                                    </div>
                                </li>';
					$phone_item = $phone_item. '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1) {
						$phone_item = $phone_item . '<span class="item_required" id = "text_required_'.$random_code.'" >*</span >';
					}else{
						$phone_item = $phone_item . '<span class="item_required" id = "text_required_'.$random_code.'" style="display:none;">*</span >';
					}
					$phone_item = $phone_item . '</div>
                                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                                            <div class="tempalte_content">
                                                <input type="text" placeholder="'.$hint.'" id="text_val_'.$random_code.'" class="item-input">
                                            </div>
                                        </div>';
				}else if($type == 'textarea'){//多行文本
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$item_li = $item_li . '<li lay-filter="action_li_'.$random_code.'">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="textarea"/>
                                <div class="action-title"><span class="title">多行文本</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'"  update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">提示语</div>
                                        <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'" update_id="#text_val_'.$random_code.'" update_type="tip">
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li . '</div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item.'<div class="phone-item" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item.'</div>
                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                            <div class="tempalte_content">
                                <textarea type="text" placeholder="'.$hint.'" id="text_val_'.$random_code.'" class="item-input item-input-textarea"></textarea>
                            </div>
                        </div>';
				}else if($type == 'select'){//下拉选项
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$option_val = $v['option_val'];
					$item_li = $item_li.'<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="select"/>
                                <div class="action-title"><span class="title">下拉选项</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max" style="width: 250px;">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'" update_id="#text_val_'.$random_code.'" update_type="select_tip">
                                            </div>
                                        </div>
                                    </div>';
					if(!empty($option_val) && count($option_val) > 0){
						$num = 1;
						foreach($option_val as $pk=>$pv){
							$item_li = $item_li.'<div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">'.$num.'</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="'.$pv.'" style="width: 430px;">
                                            <a class="delOptionBtn" href="javascript:;">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>';
							$num++;
						}
					}
					$item_li = $item_li.'<div class="input-middle-item">
                                        <a class="addOptionBtn" href="javascript:;" random_code="'.$random_code.'" type="select">
                                            +添加选项
                                        </a>
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li.'</div>
                                        <div class="input-delete">
                                            <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                                <i class="layui-icon">&#xe640;</i>删除
                                            </a>
                                        </div>
                                        <script>
                                            layui.use("form", function() {
                                                var form = layui.form;
                                                form.on("switch(required_'.$random_code.')", function(data){
                                                    var update_id = data.elem.attributes["update_id"].nodeValue;
                                                    if(data.elem.checked){
                                                        $(update_id).show();
                                                    }else{
                                                        $(update_id).hide();
                                                    }
                                                });
                                            });
                                        </script>
                                    </div>
                                </li>';
					$phone_item = $phone_item. '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item. '<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item. '<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item. '</div>
                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                            <div class="tempalte_content">
                                <select class="item-input" disabled id="text_val_'.$random_code.'">
                                    <option value="">'.$hint.'</option>
                                </select>
                            </div>
                        </div>';
				}else if($type == 'radio'){//单选
					$title = $v['title'];
					$remark = $v['remark'];
					$option_val = $v['option_val'];
					$item_li = $item_li.'<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="radio"/>
                                <div class="action-title"><span class="title">单选</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 550px;">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                    </div>';
					if(!empty($option_val) && count($option_val) > 0){
						$num = 1;
						foreach($option_val as $pk=>$pv){
							$item_li = $item_li.'<div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">'.$num.'</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="'.$pv.'" style="width: 430px;" update_id="#radio-item'.$num.'_'.$random_code.'" update_type="radio">
                                            <a class="delOptionBtn" href="javascript:;" num="'.$num.'" random_code="'.$random_code.'">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>';
							$num++;
						}
					}
					$item_li = $item_li.'<div class="input-middle-item">
                                        <a class="addOptionBtn" href="javascript:;" random_code="'.$random_code.'" type="radio">
                                            +添加选项
                                        </a>
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li.'</div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item.'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item.'</div>
                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                            <div class="tempalte_content">
                                <div class="m-radio-box" id="m-radio-box_'.$random_code.'">';
					if(!empty($option_val) && count($option_val) > 0){
						$num = 1;
						foreach($option_val as $pk=>$pv){
							$phone_item = $phone_item.'<div class="radio-item" id="radio-item'.$num.'_'.$random_code.'">
                                        <input type="radio" class="radio_option" name="radio" value="'.$pv.'" title="'.$pv.'">
                                    </div>';
							$num++;
						}
					}
					$phone_item = $phone_item.'</div>
                            </div>
                        </div>';
				}else if($type == 'checked'){//多选
					$title = $v['title'];
					$remark = $v['remark'];
					$option_val = $v['option_val'];
					$item_li = $item_li.'<li lay-filter="action_li_'.$random_code.'"  class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="checked"/>
                                <div class="action-title"><span class="title">多选</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 550px;">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                    </div>';
					if(!empty($option_val) && count($option_val) > 0){
						$num = 1;
						foreach($option_val as $pk=>$pv){
							$item_li = $item_li.'<div class="input-middle-item">
                                        <div class="input-group">
                                            <div class="input-group-addon">选项<span class="sort">'.$num.'</span></div>
                                            <input class="form-control" name="option_val_'.$random_code.'[]" type="text" placeholder="请输入" value="'.$pv.'" style="width: 430px;" update_id="#radio-item'.$num.'_'.$random_code.'" update_type="radio">
                                            <a class="delOptionBtn" href="javascript:;" num="'.$num.'" random_code="'.$random_code.'">
                                                &nbsp;删除
                                            </a>
                                        </div>
                                    </div>';
							$num++;
						}
					}
					$item_li = $item_li.'<div class="input-middle-item">
                                        <a class="addOptionBtn" href="javascript:;" random_code="'.$random_code.'" type="checkbox">
                                            +添加选项
                                        </a>
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li.'</div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item.'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item.'</div>
                                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                                            <div class="tempalte_content">
                                                <div class="m-radio-box" id="m-radio-box_'.$random_code.'">';
					if(!empty($option_val) && count($option_val) > 0){
						$num = 1;
						foreach($option_val as $pk=>$pv){
							$phone_item = $phone_item.'<div class="radio-item" id="radio-item'.$num.'_'.$random_code.'">
                                                    <input type="checkbox" class="radio_option" name="checkbox" title="'.$pv.'" value="'.$pv.'" lay-skin="primary">
                                                </div>';
							$num++;
						}
					}
					$phone_item = $phone_item.'</div>
                                    </div>
                                </div>';
				}else if($type == 'area'){//地区
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$area_type = $v['area_type'];
					$province_id = $v['province_id'];
					$city_id = $v['city_id'];
					$country_id = $v['country_id'];
					$address = "";
					if($province_id != '请选择省份'){
						$address = $address . $province_id;
					}
					if($city_id != '请选择城市'){
						$address = $address . $city_id;
					}
					if($country_id != '请选择区域'){
						$address = $address . $country_id;
					}

					$item_li = $item_li.'<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="area"/>
                                <div class="action-title"><span class="title">地区</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content" style="width:560px">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'" update_id="#address_'.$random_code.'" random_code="'.$random_code.'" update_type="select_area_tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <label class="label_title">填写条件：</label>';
					if($area_type == 'province'){
						$item_li = $item_li.'<label class="radio-inline">
                                        <input type="radio" title="省份" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="province" checked="checked">
                                      </label>
                                      <label class="radio-inline">
                                        <input type="radio" title="省市" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="city">
                                      </label>
                                      <label class="radio-inline">
                                        <input type="radio" title="省市区" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="country">
                                      </label>
                                      </div>
                                    </div>
                                    <div class="input-middle-item flex" id="areaParent_'.$random_code.'">
                                        <div class="input-group province_select">
                                            <div class="input-group-addon">默认值&nbsp;省</div>
                                            <select class="sel-province" name="province_id_'.$random_code.'" onChange="selectCity(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group city_select" style="display:none;">
                                            <div class="input-group-addon">默认值&nbsp;市</div>
                                            <select class="sel-city" name="city_id_'.$random_code.'" onChange="selectArea(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group country_select" style="display:none;">
                                            <div class="input-group-addon">默认值&nbsp;区</div>
                                            <select class="sel-area" name="country_id_'.$random_code.'"  onChange="updateArea(\'' . $random_code . '\')"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>';
					}else if($area_type == 'city'){
						$item_li = $item_li.' <label class="radio-inline">
                                            <input type="radio" title="省份" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="province">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="省市" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="city" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="省市区" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="country">
                                        </label>
                                        </div>
                                    </div>
                                    <div class="input-middle-item flex" id="areaParent_'.$random_code.'">
                                        <div class="input-group province_select">
                                            <div class="input-group-addon">默认值&nbsp;省</div>
                                            <select class="sel-province" name="province_id_'.$random_code.'" onChange="selectCity(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group city_select">
                                            <div class="input-group-addon">默认值&nbsp;市</div>
                                            <select class="sel-city" name="city_id_'.$random_code.'" onChange="selectArea(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group country_select" style="display:none;">
                                            <div class="input-group-addon">默认值&nbsp;区</div>
                                            <select class="sel-area" name="country_id_'.$random_code.'"  onChange="updateArea(\'' . $random_code . '\')"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>';
					}else if($area_type == 'country'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="省份" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="province">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="省市" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="city">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="省市区" name="area_type_'.$random_code.'" class="area_type_'.$random_code.'" value="country" checked="checked">
                                        </label>
                                        </div>
                                    </div>
                                    <div class="input-middle-item flex" id="areaParent_'.$random_code.'">
                                        <div class="input-group province_select">
                                            <div class="input-group-addon">默认值&nbsp;省</div>
                                            <select class="sel-province" name="province_id_'.$random_code.'" onChange="selectCity(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group city_select">
                                            <div class="input-group-addon">默认值&nbsp;市</div>
                                            <select class="sel-city" name="city_id_'.$random_code.'" onChange="selectcounty(\'areaParent_'.$random_code.'\');updateArea(\''.$random_code.'\');"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>
                                        <div class="input-group country_select">
                                            <div class="input-group-addon">默认值&nbsp;区</div>
                                            <select class="sel-area" name="country_id_'.$random_code.'"  onChange="updateArea(\'' . $random_code . '\')"  style="width: 130px;display:inline;">
                                                <option value="" selected="true">请选择</option>
                                            </select>
                                        </div>';
					}
					$item_li = $item_li.'<script>
                                            layui.use("form", function() {
                                                var form = layui.form;
                                                form.on("switch(required_'.$random_code.')", function(data){
                                                    var update_id = data.elem.attributes["update_id"].nodeValue;
                                                    if(data.elem.checked){
                                                        $(update_id).show();
                                                    }else{
                                                        $(update_id).hide();
                                                    }
                                                });
                                                cascdeInit(0,0,"areaParent_'.$random_code.'","'.$province_id.'","'.$city_id.'","'.$country_id.'");
                                                $(".area_type_'.$random_code.'").click(function(){
                                                    var type = $(this).val();
                                                    var obj = $(this).parent().parent().parent().parent();
                                                    if (type == "province") {
                                                        obj.find(".city_select").hide();
                                                        obj.find(".country_select").hide();
                                                    } else if(type == "city") {
                                                        obj.find(".city_select").show();
                                                        obj.find(".country_select").hide();
                                                    } else if(type == "country") {
                                                        obj.find(".city_select").show();
                                                        obj.find(".country_select").show();
                                                    }
                                                    updateArea("'.$random_code.'");
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li.'</div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                            </div>
                        </li>';
					$phone_item = $phone_item. '<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item. '
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                            <div class="tempalte_content">
                                <select class="item-input" id="address_'.$random_code.'" placeholder="'.$hint.'">
                                    <option value="">'.$address.'</option>
                                </select>
                            </div>
                        </div>';
				}else if($type == 'date'){//日期
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$date_type = $v['date_type'];
					$appoint_date = $v['appoint_date'];
					$item_li = $item_li.'<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                                        <div class="action-items">
                                            <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                            <input type="hidden" name="form_type_'.$random_code.'" value="date"/>
                                            <div class="action-title"><span class="title">日期</span></div>
                                            <div class="input-title">
                                                <div class="input-group">
                                                    <div class="input-group-addon">标题</div>
                                                    <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                                </div>
                                            </div>
                                            <div class="middle-content" style="width: 570px;">
                                                <div class="input-middle-item flex">
                                                    <div class="input-remark">
                                                        <div class="input-group">
                                                            <div class="input-group-addon">说明</div>
                                                            <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                                        </div>
                                                    </div>
                                                    <div class="input-max">
                                                        <div class="input-group" >
                                                            <div class="input-group-addon">提示语</div>
                                                            <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'" update_id="#date_val_'.$random_code.'" random_code="'.$random_code.'" update_type="tip">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-middle-item">
                                                    <div class="input-group">
                                                        <label style="font-size: 14px;font-weight: bold;line-height: 20px;">默认：</label>';
					if($date_type == 'no_default'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="no_default" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="same_day">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="appoint_date">
                                        </label>
                                        <div class="radio-inline appoint_date_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="appoint_date_'.$random_code.'" name="appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}else if($date_type == 'same_day'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="same_day" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="appoint_date">
                                        </label>
                                        <div class="radio-inline appoint_date_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="appoint_date_'.$random_code.'" name="appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}else if($date_type == 'appoint_date'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="same_day">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="date_type_'.$random_code.'" class="date_type_'.$random_code.'" value="appoint_date" checked="checked">
                                        </label>
                                        <div class="radio-inline appoint_date_txt_'.$random_code.'">
                                            <input type="text" class="layui-input" id="appoint_date_'.$random_code.'" name="appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}
					$item_li = $item_li.'</div>
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li.'</div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["laydate","form"], function() {
                                        var laydate = layui.laydate;
                                        var form = layui.form;
                                        //常规用法
                                        laydate.render({
                                            elem: "#appoint_date_'.$random_code.'",
                                            value: "'.$appoint_date.'",
                                            done:function(value, date, endDate){
                                                $("#date_val_'.$random_code.'").val(value);
                                            }
                                        });

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                    $(".date_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            obj.find(".appoint_date_txt_'.$random_code.'").hide();
                                            $("#date_val_'.$random_code.'").val("");
                                        } else if(date_type == "same_day") {
                                            obj.find(".appoint_date_txt_'.$random_code.'").hide();
                                            $("#date_val_'.$random_code.'").val("");
                                        } else if(date_type == "appoint_date") {
                                            obj.find(".appoint_date_txt_'.$random_code.'").show();
                                            var appoint_date = $("#appoint_date_'.$random_code.'").val();
                                            $("#date_val_'.$random_code.'").val(appoint_date);
                                        }
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item.'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$moren_date = "";
					if($date_type == 'appoint_date'){
						$moren_date = $appoint_date;
					}
					$phone_item = $phone_item.'</div>
                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                            <div class="tempalte_content">
                                <div class="date_item">
                                    <input type="text" placeholder="'.$hint.'"  id="date_val_'.$random_code.'" class="item-input" readonly value="'.$moren_date.'">
                                    <div class="date_icon"><i class="layui-icon layui-icon-date" style="font-size: 25px;"></i></div>
                                </div>
                            </div>
                        </div>';
				}else if($type == 'date_range'){
					$title = $v['title'];
					$remark = $v['remark'];
					$begin_date_type = $v['begin_date_type'];
					$begin_hint = $v['begin_hint'];
					$begin_appoint_date = $v['begin_appoint_date'];
					$end_date_type = $v['end_date_type'];
					$end_hint = $v['end_hint'];
					$end_appoint_date = $v['end_appoint_date'];
					$item_li = $item_li.'<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="date_range"/>
                                <div class="action-title"><span class="title">日期范围</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 550px;">
                                            <div class="input-group" style="margin-bottom: 5px;">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="begin_hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$begin_hint.'" style="width: 500px;" update_id="#begin_date_val_'.$random_code.'" random_code="'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group"><label class="label_title">开始日期</label></div>
                                        <div class="input-group" style="margin-bottom: 5px;">
                                            <label class="label_title">默认：</label>';
					if($begin_date_type == 'no_default'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="no_default" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="same_day">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="appoint_date">
                                        </label>
                                        <div class="radio-inline begin_appoint_date_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="begin_appoint_date_'.$random_code.'" name="begin_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}else if($begin_date_type == 'same_day'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="same_day" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="appoint_date">
                                        </label>
                                        <div class="radio-inline begin_appoint_date_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="begin_appoint_date_'.$random_code.'" name="begin_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}else if($begin_date_type == 'appoint_date'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="same_day">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="begin_date_type_'.$random_code.'" class="begin_date_type_'.$random_code.'" value="appoint_date" checked="checked">
                                        </label>
                                        <div class="radio-inline begin_appoint_date_txt_'.$random_code.'">
                                            <input type="text" class="layui-input" id="begin_appoint_date_'.$random_code.'" name="begin_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}
					$item_li = $item_li.'</div>
                                </div>
                                <div class="input-middle-item">
                                    <div class="input-group"><label class="label_title">结束日期</label></div>
                                    <div class="input-group" style="margin-bottom: 5px;">
                                        <label  class="label_title">默认：</label>';
					if($end_date_type == 'no_default'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="no_default" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="same_day">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="appoint_date">
                                        </label>
                                        <div class="radio-inline end_appoint_date_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="end_appoint_date_'.$random_code.'" name="end_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}else if($end_date_type == 'same_day'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="same_day" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="appoint_date">
                                        </label>
                                        <div class="radio-inline end_appoint_date_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="end_appoint_date_'.$random_code.'" name="end_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}else if($end_date_type == 'appoint_date'){
						$item_li = $item_li.'<label class="radio-inline">
                                            <input type="radio" title="不默认" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当天" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="same_day">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定日期" name="end_date_type_'.$random_code.'" class="end_date_type_'.$random_code.'" value="appoint_date" checked="checked">
                                        </label>
                                        <div class="radio-inline end_appoint_date_txt_'.$random_code.'">
                                            <input type="text" class="layui-input" id="end_appoint_date_'.$random_code.'" name="end_appoint_date_'.$random_code.'" placeholder="请选择指定日期">
                                        </div>';
					}
					$item_li = $item_li.'</div>
                                </div>
                            </div>
                            <div class="input-required">';
					if($required == 1){
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li.'<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li.'</div>
                            <div class="input-delete">
                                <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                    <i class="layui-icon">&#xe640;</i>删除
                                </a>
                            </div>
                            <script>
                                layui.use(["laydate","form"], function() {
                                    var laydate = layui.laydate;
                                    var form = layui.form;
                                    //常规用法
                                    laydate.render({
                                        elem: "#begin_appoint_date_'.$random_code.'",
                                        value: "'.$begin_appoint_date.'",
                                        done:function(value, date, endDate){
                                            $("#begin_date_val_'.$random_code.'").val(value);
                                        }
                                    });
                                    laydate.render({
                                        elem: "#end_appoint_date_'.$random_code.'",
                                        value: "'.$end_appoint_date.'",
                                        done:function(value, date, endDate){
                                            $("#end_date_val_'.$random_code.'").val(value);
                                        }
                                    });

                                    form.on("switch(required_'.$random_code.')", function(data){
                                        var update_id = data.elem.attributes["update_id"].nodeValue;
                                        if(data.elem.checked){
                                            $(update_id).show();
                                        }else{
                                            $(update_id).hide();
                                        }
                                    });
                                });
                                $(".begin_date_type_'.$random_code.'").click(function(){
                                    var date_type = $(this).val();
                                    var obj = $(this).parent().parent();
                                    if (date_type == "no_default") {
                                        $("#begin_date_val_'.$random_code.'").val("");
                                        obj.find(".begin_appoint_date_txt_'.$random_code.'").hide();
                                    } else if(date_type == "same_day") {
                                        $("#begin_date_val_'.$random_code.'").val("");
                                        obj.find(".begin_appoint_date_txt_'.$random_code.'").hide();
                                    } else if(date_type == "appoint_date") {
                                        obj.find(".begin_appoint_date_txt_'.$random_code.'").show();
                                        var begin_appoint_date = $("#begin_appoint_date_'.$random_code.'").val();
                                        $("#begin_date_val_'.$random_code.'").val(begin_appoint_date);
                                    }
                                });
                                $(".end_date_type_'.$random_code.'").click(function(){
                                    var date_type = $(this).val();
                                    var obj = $(this).parent().parent();
                                    if (date_type == "no_default") {
                                        obj.find(".end_appoint_date_txt_'.$random_code.'").hide();
                                        $("#end_date_val_'.$random_code.'").val("");
                                    } else if(date_type == "same_day") {
                                        obj.find(".end_appoint_date_txt_'.$random_code.'").hide();
                                        $("#end_date_val_'.$random_code.'").val("");
                                    } else if(date_type == "appoint_date") {
                                        obj.find(".end_appoint_date_txt_'.$random_code.'").show();
                                        var end_appoint_date = $("#end_appoint_date_'.$random_code.'").val();
                                        $("#end_date_val_'.$random_code.'").val(end_appoint_date);
                                    }
                                });
                            </script>
                        </div>
                    </li>';
					$phone_item = $phone_item.'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                                <div class="template_item">
                                    <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$begin_moren_date = "";
					if($begin_date_type == 'appoint_date'){
						$begin_moren_date = $begin_appoint_date;
					}
					$end_moren_date = "";
					if($end_date_type == 'appoint_date'){
						$end_moren_date = $end_appoint_date;
					}
					$phone_item = $phone_item.'
                                </div>
                                <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                                <div class="tempalte_content">
                                    <div class="date_item">
                                        <input type="text" placeholder="请选择日期范围"  id="begin_date_val_'.$random_code.'" class="item-input" readonly value="'.$begin_moren_date.'~'.$end_moren_date.'">
                                        <div class="date_icon"><i class="layui-icon layui-icon-date" style="font-size: 25px;"></i></div>
                                    </div>
                                </div>
                            </div>';
				}else if($type == 'idcard'){
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$item_li = $item_li . '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="idcard"/>
                                <div class="action-title"><span class="title">身份证号码</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">提示语</div>
                                        <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'" update_id="#text_val_'.$random_code.'" update_type="tip">
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li . '</div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;
                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item.'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                                <div class="template_item">
                                    <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item.'</div>
                                <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                                <div class="tempalte_content">
                                    <input type="text" name="" placeholder="'.$hint.'" id="text_val_'.$random_code.'" class="item-input">
                                </div>
                            </div>';
				}else if($type == 'time'){
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$time_type = $v['time_type'];
					$appoint_time = $v['appoint_time'];
					$item_li = $item_li. '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="time"/>
                                <div class="action-title"><span class="title">时间</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content" style="width: 560px;">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark">
                                            <div class="input-group">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                        </div>
                                        <div class="input-max">
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" style="width: 180px;" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'"  update_id="#date_val_'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group">
                                            <label style="font-size: 14px;font-weight: bold;line-height: 20px;">默认：</label>';
					if($time_type == 'no_default'){
						$item_li = $item_li. '<label class="radio-inline">
                                            <input type="radio" title="不默认" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="no_default" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当时" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="same_time">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定时间" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="appoint_time">
                                        </label>
                                        <div class="radio-inline appoint_time_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="appoint_time_'.$random_code.'" name="appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                        </div>';
					}else if($time_type == 'same_time'){
						$item_li = $item_li. '<label class="radio-inline">
                                            <input type="radio" title="不默认" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当时" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="same_time" checked="checked">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定时间" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="appoint_time">
                                        </label>
                                        <div class="radio-inline appoint_time_txt_'.$random_code.'" style="display:none;">
                                            <input type="text" class="layui-input" id="appoint_time_'.$random_code.'" name="appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                        </div>';
					}else if($time_type == 'appoint_time'){
						$item_li = $item_li. '<label class="radio-inline">
                                            <input type="radio" title="不默认" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="no_default">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="填表当时" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="same_time">
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" title="指定时间" name="time_type_'.$random_code.'" class="time_type_'.$random_code.'" value="appoint_time" checked="checked">
                                        </label>
                                        <div class="radio-inline appoint_time_txt_'.$random_code.'">
                                            <input type="text" class="layui-input" style="width: 180px;" id="appoint_time_'.$random_code.'" name="appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                        </div>';
					}
					$item_li = $item_li. '   </div>
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li. ' <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li. ' <input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li. '
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["laydate","form"], function() {
                                        var laydate = layui.laydate;
                                        var form = layui.form;
                                        //常规用法
                                        laydate.render({
                                            elem: "#appoint_time_' . $random_code . '"
                                            ,value: "'.$appoint_time.'"
                                            ,type: "time"
                                            ,done:function(value, date, endDate){
                                                $("#date_val_' . $random_code . '").val(value);
                                            }
                                        });

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });


                                    $(".time_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            $("#date_val_'.$random_code.'").val("");
                                            obj.find(".appoint_time_txt_'.$random_code.'").hide();
                                        } else if(date_type == "same_time") {
                                            $("#date_val_'.$random_code.'").val("");
                                            obj.find(".appoint_time_txt_'.$random_code.'").hide();
                                        } else if(date_type == "appoint_time") {
                                            obj.find(".appoint_time_txt_'.$random_code.'").show();
                                            var appoint_time = $("#appoint_time_'.$random_code.'").val();
                                            $("#date_val_'.$random_code.'").val(appoint_time);
                                        }
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item.'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$appoint_type_time = "";
					if($time_type == 'appoint_time'){
						$appoint_type_time = $appoint_time;
					}
					$phone_item = $phone_item.'</div>
                                        <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                                        <div class="tempalte_content">
                                            <div class="date_item">
                                                <input type="text" placeholder="'.$hint.'"  id="date_val_'.$random_code.'" class="item-input" readonly value="'.$appoint_type_time.'">
                                                <div class="date_icon"><i class="layui-icon layui-icon-time" style="font-size: 25px;"></i></div>
                                            </div>
                                        </div>
                                    </div>';
				}else if($type == 'time_range'){
					$title = $v['title'];
					$remark = $v['remark'];
					$begin_time_type = $v['begin_time_type'];
					$begin_hint = $v['begin_hint'];
					$begin_appoint_time = $v['begin_appoint_time'];

					$end_time_type = $v['end_time_type'];
					$end_hint = $v['end_hint'];
					$end_appoint_time = $v['end_appoint_time'];

					$item_li = $item_li . '<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="time_range"/>
                                <div class="action-title"><span class="title">时间范围</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'" update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="middle-content">
                                    <div class="input-middle-item flex">
                                        <div class="input-remark" style="width: 550px;">
                                            <div class="input-group" style="margin-bottom: 5px;">
                                                <div class="input-group-addon">说明</div>
                                                <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                            </div>
                                            <div class="input-group">
                                                <div class="input-group-addon">提示语</div>
                                                <input class="form-control" name="begin_hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$begin_hint.'" style="width: 500px;" update_id="#begin_time_val_'.$random_code.'" update_type="tip">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group"><label class="label_title">开始时间</label></div>
                                        <div class="input-group" style="margin-bottom: 5px;">
                                            <label class="label_title">默认：</label>';
					if($begin_time_type == 'no_default'){
						$item_li = $item_li . '       <label class="radio-inline">
                                                <input type="radio" title="不默认" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当时" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="same_time">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定时间" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="appoint_time">
                                            </label>
                                            <div class="radio-inline begin_appoint_time_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="begin_appoint_time_'.$random_code.'" name="begin_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>';
					}else if($begin_time_type == 'same_time'){
						$item_li = $item_li . '       <label class="radio-inline">
                                                <input type="radio" title="不默认" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="no_default">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当时" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="same_time" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定时间" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="appoint_time">
                                            </label>
                                            <div class="radio-inline begin_appoint_time_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="begin_appoint_time_'.$random_code.'" name="begin_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>';
					}else if($begin_time_type == 'appoint_time'){
						$item_li = $item_li . '       <label class="radio-inline">
                                                <input type="radio" title="不默认" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="no_default">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当时" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="same_time">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定时间" name="begin_time_type_'.$random_code.'" class="begin_time_type_'.$random_code.'" value="appoint_time" checked="checked">
                                            </label>
                                            <div class="radio-inline begin_appoint_time_txt_'.$random_code.'">
                                                <input type="text" class="layui-input" id="begin_appoint_time_'.$random_code.'" name="begin_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>';
					}
					$item_li = $item_li . '       </div>
                                    </div>
                                    <div class="input-middle-item">
                                        <div class="input-group"><label class="label_title">结束时间</label></div>
                                        <div class="input-group" style="margin-bottom: 5px;">
                                            <label  class="label_title">默认：</label>';
					if($end_time_type == 'no_default'){
						$item_li = $item_li . '   <label class="radio-inline">
                                                <input type="radio" title="不默认" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="no_default" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当天" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="same_time">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定日期" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="appoint_time">
                                            </label>
                                            <div class="radio-inline end_appoint_time_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="end_appoint_time_'.$random_code.'" name="end_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>';
					}else if($end_time_type == 'same_time'){
						$item_li = $item_li . '    <label class="radio-inline">
                                                <input type="radio" title="不默认" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="no_default">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当天" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="same_time" checked="checked">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定日期" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="appoint_time">
                                            </label>
                                            <div class="radio-inline end_appoint_time_txt_'.$random_code.'" style="display:none;">
                                                <input type="text" class="layui-input" id="end_appoint_time_'.$random_code.'" name="end_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>';
					}else if($end_time_type == 'appoint_time'){
						$item_li = $item_li . '    <label class="radio-inline">
                                                <input type="radio" title="不默认" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="no_default">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="填表当天" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="same_time">
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" title="指定日期" name="end_time_type_'.$random_code.'" class="end_time_type_'.$random_code.'" value="appoint_time" checked="checked">
                                            </label>
                                            <div class="radio-inline end_appoint_time_txt_'.$random_code.'">
                                                <input type="text" class="layui-input" id="end_appoint_time_'.$random_code.'" name="end_appoint_time_'.$random_code.'" placeholder="请选择指定时间">
                                            </div>';
					}
					$item_li = $item_li . '</div>
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li . '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li . '
                                </div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use(["laydate","form"], function() {
                                        var laydate = layui.laydate;
                                        var form = layui.form;
                                        //常规用法
                                        laydate.render({
                                            elem: "#begin_appoint_time_'.$random_code.'"
                                            ,type: "time"
                                            ,value: "'.$begin_appoint_time.'"
                                            ,done:function(value, date, endDate){
                                                $("#begin_time_val_' . $random_code . '").val(value);
                                            }
                                        });
                                        laydate.render({
                                            elem: "#end_appoint_time_'.$random_code.'"
                                            ,type: "time"
                                            ,value: "'.$begin_appoint_time.'"
                                            ,done:function(value, date, endDate){
                                                $("#end_time_val_' . $random_code . '").val(value);
                                            }
                                        });

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                    $(".begin_time_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            obj.find(".begin_appoint_time_txt_'.$random_code.'").hide();
                                            $("#begin_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "same_time") {
                                            obj.find(".begin_appoint_time_txt_'.$random_code.'").hide();
                                            $("#begin_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "appoint_time") {
                                            obj.find(".begin_appoint_time_txt_'.$random_code.'").show();
                                            var begin_appoint_time = $("#begin_appoint_time_'.$random_code.'").val();
                                            $("#begin_time_val_'.$random_code.'").val(begin_appoint_time);
                                        }
                                    });
                                    $(".end_time_type_'.$random_code.'").click(function(){
                                        var date_type = $(this).val();
                                        var obj = $(this).parent().parent();
                                        if (date_type == "no_default") {
                                            obj.find(".end_appoint_time_txt_'.$random_code.'").hide();
                                            $("#end_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "same_time") {
                                            obj.find(".end_appoint_time_txt_'.$random_code.'").hide();
                                            $("#end_time_val_'.$random_code.'").val("");
                                        } else if(date_type == "appoint_time") {
                                            obj.find(".end_appoint_time_txt_'.$random_code.'").show();
                                            var end_appoint_time = $("#end_appoint_time_'.$random_code.'").val();
                                            $("#end_time_val_'.$random_code.'").val(end_appoint_time);
                                        }
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item .'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item .'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item .'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$begin_appoint_type_time = "";
					$end_appoint_type_time = "";
					if($begin_time_type == 'appoint_time'){
						$begin_appoint_type_time = $begin_appoint_time;
					}
					if($end_time_type == 'appoint_time'){
						$end_appoint_type_time = $end_appoint_time;
					}
					$phone_item = $phone_item .'
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                            <div class="tempalte_content">
                                <div class="date_item">
                                    <input type="text" placeholder="请选择时间范围"  id="begin_time_val_'.$random_code.'" class="item-input" readonly value="'.$begin_appoint_type_time.'~'.$end_appoint_type_time.'">
                                    <div class="date_icon"><i class="layui-icon layui-icon-time" style="font-size: 25px;"></i></div>
                                </div>
                            </div>
                        </div>';
				}else if($type == 'telephone'){
					$title = $v['title'];
					$remark = $v['remark'];
					$hint = $v['hint'];
					$item_li = $item_li.'<li lay-filter="action_li_'.$random_code.'" class="layui-form">
                            <div class="action-items">
                                <input type="hidden" name="random_code[]" value="'.$random_code.'"/>
                                <input type="hidden" name="form_type_'.$random_code.'" value="telephone"/>
                                <div class="action-title"><span class="title">手机号码</span></div>
                                <div class="input-title">
                                    <div class="input-group">
                                        <div class="input-group-addon">标题</div>
                                        <input class="form-control" name="title_'.$random_code.'" type="text" placeholder="请输入" value="'.$title.'"  update_id="#text_title_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-remark">
                                    <div class="input-group">
                                        <div class="input-group-addon">说明</div>
                                        <input class="form-control" name="remark_'.$random_code.'" type="text" placeholder="请输入说明文字" value="'.$remark.'" update_id="#text_tip_'.$random_code.'" update_type="html">
                                    </div>
                                </div>
                                <div class="input-max">
                                    <div class="input-group">
                                        <div class="input-group-addon">提示语</div>
                                        <input class="form-control" name="hint_'.$random_code.'" type="text" placeholder="请输入" value="'.$hint.'" update_id="#text_val_'.$random_code.'" update_type="tip">
                                    </div>
                                </div>
                                <div class="input-required">';
					if($required == 1){
						$item_li = $item_li. '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" checked="checked" lay-skin="switch" lay-text="必填|非必填">';
					}else{
						$item_li = $item_li. '<input type="checkbox" name="required_'.$random_code.'" lay-filter="required_'.$random_code.'" update_id="#text_required_'.$random_code.'" lay-skin="switch" lay-text="必填|非必填">';
					}
					$item_li = $item_li. '</div>
                                <div class="input-delete">
                                    <a class="layui-btn layui-btn-xs deleteBtn" href="javascript:;" random_code="'.$random_code.'">
                                        <i class="layui-icon">&#xe640;</i>删除
                                    </a>
                                </div>
                                <script>
                                    layui.use("form", function() {
                                        var form = layui.form;

                                        form.on("switch(required_'.$random_code.')", function(data){
                                            var update_id = data.elem.attributes["update_id"].nodeValue;
                                            if(data.elem.checked){
                                                $(update_id).show();
                                            }else{
                                                $(update_id).hide();
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>';
					$phone_item = $phone_item.'<div class="phone-item layui-form" id="phone_item_'.$random_code.'" lay-filter="phone_item_'.$random_code.'">
                            <div class="template_item">
                                <span class="item_title" id="text_title_'.$random_code.'">'.$title.'</span>';
					if($required == 1){
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'">*</span>';
					}else{
						$phone_item = $phone_item.'<span class="item_required" id="text_required_'.$random_code.'" style="display:none;">*</span>';
					}
					$phone_item = $phone_item.'
                            </div>
                            <div class="template_tip" id="text_tip_'.$random_code.'">'.$remark.'</div>
                            <div class="tempalte_content">
                                <input type="text" placeholder="'.$hint.'" id="text_val_'.$random_code.'" class="item-input">
                            </div>
                        </div>';
				}
			}
		}
		$item_li = $item_li . '
                        <script>
                            layui.use("form", function() {
                                var form = layui.form;
                                form.render();
                            });
                        </script>';
		$need_data = ['item_li' => $item_li,'phone_item' => $phone_item];
		return $need_data;
	}
}
?>
