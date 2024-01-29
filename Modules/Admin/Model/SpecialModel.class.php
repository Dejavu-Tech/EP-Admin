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
namespace Admin\Model;
use Think\Model;
class SpecialModel extends Model{


	protected  $tableName ='mb_special';
	public function show_special_page($search = array()){

		$sql='SELECT * FROM '.C('DB_PREFIX').'mb_special ';



		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by special_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);


		foreach ($list as $key => $value) {
			$value['image']=resize($value['special_bgimage'], 100, 100);

			$list[$key]= $value;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}
	/**
     * 编辑专题项目启用状态
     * @param string usable-启用/unsable-不启用
     * @param int $item_id
     * @param int $special_id
     *
     */
    public function editMbSpecialItemUsableByID($usable, $item_id, $special_id) {
        $update = array();
        if($usable == 'usable') {
            $update['item_usable'] = 1;
        } else {
            $update['item_usable'] = 0;
        }
        return $this->editMbSpecialItemByID($update, $item_id, $special_id);
    }
	/**
     * 编辑专题项目
     * @param array $update
     * @param int $item_id
     * @param int $special_id
     * @return bool
     *
     */
    public function editMbSpecialItemByID($update, $item_id, $special_id) {
        if(isset($update['item_data'])) {
            $update['item_data'] = serialize($update['item_data']);
        }
        $condition = array();
        $condition['item_id'] = $item_id;

        return M('mb_special_item')->where($condition)->save($update);
    }

	/**
     * 获取项目详细信息
     * @param int $item_id
     *
     */
    public function getMbSpecialItemInfoByID($item_id) {
        $item_id = intval($item_id);
        if($item_id <= 0) {
            return false;
        }

        $condition = array();
        $condition['item_id'] = $item_id;
        $item_info = M('mb_special_item')->where($condition)->find();
        $item_info['item_data'] = $this->_initMbSpecialItemData($item_info['item_data'], $item_info['item_type']);

        return $item_info;
    }

    /**
     * 整理项目内容
     *
     */
    private function _initMbSpecialItemData($item_data, $item_type) {
        if(!empty($item_data)) {
            $item_data = unserialize($item_data);
            if($item_type == 'goods') {
                $item_data = $this->_initMbSpecialItemGoodsData($item_data, $item_type);
            }
        } else {
            $item_data = $this->_initMbSpecialItemNullData($item_type);
        }
        return $item_data;

    }

    /**
     * 处理goods类型内容
     */
    private function _initMbSpecialItemGoodsData($item_data, $item_type) {
        $goods_id_string = '';
        if(!empty($item_data['item'])) {
            foreach ($item_data['item'] as $value) {
                $goods_id_string .= $value . ',';
            }
            $goods_id_string = rtrim($goods_id_string, ',');

            //查询商品信息
            $condition['goods_id'] = array('in', $goods_id_string);
            $model_goods = M('goods');
            $goods_list = $model_goods->where($condition)->select();
            $goods_list = array_under_reset($goods_list, 'goods_id');

            //整理商品数据
            $new_goods_list = array();
            foreach ($item_data['item'] as $value) {
                if(!empty($goods_list[$value])) {
                    $new_goods_list[] = $goods_list[$value];
                }
            }
            $item_data['item'] = $new_goods_list;
        }
        return $item_data;
    }

    /**
     * 初始化空项目内容
     */
    private function _initMbSpecialItemNullData($item_type) {
        $item_data = array();
        switch ($item_type) {
        case 'home1':
            $item_data = array(
                'title' => '',
                'image' => '',
                'type' => '',
                'data' => '',
            );
            break;
        case 'home2':
        case 'home4':
            $item_data= array(
                'title' => '',
                'square_image' => '',
                'square_type' => '',
                'square_data' => '',
                'rectangle1_image' => '',
                'rectangle1_type' => '',
                'rectangle1_data' => '',
                'rectangle2_image' => '',
                'rectangle2_type' => '',
                'rectangle2_data' => '',
            );
            break;
        default:
        }
        return $item_data;
    }

