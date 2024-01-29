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
namespace Home\Controller;

class SpecialController extends CommonController {
    protected function _initialize()
    {
    	parent::_initialize();
        $this->cur_page = 'special';
    }
    public function index(){

		//special_id
		$special_id = I('get.special_id',0);
		$special_info = M('mb_special')->where( array('mb_special' => $special_id) )->find();


		$mb_special_item_list = M('mb_special_item')->where( array('special_id' =>$special_id,'item_usable' => 1) )->order('item_sort asc')->select();


		$item_content_html = '';

		foreach($mb_special_item_list as $item)
		{
			if($item['item_type'] == 'adv_list')
			{
				$adv_list_html = $this->get_adv_list_html($item);
				$item_content_html .= $adv_list_html;
			}

			if($item['item_type'] == 'home1')
			{
				$home1_html = $this->home1($item);
				$item_content_html .= $home1_html;
			}

			if($item['item_type'] == 'home2')
			{
				$home2_html = $this->home2($item);
				$item_content_html .= $home2_html;
			}
			//home3

			if($item['item_type'] == 'home3')
			{
				$home3_html = $this->home3($item);
				$item_content_html .= $home3_html;
			}

			//home4
			if( $item['item_type'] == 'home4')
			{

				$home4_html = $this->home4($item);

				$item_content_html .= $home4_html;
			}
		}
		$this->item_content_html = $item_content_html;

       $this->special_info = $special_info;
	   $this->mb_special_item_list = $mb_special_item_list;
       $this->display();
    }

