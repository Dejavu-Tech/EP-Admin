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