	/*
	 * 删除
	 * @param array $condition
	 * @return bool
     *
	 */
    public function delMbSpecialItem($condition, $special_id) {

        return M('mb_special_item')->where($condition)->delete();
    }
	/**
     * 获取专题模块类型列表
     * @return array
     *
     */
    public function getMbSpecialModuleList() {
        $module_list = array();
        $module_list['adv_list'] = array('name' => 'adv_list' , 'desc' => '广告条版块');
        $module_list['home1'] = array('name' => 'home1' , 'desc' => '模型版块布局A');
        $module_list['home2'] = array('name' => 'home2' , 'desc' => '模型版块布局B');
        $module_list['home3'] = array('name' => 'home3' , 'desc' => '模型版块布局C');
        $module_list['home4'] = array('name' => 'home4' , 'desc' => '模型版块布局D');
        $module_list['goods'] = array('name' => 'goods' , 'desc' => '商品版块');
        return $module_list;
    }
	public function addMbSpecialItem($param) {
        $param['item_usable'] = 0;
        $param['item_sort'] = 255;
        $result = M('mb_special_item')->add($param);
        //删除缓存
        if($result) {
            $param['item_id'] = $result;
            return $param;
        } else {
            return false;
        }
    }

	public function isMbSpecialItemExist($condition) {
        $item_list = M('mb_special_item')->where($condition)->select();
        if($item_list) {
            return true;
        } else {
            return false;
        }
    }

	/**
     * 专题项目列表（用于后台编辑显示所有项目）
	 * @param int $special_id
     *
     */
    public function getMbSpecialItemListByID($special_id) {
        $condition = array();
        $condition['special_id'] = $special_id;

        return $this->_getMbSpecialItemList($condition);
    }

	/**
     * 查询专题项目列表
     */
    private function _getMbSpecialItemList($condition, $order = 'item_sort asc') {
        $item_list = M('mb_special_item')->where($condition)->order($order)->select();
        foreach ($item_list as $key => $value) {
            $item_list[$key]['item_data'] = $this->_initMbSpecialItemData($value['item_data'], $value['item_type']);
            if($value['item_usable'] == 1) {
                $item_list[$key]['usable_class'] = 'usable';
                $item_list[$key]['usable_text'] = '禁用';
            } else {
                $item_list[$key]['usable_class'] = 'unusable';
                $item_list[$key]['usable_text'] = '启用';
            }
        }
        return $item_list;
    }
	public function edit_special($data){


			$special_id=$data['special_id'];

			$special = array();
			$special['special_id']=$special_id;
			$special['special_desc'] = $data['special_desc'];
			$special['share_title'] = $data['share_title'];
			$special['share_descript'] = $data['share_descript'];
			$special['share_image'] = $data['share_image'];
			$special['special_bgcolor'] = $data['special_bgcolor'];
			$special['special_bgimage'] = $data['image'];

			$r=M('mb_special')->save($special);

			if($r){


					return array(
					'status'=>'success',
					'message'=>'修改成功',
					'jump'=>U('Special/index')
					);


			}else{
				return array(
				'status'=>'success',
				'message'=>'修改失败',
				'jump'=>U('Special/index')
				);
			}

	}

	 function add_special($data){

			if(empty($data['special_desc']))
			{
				return array(
					'status'=>'fail',
					'message'=>'新增失败,专题名称未填写！',
					'jump'=>U('Special/index')
					);
			} else {

				$special =  array();
				$special['special_desc'] = $data['special_desc'];
				$special['special_bgcolor'] = $data['special_bgcolor'];
				$special['share_title'] = $data['share_title'];
				$special['share_descript'] = $data['share_descript'];
				$special['share_image'] = $data['share_image'];

				$special['special_bgimage'] = $data['image'];

				$special_id=M('mb_special')->add($special);

				if($special_id){
					return array(
						'status'=>'success',
						'message'=>'新增成功',
						'jump'=>U('Special/index')
					);
				}else{
					return array(
					'status'=>'fail',
					'message'=>'新增失败',
					'jump'=>U('Special/index')
					);
				}

			}






	}
}
?>