	private function home4($item)
	{
		$tmp_item_data = unserialize($item['item_data']);

		$home4_html = '';

		$home4_html .= '<div class="home4_box">';

		$home4_html .= '<div class="half_left">';

		if(!empty($tmp_item_data['rectangle1_image']))
		{
			if($tmp_item_data['rectangle1_type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$tmp_item_data['rectangle1_data']);
			} else if($tmp_item_data['rectangle1_type'] == 'url')
			{
				$tmp_url = $tmp_item_data['rectangle1_data'];
			}

			$home4_html .= '<div class="half_half_div">';
			$home4_html .= '	<a href="'.$tmp_url.'"><img src="/Uploads/image/'.$tmp_item_data['rectangle1_image'].'" /></a>';
			$home4_html .= '</div>';
		}

		if(!empty($tmp_item_data['rectangle2_image']))
		{
			if($tmp_item_data['rectangle2_type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$tmp_item_data['rectangle2_data']);
			} else if($tmp_item_data['rectangle2_type'] == 'url')
			{
				$tmp_url = $tmp_item_data['rectangle2_data'];
			}

			$home4_html .= '<div class="half_half_div">';
			$home4_html .= '	<a href="'.$tmp_url.'"><img src="/Uploads/image/'.$tmp_item_data['rectangle2_image'].'" /></a>';
			$home4_html .= '</div>';
		}
		$home4_html .= '	</div>';

		if(!empty($tmp_item_data['square_image']))
		{
			if($tmp_item_data['square_type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$tmp_item_data['square_data']);
			} else if($tmp_item_data['square_type'] == 'url')
			{
				$tmp_url = $tmp_item_data['square_data'];
			}

			$home4_html .= '<div class="half_left">';
			$home4_html .= '	<a href="'.$tmp_url.'"><img src="/Uploads/image/'.$tmp_item_data['square_image'].'" /></a>';
			$home4_html .= '</div>';
		}

		$home4_html .= '</div>';
		return $home4_html;
	}

	private function home3($item)
	{
		$tmp_item_data = unserialize($item['item_data']);

		$home3_html = '';
		$home3_html .= '<div class="home_3_box">';


		foreach($tmp_item_data['item'] as $item_data)
		{
			if($item_data['type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$item_data['data']);
			} else if($item_data['type'] == 'url')
			{
				$tmp_url = $item_data['data'];
			}

			$home3_html .= '<div class="home_3_div">';
			$home3_html .= '	<a href="'.$tmp_url.'">';
			$home3_html .= '		<img src="/Uploads/image/'.$item_data['image'].'" />';
			$home3_html .= '	</a>';
			$home3_html .= '</div>';
		}

		$home3_html .= '</div>';

		return $home3_html;
	}

	private function home2($item)
	{
		$tmp_item_data = unserialize($item['item_data']);

		$home2_html = '';

		$home2_html .= '<div class="home2_box">';
		if(!empty($tmp_item_data['square_image']))
		{
			if($tmp_item_data['square_type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$tmp_item_data['square_data']);
			} else if($tmp_item_data['square_type'] == 'url')
			{
				$tmp_url = $tmp_item_data['square_data'];
			}

			$home2_html .= '<div class="half_left">';
			$home2_html .= '	<a href="'.$tmp_url.'"><img src="/Uploads/image/'.$tmp_item_data['square_image'].'" /></a>';
			$home2_html .= '</div>';
		}

		$home2_html .= '<div class="half_right">';

		if(!empty($tmp_item_data['rectangle1_image']))
		{
			if($tmp_item_data['rectangle1_type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$tmp_item_data['rectangle1_data']);
			} else if($tmp_item_data['rectangle1_type'] == 'url')
			{
				$tmp_url = $tmp_item_data['rectangle1_data'];
			}

			$home2_html .= '<div class="half_half_div">';
			$home2_html .= '	<a href="'.$tmp_url.'"><img src="/Uploads/image/'.$tmp_item_data['rectangle1_image'].'" /></a>';
			$home2_html .= '</div>';
		}

		if(!empty($tmp_item_data['rectangle2_image']))
		{
			if($tmp_item_data['rectangle2_type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$tmp_item_data['rectangle2_data']);
			} else if($tmp_item_data['rectangle2_type'] == 'url')
			{
				$tmp_url = $tmp_item_data['rectangle2_data'];
			}

			$home2_html .= '<div class="half_half_div">';
			$home2_html .= '	<a href="'.$tmp_url.'"><img src="/Uploads/image/'.$tmp_item_data['rectangle2_image'].'" /></a>';
			$home2_html .= '</div>';
		}
		$home2_html .= '	</div>';
		$home2_html .= '</div>';

		return $home2_html;
	}


	private function home1($item)
	{
		$tmp_item_data = unserialize($item['item_data']);
		if($tmp_item_data['type'] == 'goods')
		{
			$tmp_url = U('/goods/'.$tmp_item_data['data']);
		} else if($tmp_item_data['type'] == 'url')
		{
			$tmp_url = $tmp_item_data['data'];
		}

		$home1_html = '';
		$home1_html .= '<div class="index_ad_list">';
		$home1_html .=	'<ul>';
		$home1_html .=	'	<li><a href="'.$tmp_url.'"><img src="/Uploads/image/'.$tmp_item_data['image'].'" /></a></li>';
		$home1_html .=	'</ul>';
		$home1_html .=	'</div>';
		return $home1_html;
	}
	private function get_adv_list_html($item)
	{
		$tmp_item_data = unserialize($item['item_data']);
		$adv_list_html = '';
		$adv_list_html = '<div class="tuanc" id="banner">';
		$adv_list_html .= '<div class="bd">';
		$adv_list_html .= '<ul  id="slider">';

		foreach($tmp_item_data['item'] as $adv_list_item)
		{
			if($adv_list_item['type'] == 'goods')
			{
				$tmp_url = U('/goods/'.$adv_list_item['data']);
			} else if($adv_list_item['type'] == 'url')
			{
				$tmp_url = $adv_list_item['data'];
			}

			$adv_list_html .= '<li><a href="'.$tmp_url.'"><img src="/Uploads/image/'.$adv_list_item['image'].'" /></a></li>';
		}
		$adv_list_html .= '</ul></div>';
		$adv_list_html .= '<div class="banner-focus"><div class="banner-hd"><ul>';

		$i = 1;
		foreach($tmp_item_data['item'] as $adv_list_item)
		{
			if($i == 1){
				$adv_list_html .= '<li class="on"></li>';
			} else {
				$adv_list_html .= '<li></li>';
			}
			$i++;
		}
		$adv_list_html .= '</ul></div></div></div>';
		return $adv_list_html;
	}

}
