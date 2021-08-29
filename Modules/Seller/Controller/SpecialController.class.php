<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      http://www.ch871.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   http://www.ch871.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Controller;
use Admin\Model\SpecialModel;
class SpecialController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='营销活动';
		$this->breadcrumb2='专题管理';
	}

	public function index(){
		$model=new SpecialModel();

		$data=$model->show_special_page();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

		$this->display();
	}

	/**
     * 专题项目添加
     */
    public function special_item_add() {
        $model_mb_special = new SpecialModel();

        $param = array();
        $param['special_id'] = $_POST['special_id'];
        $param['item_type'] = $_POST['item_type'];

        //广告只能添加一个
        if($param['item_type'] == 'adv_list') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '广告条板块只能添加一个'));die;
            }
        }

        $item_info = $model_mb_special->addMbSpecialItem($param);
        if($item_info) {
            echo json_encode($item_info);die;
        } else {
            echo json_encode(array('error' => '添加失败'));die;
        }
    }
	public function special_item_edit() {
        $model_mb_special = new SpecialModel();
        $item_info = $model_mb_special->getMbSpecialItemInfoByID($_GET['item_id']);
        $this->item_info = $item_info;
		$this->display('mb_special_item_edit');
    }

	/**
     * 专题项目保存
     */
    public function special_item_save() {
        $model_mb_special = new SpecialModel();

        $result = $model_mb_special->editMbSpecialItemByID(array('item_data' => $_POST['item_data']), $_POST['item_id'], $_POST['special_id']);

        $return = array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('Special/addGoods',array('special_id' => $_POST['special_id']))
				);

		$this->osc_alert($return);
    }

	/**
     * 更新项目排序
     */
    public function update_item_sort() {
        $item_id_string = $_POST['item_id_string'];
        $special_id = $_POST['special_id'];
        if(!empty($item_id_string)) {
            $model_mb_special = new SpecialModel();
            $item_id_array = explode(',', $item_id_string);
            $index = 0;
            foreach ($item_id_array as $item_id) {
                $result = $model_mb_special->editMbSpecialItemByID(array('item_sort' => $index), $item_id, $special_id);
                $index++;
            }
        }
        $data = array();
        $data['message'] = '操作成功';
        echo json_encode($data);
		die();
    }
	 /**
     * 更新项目启用状态
     */
    public function update_item_usable() {
        $model_mb_special =  new SpecialModel();
        $result = $model_mb_special->editMbSpecialItemUsableByID($_POST['usable'], $_POST['item_id'], $_POST['special_id']);
        $data = array();
        if($result) {
            $data['message'] = '操作成功';
        } else {
            $data['error'] = '操作失败';
        }
        echo json_encode($data);
    }

	/**
     * 专题项目删除
     */
    public function special_item_del() {
        $model_mb_special = new SpecialModel();

        $condition = array();
        $condition['item_id'] = $_POST['item_id'];

        $result = $model_mb_special->delMbSpecialItem($condition, $_POST['special_id']);
        if($result) {
            echo json_encode(array('message' => '删除成功'));die;
        } else {
            echo json_encode(array('error' => '删除失败'));die;
        }
    }
	public function addGoods()
	{
		$special_id = I('get.special_id');
		$model_mb_special = new SpecialModel();
		$this->crumbs='制作专题';

		$this->special=M('mb_special')->where(array('special_id'=>$special_id ))->find();
		$special_item_list = $model_mb_special->getMbSpecialItemListByID($_GET['special_id']);
		$this->list = $special_item_list;
		$this->special_id = $special_id;
		$module_list = $model_mb_special->getMbSpecialModuleList();
		$this->module_list = $module_list;

		$this->display('modifyspecial');
	}
	function add(){

		if(IS_POST){

			$model=new SpecialModel();
			$data=I('post.');
			$return=$model->add_special($data);
			$this->osc_alert($return);
		}
		$this->action=U('Special/add');
		$this->crumbs='新增';
		$this->display('edit');
	}

	function edit(){

		$model=new SpecialModel();

		if(IS_POST){

			$data=I('post.');
			$return=$model->edit_special($data);

			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('Special/edit');

		$this->special=M('mb_special')->where(array('special_id'=>I('special_id')))->find();

		$this->display('edit');
	}


	function del(){
		$model=new SpecialModel();
		M('mb_special')->where(array('special_id'=>I('get.special_id')))->delete();
		$return = array(
						'status'=>'success',
						'message'=>'删除成功',
						'jump'=>U('Special/index')
					);
		$this->osc_alert($return);
	}
}
?>
