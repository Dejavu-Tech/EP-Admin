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
namespace Admin\Widget;
use Think\Controller;
/**
 * 后台菜单
 */
class MenuWidget extends Controller{

	function menu_show(){

		$menu=M('Menu')->order('sort_order')->select();
		$tree=list_to_tree($menu,'id','pid','children',0);

		if(!$_SESSION[C('ADMIN_AUTH_KEY')])
		{
			$authId = $_SESSION[C('USER_AUTH_KEY')];
			//先查询出有权限的 node ,拼接node的链接，查询出所有的菜单可用性，接着，反向查询，顶层无链接的菜单

			$show_menu_arr = array();//需要显示的菜单
			$user_auth_arr = session('user_auth');
			$user_role_id = $user_auth_arr['role_id'];

			$access_action_arr =   S('ACCESS_ACTION_LIST_ROLE'.$user_role_id);
	        if(empty($access_action_arr))
	        {
				$sql = "select n.* from ".C('DB_PREFIX')."access  as a left join ".C('DB_PREFIX')."node as n on a.node_id = n.id where a.role_id = {$user_role_id} order by pid asc";
				$node_list = M()->query($sql);

				$node_list_tree = list_to_tree($node_list,'id','pid','children',0);

				$access_action_arr = array();//被允许的模块
				foreach($node_list_tree as $first)
				{
					foreach($first['children'] as $second)
					{
						foreach($second['children'] as $third)
						{
							$access_action_arr[] =  strtoupper($second['name'].'/'.$third['name']);
						}
					}
				}
				 S('ACCESS_ACTION_LIST_ROLE'.$user_role_id,$access_action_arr);
			}

			$child_menu = array();
			foreach($menu as $val)
			{
				if( in_array(strtoupper($val['url']),$access_action_arr) )
				{
					$child_menu[] = $val['id'];
					$this->_get_parent_menu($val,$child_menu);
				}
			}

			$child_menu_str = implode(',',$child_menu);

			$menu=M('Menu')->where( 'id in ('.$child_menu_str.')' )->order('sort_order')->select();

			$tree=list_to_tree($menu,'id','pid','children',0);

		}

		$this->admin_menu=$tree;
		$this->display('Widget:menu');
	}

	/**
	 * 获取上级菜单
	 */
	private function _get_parent_menu($menu,&$child_menu)
	{
		if($menu['pid'] !=0)
		{
			$tmp_menu = M('menu')->where( array('id' => $menu['pid']) )->find();
			$child_menu[] = $tmp_menu['id'];
			$this->_get_parent_menu($tmp_menu,$child_menu);
		}
	}



	/**
     * 得到菜单
     * @return array
     */
    protected function getMenu() {
        $menu = C('MENU');


        // 主菜单
        $mainMenu = array();
        // 已被映射过的键值
        $mapped = array();

        // 访问权限
        $access = $_SESSION['_ACCESS_LIST'];
      //var_dump($access);die();
        if (empty($access)) {
            $authId = $_SESSION[C('USER_AUTH_KEY')];
            $access = \Org\Util\Rbac::getAccessList($authId);
        }

        $authGroup = strtoupper(C('GROUP_AUTH_NAME'));

        // 处理主菜单
        foreach ($menu as $key => $menuItem) {
            // 不显示无权限访问的主菜单
            if (!$_SESSION[C('ADMIN_AUTH_KEY')]
                && !array_key_exists(strtoupper($key), $access[$authGroup])) {
                continue ;
            }

            // 主菜单是否存在映射
            if (isset($menuItem['mapping'])) {
                // 映射名
                $mapping = $menuItem['mapping'];
                // 新的菜单键值
                if (!empty($mapped[$mapping])) {
                    $key = "{$mapped[$mapping]}-{$key}";
                    $mapping = $mapped[$mapping];
                } else {
                    $key = "{$mapping}-{$key}";
                }

                // 需要映射的键值已存在，则删除
                if (isset($mainMenu[$mapping])) {
                    $mainMenu[$key]['name'] = $mainMenu[$mapping]['name'];
                    $mainMenu[$key]['target'] = $mainMenu[$mapping]['target'];
                    unset($mainMenu[$mapping]);
                    $mapped[$mapping] = $key;
                }

                continue ;
            }

            $mainMenu[$key]['name'] = $menuItem['name'];
            $mainMenu[$key]['target'] = $menuItem['target'];

            //如果默认的target用户无权访问，则显示sub_menu中的用户有权访问的第一个页面
            $actions = $access[$authGroup][strtoupper($key)];
            $action = explode('/', strtoupper($mainMenu[$key]['target']));
            while (!$_SESSION[C('ADMIN_AUTH_KEY')] && !array_key_exists($action[1], $actions)) {
                $nextSubMenu = next($menu[$key]['sub_menu']);
                if (empty($nextSubMenu)) break;
                $mainMenu[$key]['target'] = key(current($nextSubMenu));
                $action = explode('/', strtoupper($mainMenu[$key]['target']));
            }
        }

        // 子菜单
        $subMenu = array();
        $ctrlName = $this->getCtrName();
        if (isset($menu[$ctrlName]['mapping'])) {
            $ctrlName = $menu[$ctrlName]['mapping'];
        }

        $actions = $access[$authGroup];
        // 主菜单如果为隐藏，则子菜单也不被显示
        foreach ($menu[$ctrlName]['sub_menu'] as $item) {
            // 子菜单是否需要显示
            if (isset($item['hidden']) && true === $item['hidden']) {
                continue ;
            }

            $route = array_shift(array_keys($item['item']));
            $action = explode('/', strtoupper($route));
            // 不显示无权限访问的子菜单
            if (!$_SESSION[C('ADMIN_AUTH_KEY')]
                && (!array_key_exists($action[0], $actions)
                    || !array_key_exists($action[1], $actions[$action[0]]))) {
                continue ;
            }

            // 子菜单是否有配置
            if (!isset($item['item']) || empty($item['item'])) {
                continue ;
            }

            $routes = array_keys($item['item']);
            $itemNames = array_values($item['item']);
            $subMenu[$routes[0]] = $itemNames[0];
        }

        unset($menu);
        return array(
            'main_menu' => $mainMenu,
            'sub_menu' => $subMenu
        );
    }

    protected function getCtrName() {
        $ctrName = CONTROLLER_NAME;

        if(strpos($ctrName, '.') !== false && strtoupper($ctrName[0]) === $ctrName[0]) {
            $ctrName[0] = strtolower($ctrName[0]);
        }

        return $ctrName;
    }

}

