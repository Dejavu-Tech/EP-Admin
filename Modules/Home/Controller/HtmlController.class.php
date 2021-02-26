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
namespace Home\Controller;

class HtmlController extends CommonController {

    public function about(){
       $this->title='关于我们-';
       $this->meta_keywords=C('SITE_KEYWORDS');
       $this->meta_description=C('SITE_DESCRIPTION');
       $this->display();
    }

    public function contact(){
       $this->title='联系我们-';
       $this->meta_keywords=C('SITE_KEYWORDS');
       $this->meta_description=C('SITE_DESCRIPTION');
       $this->display();
    }



}
