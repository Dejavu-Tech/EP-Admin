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
namespace Seller\Model;

class MenuModel
{

    /**
     * 获取 全部菜单带路由
     * @param bool $full 是否返回长URL
     * @return array
     */
    public function getMenu($full = false)
    {
        $return_menu = array();
        $return_submenu = array();

        $action_name = ACTION_NAME;
        if (!empty($action_name)) {

            $route = strtolower(CONTROLLER_NAME) . '.' . ACTION_NAME;
        } else {
            $route = strtolower(CONTROLLER_NAME);
        }

        $routes = explode('.', $route);
        $top = (empty($routes[0]) ? 'shop' : $routes[0]);


        $allmenus = $this->shopMenu();

        if ($routes[0] == 'system') {
            $top = $routes[1];
        }


        if (in_array(strtolower(CONTROLLER_NAME), array('weprogram', 'configpay', 'express', 'logistics', 'shipping', 'configindex', 'copyright'))) {
            $top = 'config';

        }

        if (!empty($allmenus)) {
            $submenu = $allmenus[$top];

            foreach ($allmenus as $key => $val) {

                $menu_item = array('route' => empty($val['route']) ? $key : $val['route'], 'text' => $val['title'], 'subtitle' => $val['subtitle']);

                if ($routes[0] == 'system') {
                    //$menu_item['route'] = 'system.' . $menu_item['route'];
                }
                if (isset($val['items'])) {
                    $menu_item['items'] = $val['items'];
                }
                if (!empty($val['index'])) {
                    $menu_item['index'] = $val['index'];
                }

                if (!empty($val['param'])) {
                    $menu_item['param'] = $val['param'];
                }

                if (!empty($val['icon'])) {
                    $menu_item['icon'] = $val['icon'];

                    if (!empty($val['iconcolor'])) {
                        $menu_item['iconcolor'] = $val['iconcolor'];
                    }
                }


                if ($this->verifyParam($val)) {
                    $menu_item['active'] = 1;
                }


                if ($full) {
                    //$menu_item['url'] = U($menu_item['route'], !empty($menu_item['param']) && is_array($menu_item['param']) ? $menu_item['param'] : array());
                }

                $return_menu[] = $menu_item;

            }

            unset($key);
            unset($val);

            if (!empty($submenu)) {


                $return_submenu['subtitle'] = $submenu['subtitle'];

                if ($submenu['main']) {
                    $return_submenu['route'] = $top;

                    if (is_string($submenu['main'])) {
                        //$return_submenu['route'] .= '.' . $submenu['main'];
                    }
                }

                if (!empty($submenu['index'])) {
                    //$return_submenu['route'] = $top . '.' . $submenu['index'];
                }


                if (!empty($submenu['items'])) {


                    //var_dump($submenu['items']);die();
                    foreach ($submenu['items'] as $i => $child) {


                        if (!empty($child['top'])) {
                            $top = '';
                        }

                        if (empty($child['items'])) {
                            $return_submenu_default = $top . '';
                            $route_second = $top;

                            if (!empty($child['route'])) {
                                if (!empty($top)) {
                                    $route_second .= '.';
                                }

                                $route_second .= $child['route'];
                            }

                            $return_menu_child = array('title' => $child['title'], 'route' => $child['route']);

                            if (!empty($child['param'])) {
                                $return_menu_child['param'] = $child['param'];
                            }

                            if (!empty($child['perm'])) {
                                $return_menu_child['perm'] = $child['perm'];
                            }

                            if (!empty($child['permmust'])) {
                                $return_menu_child['permmust'] = $child['permmust'];
                            }

                            if ($routes[0] == 'system') {
                                //$return_menu_child['route'] = 'system.' . $return_menu_child['route'];
                            }

                            $addedit = false;

                            if (!$child['route_must']) {
                                if ((($return_menu_child['route'] . '.add') == $route) || (($return_menu_child['route'] . '.edit') == $route)) {
                                    $addedit = true;
                                }
                            }

                            $extend = false;

                            if (!empty($child['extend'])) {
                                if ((($child['extend'] . '.add') == $route) || (($child['extend'] . '.edit') == $route) || ($child['extend'] == $route)) {
                                    $extend = true;
                                }
                            } else {
                                if (!empty($child['extends']) && is_array($child['extends'])) {
                                    if (in_array($route, $child['extends']) || in_array($route . '.add', $child['extends']) || in_array($route . '.edit', $child['extends'])) {
                                        $extend = true;
                                    }
                                }
                            }


                            if ($this->verifyParam($return_menu_child, false)) {
                                $return_menu_child['active'] = 1;
                            }


                            if ($full) {
                                //$return_menu_child['url'] = U($return_menu_child['route'], !empty($return_menu_child['param']) && is_array($return_menu_child['param']) ? $return_menu_child['param'] : array());
                            }

                            //if (!empty($return_menu_child['permmust']) && !$this->cv($return_menu_child['permmust'])) {
                            //	continue;
                            //}

                            //if (!$this->cv($return_menu_child['route'])) {
                            //	if (empty($return_menu_child['perm']) || !$this->cv($return_menu_child['perm'])) {
                            //		continue;
                            //	}
                            //}

                            $return_submenu['items'][] = $return_menu_child;
                            unset($return_submenu_default);
                            unset($route_second);
                        } else {
                            $return_menu_child = array(
                                'title' => $child['title'],
                                'items' => array()
                            );

                            foreach ($child['items'] as $ii => $three) {


                                $return_submenu_default = $top . '';
                                $route_second = 'main';

                                if (!empty($child['route'])) {
                                    $return_submenu_default = $top . '.' . $child['route'];
                                    $route_second = $child['route'];
                                }

                                $return_submenu_three = array('title' => $three['title']);

                                $return_submenu_three['route'] = $three['route'];

                                if ($this->verifyParam($three, false)) {
                                    $return_submenu_three['active'] = 1;
                                }


                                if (!empty($three['route'])) {
                                    if (!empty($child['route'])) {
                                        if (!empty($three['route_ns'])) {
                                            //$return_submenu_three['route'] = $top . '.' . $three['route'];
                                        } else {
                                            //	$return_submenu_three['route'] = $top . '.' . $child['route'] . '.' . $three['route'];
                                        }
                                    } else {
                                        if (!empty($three['top'])) {
                                            //$return_submenu_three['route'] = $three['route'];
                                        } else {
                                            //$return_submenu_three['route'] = $top . '.' . $three['route'];
                                        }

                                        $route_second = $three['route'];
                                    }
                                } else {
                                    //$return_submenu_three['route'] = $return_submenu_default;
                                }

                                if (!empty($three['param'])) {
                                    $return_submenu_three['param'] = $three['param'];
                                }

                                if (!empty($three['perm'])) {
                                    $return_submenu_three['perm'] = $three['perm'];
                                }

                                if (!empty($three['permmust'])) {
                                    $return_submenu_three['permmust'] = $three['permmust'];
                                }

                                if ($routes[0] == 'system') {
                                    ///$return_submenu_three['route'] = 'system.' . $return_submenu_three['route'];
                                }

                                $addedit = false;

                                if (!$three['route_must']) {
                                    if ((($return_submenu_three['route'] . '.add') == $route) || (($return_submenu_three['route'] . '.edit') == $route)) {
                                        $addedit = true;
                                    }
                                }

                                $extend = false;

                                if (!empty($three['extend'])) {
                                    if ((($three['extend'] . '.add') == $route) || (($three['extend'] . '.edit') == $route) || ($three['extend'] == $route)) {
                                        $extend = true;
                                    }
                                } else {
                                    if (!empty($three['extends']) && is_array($three['extends'])) {
                                        if (in_array($route, $three['extends']) || in_array($route . '.add', $three['extends']) || in_array($route . '.edit', $three['extends'])) {
                                            $extend = true;
                                        }
                                    }
                                }

                                if ($three['route_in'] && strexists($route, $return_submenu_three['route'])) {
                                    $return_menu_child['active'] = 1;
                                    $return_submenu_three['active'] = 1;
                                } else {
                                    if (($return_submenu_three['route'] == $route) || $addedit || $extend) {
                                        //if ($this->verifyParam($three)) {
                                        $return_menu_child['active'] = 1;
                                        $return_submenu_three['active'] = 1;
                                        //}
                                    }
                                }

                                if (!empty($child['extend'])) {
                                    if ($child['extend'] == $route) {
                                        $return_menu_child['active'] = 1;
                                    }
                                } else {
                                    if (is_array($child['extends'])) {
                                        if (in_array($route, $child['extends'])) {
                                            $return_menu_child['active'] = 1;
                                        }
                                    }
                                }

                                if ($full) {
                                    //$return_submenu_three['url'] = U($return_submenu_three['route'], !empty($return_submenu_three['param']) && is_array($return_submenu_three['param']) ? $return_submenu_three['param'] : array());
                                }

                                //	if (!empty($return_submenu_three['permmust']) && !$this->cv($return_submenu_three['permmust'])) {
                                //		continue;
                                //	}

                                //	if (!$this->cv($return_submenu_three['route'])) {
                                //	if (empty($return_submenu_three['perm']) || !$this->cv($return_submenu_three['perm'])) {
                                //			continue;
                                //		}
                                //}

                                $return_menu_child['items'][] = $return_submenu_three;
                            }

                            if (!empty($child['items']) && empty($return_menu_child['items'])) {
                                continue;
                            }

                            $return_menu_child['is_show_list'] = $child['is_hide_child'];


                            $return_submenu['items'][] = $return_menu_child;
                            unset($ii);
                            unset($three);
                            unset($route_second);
                        }
                    }
                }
            }
        }


        return array('menu' => $return_menu, 'submenu' => $return_submenu, 'shopmenu' => array());
    }

