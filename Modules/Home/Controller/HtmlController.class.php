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
