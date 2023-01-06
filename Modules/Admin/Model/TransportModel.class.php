<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.io/
 * @copyright Copyright (c) 2019-2023 Dejavu Tech.
 * @license   https://e-p.io/license
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Admin\Model;
use Think\Model;
class TransportModel extends Model{

	public function getExtendInfo($condition){
	    return M('transport_extend')->where($condition)->select();
	}

	public function delTansport($condition){
	    try {
            $this->startTrans();
            $delete = M('transport')->where($condition)->delete();
            if ($delete) {
                $delete = M('transport_extend')->where(array('transport_id'=>$condition['id']))->delete();
            }
            if (!$delete) throw new Exception();
            $this->commit();
        }catch (Exception $e){
            $model->rollback();
            return false;
        }
        return true;
	}

	public function getTransportInfo($condition){
	    return M('transport')->where($condition)->find();
	}

	public function getTransportList(){

		$count=M('transport')->count();
		$Page = new \Think\Page($count,4);
		$show  = $Page->show();// 分页显示输出

		$list= M('transport')->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'list'=>$list,
			'page'=>$show
		);

	}

	public function getExtendList($condition=array(), $order='is_default'){
		return M('transport_extend')->where($condition)->order($order)->select();
	}

	public function transUpdate($data){
	    return M('transport')->save($data);
	}

	public function delExtend($transport_id){
		return M('transport_extend')->where(array('transport_id'=>$transport_id))->delete();
	}

	public function addTransport($data){
	    return M('transport')->add($data);
	}
	public function addExtend($data){
	    return M('transport_extend')->addAll($data);
	}
}