    protected function verifyParam($item = array(), $is_first = true)
    {
        $action_name = ACTION_NAME;

        if (!empty($action_name)) {
            $route = strtolower(CONTROLLER_NAME) . '/' . ACTION_NAME;
        } else {
            $route = strtolower(CONTROLLER_NAME);
        }


        $return = false;

        $item_controller_action = $item['route'];


        $item_controller_action_arr = explode('/', $item_controller_action);

        if ($is_first) {

            //weprogram
            //weprogram array('weprogram','configpay','configindex','copyright')
            //特殊菜单
            if (in_array(strtolower(CONTROLLER_NAME), array('weprogram', 'configpay', 'configindex', 'copyright'))) {
                if ($item_controller_action_arr[0] == 'config') {
                    $return = true;
                }
            }

            //一级菜单
            if ($item_controller_action_arr[0] == strtolower(CONTROLLER_NAME)) {
                $return = true;
            }

            //var_dump($item_controller_action_arr[0] , strtolower(CONTROLLER_NAME) );die();
        } else {

            if ($item_controller_action == $route) {
                $return = true;
            }
        }


        /**
         * array(5) { ["title"]=> string(6) "概况" ["icon"]=> string(5) "index"
         * ["subtitle"]=> string(12) "概况信息" ["route"]=> string(5) "index"
         * ["items"]=> array(1) { [0]=> array(2) { ["title"]=> string(6) "统计" ["route"]=> string(5) "index" } } }
         **/


        return $return;
    }

