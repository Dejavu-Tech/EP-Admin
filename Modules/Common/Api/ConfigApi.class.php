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
