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

namespace Common\Api;
class ConfigApi {
    /**
     * 获取数据库中的配置列表
     * @return array 配置数组
     */
    public static function lists(){

        $data   = M('Config')->select();

        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['name']] =$value['value'];
            }
        }
        return $config;
    }


}