    /**
     * 定义 商城 菜单
     * @return array
     */
    public function shopMenu()
    {

        $shopmenu_2 = array(
            'index' => array(
                'title' => '概况',
                'icon' => 'home',
                'subtitle' => '概况信息',
                'route' => 'index/analys',
                'items' => array(
                    array('title' => '统计', 'route' => 'index/analys'),
                )
            ),
            'goods' => array(
                'title' => '商品',
                'subtitle' => '商品管理',
                'icon' => 'social-shopify',
                'route' => 'goods/index',
                'items' => array(
                    array('title' => '商品列表', 'route' => 'goods/index'),
                    array('title' => '商品分类', 'route' => 'goods/goodscategory'),
                    array('title' => '商品规格', 'route' => 'goods/goodsspec'),
                    array('title' => '商品标签', 'route' => 'goods/goodstag'),
                    //array('title' => '虚拟评价', 'route' => 'goods/goodsvircomment'),
                    //array('title' => '商品设置', 'route' => 'goods/config'),

                    array(
                        'title' => '商品设置',
                        'route' => 'goods/config',
                        'items' => array(
                            array('title' => '基本设置', 'route' => 'goods/config', 'desc' => ''),
                            array('title' => '统一时间', 'route' => 'goods/settime', 'desc' => ''),
                            array('title' => '工商资质', 'route' => 'goods/industrial', 'desc' => '')
                        ),
                    ),
                )
            ),
            'order' => array(
                'title' => '订单',
                'subtitle' => '订单管理',
                'icon' => 'shopping-cart',
                'route' => 'order/index',
                'items' => array(
                    array('title' => '订单列表', 'route' => 'order/index', 'desc' => ''),



                    array(
                        'title' => '售后管理',
                        'route' => 'order/orderaftersales',
                        'items' => array(
                            array('title' => '售后订单', 'route' => 'order/orderaftersales', 'desc' => ''),
                        )
                    ),
                    array(
                        'title' => '评价管理',
                        'route' => 'order/ordercomment',
                        'items' => array(
                            array('title' => '评价列表', 'route' => 'order/ordercomment', 'desc' => ''),
                            array('title' => '评价有礼', 'route' => 'order/ordercomment_gift', 'desc' => ''),
                            array('title' => '评价设置', 'route' => 'order/ordercomment_config', 'desc' => '')
                        ),
                    ),
                    array('title' => '订单设置', 'route' => 'order/config', 'desc' => ''),

                ),
            ),
            'user' => array(
                'title' => '会员',
                'subtitle' => '会员管理',
                'icon' => 'user-alt-5',
                'route' => 'user/index',
                'items' => array(
                    array('title' => '会员列表', 'route' => 'user/index', 'desc' => ''),
                    array('title' => '虚拟会员', 'route' => 'user/userjia', 'desc' => ''),
                    array('title' => '会员设置', 'route' => 'user/config', 'desc' => ''),
                    array('title' => '会员分组', 'route' => 'user/usergroup', 'desc' => ''),
                    array('title' => '会员等级', 'route' => 'user/userlevel', 'desc' => ''),
                )
            ),
            'communityhead' => array(
                'title' => '团长',
                'subtitle' => '团长管理',
                'icon' => 'brainstorming',
                'route' => 'communityhead/index',
                'items' => array(
                    array('title' => '团长列表', 'route' => 'communityhead/index', 'desc' => ''),
                    array('title' => '团长分组', 'route' => 'communityhead/usergroup', 'desc' => ''),
                    array('title' => '团长等级', 'route' => 'communityhead/headlevel', 'desc' => ''),
                    array('title' => '团长设置', 'route' => 'communityhead/config', 'desc' => ''),
                    array(
                        'title' => '提现管理',
                        'route' => 'communityhead/distribulist',
                        'items' => array(
                            array('title' => '提现列表', 'route' => 'communityhead/distribulist', 'desc' => ''),
                            array('title' => '提现设置', 'route' => 'communityhead/distributionpostal', 'desc' => ''),

                        )
                    ),
                ),
            ),



            'perm' => array(
                'title' => '权限',
                'subtitle' => '权限管理',
                'icon' => 'ui-lock',
                'route' => 'perm/index',
                'items' => array(
                    array('title' => '角色管理', 'route' => 'perm/index', 'desc' => ''),
                    array('title' => '后台管理员', 'route' => 'perm/user', 'desc' => ''),
                ),
            ),

            'config' => array(
                'title' => '设置',
                'subtitle' => '设置',
                'icon' => 'settings-alt',
                'route' => 'config/index',
                'items' => array(
                    array('title' => '基本设置', 'route' => 'config/index', 'desc' => ''),
                    array('title' => '图片设置', 'route' => 'config/picture', 'desc' => ''),
                    array(
                        'title' => '小程序设置',
                        'route' => 'weprogram/index',
                        'items' => array(
                            array('title' => '参数设置', 'route' => 'weprogram/index', 'desc' => ''),
                            array('title' => '模板消息', 'route' => 'weprogram/templateconfig', 'desc' => ''),
                            array('title' => '订阅消息', 'route' => 'weprogram/subscribetemplateconfig', 'desc' => ''),
                            array('title' => '底部菜单', 'route' => 'weprogram/tabbar', 'desc' => ''),
                        )
                    ),
                    //array('title' => '证书设置', 'route' => 'configpay/index', 'desc' => ''),


                    array(
                        'title' => '个人中心',
                        'route' => 'copyright/index',
                        'is_hide_child' => 2,
                        'items' => array(
                            array('title' => '版权说明', 'route' => 'copyright/index', 'desc' => ''),
                            array('title' => '关于我们', 'route' => 'copyright/about', 'desc' => ''),
                        )
                    ),
                    //array('title' => '后台账户', 'route' => 'copyright/account', 'desc' => ''),
                )
            ),
        );


        if (SELLERUID != 1) {
            $seller_info = M('seller')->field('s_role_id')->where(array('s_id' => SELLERUID))->find();

            $perm_role = M('eaterplanet_ecommerce_perm_role')->where(array('id' => $seller_info['s_role_id']))->find();

            //marketing,marketing.marketing.recharge_diary,marketing.vipcard.order
            $perms_str = $perm_role['perms2'];


            // marketing.explain

            $shopmenu3 = array();
            foreach ($shopmenu_2 as $key => $val) {
                $j = 0;
                $is_in = false;
                $new_val = $val;
                $get_items = array();
                $first_route = '';

                foreach ($val['items'] as $kk => $vv) {
                    //route

                    $new_rt = str_replace('/', '.', $vv['route']);

                    $is_in_child = false;
                    if (isset($vv['items']) && !empty($vv['items'])) {
                        $ij = 0;

                        $tp_items = array();
                        foreach ($vv['items'] as $tk => $tv) {
                            $new_rt_tk = str_replace('/', '.', $tv['route']);

                            if (strpos($perms_str, $new_rt_tk) !== false) {
                                $tp_items[$tk] = $tv;

                                $is_in_child = true;

                                if ($ij == 0) {
                                    $first_route = $tv['route'];
                                }
                                $ij++;
                            }
                        }

                        if (!empty($tp_items)) {
                            $vv['route'] = $first_route;
                            $vv['items'] = $tp_items;
                        }
                    }


                    if (strpos($perms_str, $new_rt) !== false) {
                        $get_items[$kk] = $vv;
                        $is_in = true;
                    } else {
                        if (isset($vv['items']) && !empty($vv['items']) && $is_in_child) {
                            $get_items[$kk] = $vv;
                            $is_in = true;
                        }
                        continue;
                    }

                    if ($j == 0) {
                        $first_route = $vv['route'];
                    }

                    $j++;
                }
                if ($is_in) {
                    $new_val['route'] = $first_route;
                    $new_val['items'] = $get_items;
                    $shopmenu3[$key] = $new_val;
                }
            }

            $shopmenu_2 = $shopmenu3;

        }


        if (defined('ROLE') && ROLE == 'agenter') {

            $supper_info = get_agent_logininfo();

            $shopmenu_2 = array();

            $shopmenu_2['index'] = array(
                'title' => '概况',
                'icon' => 'home',
                'subtitle' => '概况信息',
                'route' => 'index/analys',
                'items' => array(
                    array('title' => '统计', 'route' => 'index/analys')
                )
            );

            $shopmenu_2['goods'] = array(
                'title' => '商品',
                'subtitle' => '商品管理',
                'icon' => 'social-shopify',
                'route' => 'goods/index',
                'items' => array(
                    array('title' => '商品列表', 'route' => 'goods/index'),
                    array(
                        'title' => '商品设置',
                        'route' => '',
                        'items' => array(
                            array('title' => '统一时间', 'route' => 'goods/settime', 'desc' => '')
                        ),
                    ),
                )
            );

            if ($supper_info['type'] == 1) {
                $is_open_supply_print = D('Home/Front')->get_config_by_name('is_open_supply_print');
                if (!empty($is_open_supply_print)) {
                    $shopmenu_2['order'] = array(
                        'title' => '订单',
                        'subtitle' => '订单管理',
                        'icon' => 'shopping-cart',
                        'route' => 'order/index',
                        'items' => array(
                            array('title' => '订单列表', 'route' => 'order/index', 'desc' => ''),
                            array('title' => '批量发货', 'route' => 'order/ordersendall', 'desc' => ''),
                            array(
                                'title' => '售后管理',
                                'route' => '',
                                'items' => array(
                                    array('title' => '售后订单', 'route' => 'order/orderaftersales', 'desc' => ''),
                                )
                            ),
                            array('title' => '打印机设置', 'route' => 'order/printconfig', 'desc' => ''),

                        )
                    );
                } else {
                    $shopmenu_2['order'] = array(
                        'title' => '订单',
                        'subtitle' => '订单管理',
                        'icon' => 'shopping-cart',
                        'route' => 'order/index',
                        'items' => array(
                            array('title' => '订单列表', 'route' => 'order/index', 'desc' => ''),
                            array('title' => '批量发货', 'route' => 'order/ordersendall', 'desc' => ''),
                            array(
                                'title' => '售后管理',
                                'route' => '',
                                'items' => array(
                                    array('title' => '售后订单', 'route' => 'order/orderaftersales', 'desc' => ''),
                                )
                            ),
                        )
                    );
                }
            } else {
                $shopmenu_2['order'] = array(
                    'title' => '订单',
                    'subtitle' => '订单管理',
                    'route' => 'order/index',
                    'icon' => 'shopping-cart',
                    'items' => array(
                        array('title' => '订单列表', 'route' => 'order/index', 'desc' => ''),
                    )
                );
            }

            $shopmenu_2['salesroom'] = array(
                'title' => '门店',
                'subtitle' => '门店管理',
                'icon' => 'fast-food',
                'route' => 'salesroom/index',
                'items' => array(
                    array('title' => '门店列表', 'route' => 'salesroom/index', 'desc' => ''),
                    array('title' => '核销人员', 'route' => 'salesroom_member/index', 'desc' => ''),
                    array('title' => '核销订单记录', 'route' => 'salesroom_order/index', 'desc' => ''),
                ),
            );

            $shopmenu_2['supply'] = array(
                'title' => '财务',
                'subtitle' => '资金流水',
                'icon' => 'chart-pie',
                'route' => 'supply/floworder',
                'items' => array(
                    array('title' => '资金流水', 'route' => 'supply/floworder', 'desc' => ''),
                    array('title' => '提现管理', 'route' => 'supply/tixianlist', 'desc' => ''),
                ),
            );
            $supply_is_open_localtown_distribution = D('Home/Front')->get_config_by_name('supply_is_open_localtown_distribution');
            $isopen_localtown_delivery = D('Home/Front')->get_config_by_name('isopen_localtown_delivery');
            if (!empty($supply_is_open_localtown_distribution) && !empty($isopen_localtown_delivery)) {
                $shopmenu_2['modifypassword'] = array(
                    'title' => '设置',
                    'subtitle' => '登录信息',
                    'icon' => 'settings-alt',
                    'route' => 'supply/modifypassword',
                    'items' => array(
                        array('title' => '修改密码', 'route' => 'supply/modifypassword', 'desc' => ''),
                        array('title' => '同城配送', 'route' => 'express/localtownconfig', 'desc' => '')
                    ),
                );

                $shopmenu_2['order']['items'][] = array(
                    'title' => '配送管理',
                    'route' => 'orderdistribution/index',
                    'items' => array(
                        array('title' => '配送员管理', 'route' => 'orderdistribution/index', 'desc' => ''),
                        array('title' => '配送设置', 'route' => 'orderdistribution/distributionconfig', 'desc' => ''),
                    )
                );
                $shopmenu_2['order']['items'][] = array(
                    'title' => '提现管理',
                    'route' => 'orderdistribution/withdrawallist',
                    'items' => array(
                        array('title' => '提现列表', 'route' => 'orderdistribution/withdrawallist', 'desc' => ''),
                        array('title' => '提现设置', 'route' => 'orderdistribution/withdrawalconfig', 'desc' => ''),
                    )
                );
            } else {
                $shopmenu_2['modifypassword'] = array(
                    'title' => '设置',
                    'subtitle' => '登录信息',
                    'icon' => 'settings-alt',
                    'route' => 'supply/modifypassword',
                    'items' => array(
                        array('title' => '修改密码', 'route' => 'supply/modifypassword', 'desc' => '')
                    ),
                );
            }
        }
        return $shopmenu_2;
    }

    public function check_seller_perm($route)
    {
        if (SELLERUID != 1) {

            $seller_info = M('seller')->field('s_role_id')->where(array('s_id' => SELLERUID))->find();

            $perm_role = M('eaterplanet_ecommerce_perm_role')->where(array('id' => $seller_info['s_role_id']))->find();

            $perms_str = $perm_role['perms2'];

            $new_route = str_replace('/', '.', $route);


            if (strpos($perms_str, $new_route) !== false) {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }
}

?>
