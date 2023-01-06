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
namespace Seller\Controller;

class UpgradeController extends \Think\Controller {

    function __construct()
    {
       parent::__construct();



		//两个号都有的人
        $all_list = array(
						);
		//多商户更新内容
        $banben_list = array(
						);
		//小程序更新内容
        $weprog_banben_list = array(
						);

		$this->domain_weprog_list = $domain_weprog_list;
		$this->weprog_banben_list = $weprog_banben_list;

        $this->domain_list = $domain_list;
        $this->banben_list = $banben_list;

		$this->all_list = $all_list;
        $this->domain_all_list = $domain_all_list;
    }
    /**
		获取不同类型的域名
	**/
	public function get_type_domain( $type  )
	{
		$domain_list = array();
		switch( $type )
		{
			case 'all':
				$domain_list = $this->domain_all_list;
			break;
			case 'mall':
				$domain_list = $this->domain_list;
			break;
			case 'weprog':
				$domain_list = $this->domain_weprog_list;
			break;
		}
		return $domain_list;
	}
	/**
		获取不同类型的版本
	**/
	public function get_type_banben( $type  )
	{
		$banben_list = array();
		switch( $type )
		{
			case 'all':
				$banben_list = $this->all_list;
			break;
			case 'mall':
				$banben_list = $this->banben_list;
			break;
			case 'weprog':
				$banben_list = $this->weprog_banben_list;
			break;
		}
		return $banben_list;
	}

    public function down_version_file()
    {
        $version = trim(I('get.version'));
		$type = trim(I('get.type','weprog'));
        $host = base64_decode(I('get.host'));

		$domain_list = $this->get_type_domain( $type  );



        if(!in_array($host,$domain_list))
        {
            $data = array();
            $data['domain'] = $host;
            $data['add_time'] = time();
            M('bad_domain')->add($data);
            die('-');
        }

		$banben = $this->get_type_banben( $type  );


        header("Content-type:text/html;charset=utf-8");
        $file_name=  $banben['name'];
        $file_name=iconv("utf-8","gb2312",$file_name);
        $file_size=filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file_name);
        $buffer=1024;
        $file_count=0;
        //向浏览器返回数据
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);

    }

    public function req_version()
    {
        $version = trim(I('get.version'));
		$type = trim(I('get.type'));

        $host = base64_decode(I('get.host'));

        //$domain_list = $this->domain_list;


	   $domain_list = $this->get_type_domain($type);



       if(!in_array($host,$domain_list))
       {
            $data = array();
            $data['domain'] = $host;
            $data['add_time'] = time();
            M('bad_domain')->add($data);
            die('-');
       }


        $need_updrade_list = array();
        $is_find_cur = false;


		$banben_list = $this->get_type_banben($type);


        foreach($banben_list as $key => $val)
        {
            if($is_find_cur)
            {
                $need_updrade_list[$key] = $val;
            }
            if($version == $key)
            {
                $is_find_cur = true;
            }
        }


        echo json_encode($need_updrade_list);
        die();
    }

}
