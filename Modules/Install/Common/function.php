<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// 检测环境是否支持可写
define('IS_WRITE', APP_MODE !== 'sae');

/**
 * 生成系统AUTH_KEY
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function build_auth_key()
{
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chars .= '`~!@#$%^&*()_+-=[]{};:"|,.<>/?';
    $chars = str_shuffle($chars);
    return substr($chars, 0, 40);
}


/**
 * 系统环境检测
 * @return array 系统环境数据
 */
function check_env()
{
    $items = array(
        'os' => array('操作系统', '不限制', '类Unix', PHP_OS, 'success'),
        'php' => array('PHP版本', '5.3', '5.3+', PHP_VERSION, 'success'),
        //'mysql'   => array('MYSQL版本', '5.0', '5.0+', '未知', 'success'), //PHP5.5不支持mysql版本检测
        'upload' => array('附件上传', '不限制', '2M+', '未知', 'success'),
        'gd' => array('GD库', '2.0', '2.0+', '未知', 'success'),
        'curl' => array('Curl扩展', '开启', '不限制', '未知', 'success'),
        'disk' => array('磁盘空间', '150M', '不限制', '未知', 'success'),
    );

    //PHP环境检测
    if ($items['php'][3] < $items['php'][1]) {
        $items['php'][4] = 'remove';
        session('error', true);
    }


    //附件上传检测
    if (@ini_get('file_uploads'))
        $items['upload'][3] = ini_get('upload_max_filesize');

    //GD库检测
    $tmp = function_exists('gd_info') ? gd_info() : array();
    if (empty($tmp['GD Version'])) {
        $items['gd'][3] = '未安装';
        $items['gd'][4] = 'remove';
        session('error', true);
    } else {
        $items['gd'][3] = $tmp['GD Version'];
    }
    unset($tmp);

    $tmp = function_exists('curl_init') ? curl_version() : array();
    if (empty($tmp['version'])) {
        $items['curl'][3] = '未安装';
        $items['curl'][4] = 'remove';
        session('curl', true);
    } else {
        $items['curl'][3] = $tmp['version'];
    }
    unset($tmp);
    //磁盘空间检测
    if (function_exists('disk_free_space')) {
        $items['disk'][3] = floor(disk_free_space(INSTALL_APP_PATH) / (1024 * 1024)) . 'M';
    }

    return $items;
}

/**
 * 目录，文件读写检测
 * @return array 检测数据
 */
function check_dirfile()
{
    $items = array(
        array('dir', '可写', 'ok', './Uploads/image'),
        array('dir', '可写', 'ok', './Runtime'),
        array('file', '可写', 'ok', './Modules/Common/Conf/')
	);

    foreach ($items as &$val) {
        if ('dir' == $val[0]) {
            if (!is_writable(INSTALL_APP_PATH . $val[3])) {
                if (is_dir($items[1])) {
                    $val[1] = '可读';
                    $val[2] = 'remove';
                    session('error', true);
                } else {
                    $val[1] = '不存在或者不可写';
                    $val[2] = 'remove';
                    session('error', true);
                }
            }
        } else {
            if (file_exists(INSTALL_APP_PATH . $val[3])) {
                if (!is_writable(INSTALL_APP_PATH . $val[3])) {
                    $val[1] = '存在但不可写';
                    $val[2] = 'remove';
                    session('error', true);
                }
            } else {
                if (!is_writable(dirname(INSTALL_APP_PATH . $val[3]))) {
                    $val[1] = '不存在或者不可写';
                    $val[2] = 'remove';
                    session('error', true);
                }
            }
        }
    }

    return $items;
}

/**
 * 函数检测
 * @return array 检测数据
 */
function check_func()
{
    $items = array(
        array('file_get_contents', '支持', 'ok'),
        array('mb_strlen', '支持', 'ok'),
        array('curl_init', '支持', 'ok'),
    );

    foreach ($items as &$val) {
        if (!function_exists($val[0])) {
            $val[1] = '不支持';
            $val[2] = 'remove';
            $val[3] = '开启';
            session('error', true);
        }
    }

    return $items;
}

/**
 * 写入配置文件
 * @param  array $config 配置信息
 */
function write_config($config, $auth)
{
    if (is_array($config)) {
        //读取配置内容
        $conf = file_get_contents(MODULE_PATH . 'Data/db.tpl');

        //替换配置项
        foreach ($config as $name => $value) {
            $conf = str_replace("[{$name}]", $value, $conf);

        }

        $conf = str_replace('[AUTH_KEY]', seller, $conf);


        //写入应用配置文件
        if (!IS_WRITE) {
            return '由于您的环境不可写，请复制下面的配置文件内容覆盖到相关的配置文件，然后再登录后台。<p>' . realpath('') . './Modules/Common/Conf/db.php</p>
			<textarea name="" style="width:650px;height:185px">' . $conf . '</textarea>';
        } else {
            if (file_put_contents('./Modules/Common/Conf/db.php', $conf)) {
                chmod('./Modules/Common/Conf/db.php', 0777);

                show_msg('配置文件写入成功');
            } else {
                show_msg('配置文件写入失败！', 'error');
                session('error', true);
            }
            return '';
        }

    }
}

/**
 * 创建数据表
 * @param  resource $db 数据库连接资源
 */
function create_tables($db, $prefix = '')
{
    //读取SQL文件
    $sql = file_get_contents(MODULE_PATH . 'Data/install.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $orginal = C('ORIGINAL_TABLE_PREFIX');
    $sql = str_replace(" `{$orginal}", " `{$prefix}", $sql);


    //开始安装
    show_msg('开始安装数据库...');
    foreach ($sql as $value) {
        $value = trim($value);
        if (empty($value)) continue;
        if (substr($value, 0, 12) == 'CREATE TABLE') {
            $name = preg_replace("/^CREATE TABLE IF NOT EXISTS `(\w+)` .*/s", "\\1", $value);
            $msg = "创建数据表{$name}";
            if (false !== $db->execute($value)) {
                show_msg($msg . '...成功');
            } else {
                show_msg($msg . '...失败！', 'error');
                session('error', true);
            }
        } else {
            $db->execute($value);
        }
    }

}

function register_administrator($db, $prefix, $seller, $auth)
{
    show_msg('开始注册初始管理员帐号...');
    /*插入用户*/
    $sql = <<<sql
REPLACE INTO `[PREFIX]seller` (`s_id`,`s_uname`, `s_passwd`, `s_create_time`, `s_status`) VALUES
(1,'[NAME]', '[PASS]', '[TIME]',1);
sql;

    $password = think_ucenter_encrypt($seller['password'], $auth);
    $sql = str_replace(
        array('[PREFIX]', '[NAME]', '[PASS]', '[TIME]'),
        array($prefix, $seller['username'], $password,  NOW_TIME),
        $sql);

    //执行sql
    $db->execute($sql);
    /*插入pwd_key资料*/
    $sql = <<<sql
REPLACE INTO `[PREFIX]config` (`name`, `config_group`, `create_time`, `value`) VALUES
('[NAME]','site','[TIME]','[PASS]');
sql;

    $sql = str_replace(
        array('[PREFIX]', '[NAME]', '[TIME]', '[PASS]'),
        array($prefix,'SELLER_PWD_KEY', NOW_TIME, $auth),
        $sql);

    $db->execute($sql);

    show_msg('初始管理员帐号注册完成！');
}

/**
 * 及时显示提示信息
 * @param  string $msg 提示信息
 */
function show_msg($msg, $class = '')
{
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\")</script>";
    ob_flush();
    flush();


}


/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function user_md5($str, $key = '')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}
