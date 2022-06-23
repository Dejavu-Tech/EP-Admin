<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2022 Dejavu.Tech.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Controller;

class GalleryController extends CommonController {
	//显示全部产品
	public function all(){

		$count=M('goods')->where(array('status'=>1))->count();

		$Page = new \Think\Page($count,C('FRONT_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql='SELECT goods_id,image,name,price FROM '.C('DB_PREFIX').'goods WHERE status=1 order by goods_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

			foreach ($list as $k => $v) {
				$list[$k]['goods_id']=$hashids->encode($v['goods_id']);
				$list[$k]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}

		$this->title='全部产品图册-';
		$this->category='全部产品图册';
		$this->meta_keywords=C('SITE_DESCRIPTION');
	    $this->meta_description=C('SITE_NAME').'产品图册';

		$show=str_replace("/gallery/all/p/","/gallerys/", $show);

		$this->assign('empty','没有数据');// 赋值数据集
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出

		$this->display();
	}
	//按分类显示产品
	public function category(){

		$id=get_url_id('id');

		$sql='SELECT p.goods_id,p.image,p.name,p.price FROM '.C('DB_PREFIX').'goods p,'.
		C('DB_PREFIX').'goods_to_category ptc '.
		' WHERE p.goods_id=ptc.goods_id AND p.status=1 AND ptc.category_id='.$id;

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('FRONT_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by p.goods_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);
		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		foreach ($list as $k => $v) {
				$list[$k]['goods_id']=$hashids->encode($v['goods_id']);
				$list[$k]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}

		$category=M('goods_category')->find($id);

		$this->title=$category['name'].'-';
		$this->category=$category['name'];
		$this->meta_keywords=$category['meta_keyword'];
	    $this->meta_description=$category['meta_description'];

		$show=str_replace("/gallery/category/id/","/gcategory/", $show);

		$this->assign('empty','没有数据');// 赋值数据集
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出

		$this->display('all');
	}
	//产品详情
    public function pshow(){

    	$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		$id=get_url_id('id');

		$sql='SELECT p.goods_id,p.model,pi.image,pd.summary,p.name,pd.meta_description,pd.meta_keyword FROM '.
				C('DB_PREFIX').'goods p,'.
				C('DB_PREFIX').'goods_image pi,'.
				C('DB_PREFIX').'goods_description pd where p.goods_id=pd.goods_id and p.goods_id=pi.goods_id and p.status=1 and p.goods_id='.$id;

		$list=M()->query($sql);

		if(isset($list)){

			foreach ($list as $k => $v) {
				$list[$k]['thumb']=resize($v['image'], C('gallery_thumb_width'), C('gallery_thumb_height'));
			}

			$this->goods_image=$list;

			$goods=array(
				'name'=>$list[0]['name'],
				'model'=>$list[0]['model'],
				'summary'=>$list[0]['summary'],
			);

			$this->goods=$goods;

			$other=M()->query('select goods_id,image from '.C('DB_PREFIX').'goods order by rand() limit 6');

			foreach ($other as $k => $v) {
				$other[$k]['goods_id']=$hashids->encode($v['goods_id']);
				$other[$k]['image']=resize($v['image'], C('gallery_related_thumb_width'), C('gallery_related_thumb_height'));
			}

			$this->other_goods=$other;

			$this->title=$list[0]['name'].'-';
			$this->meta_keywords=$list[0]['meta_keyword'];
	        $this->meta_description=$list[0]['meta_description'];

	        $this->display();
		}
    }
}
