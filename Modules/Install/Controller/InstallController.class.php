<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Install\Controller;
use Think\Controller;
use Think\Db;
use Think\Storage;

class InstallController extends Controller{

    protected function _initialize(){

        if(Storage::has( 'Conf/install.lock')){
            $this->error('已经成功安装，请不要重复安装!');
        }
    }

    //安装第一步，检测运行所需的环境设置
    public function step1(){
        session('error', false);

        //环境检测
        $env = check_env();

        //目录文件读写检测
        if(IS_WRITE){
            $dirfile = check_dirfile();
            $this->assign('dirfile', $dirfile);
        }

        //函数检测
        $func = check_func();

        session('step', 1);

        $this->assign('env', $env);
        $this->assign('func', $func);
        $this->display();
    }

    //安装第二步，创建数据库
    public function step2($db = null, $seller = null){
        if(IS_POST){


            //检测数据库配置
            if(!is_array($db) || empty($db[0]) ||  empty($db[1]) || empty($db[2]) || empty($db[3])){
                $this->error('请填写完整的数据库配置');
            } else {
                $DB = array();
                list($DB['DB_TYPE'], $DB['DB_HOST'], $DB['DB_NAME'], $DB['DB_USER'], $DB['DB_PWD'],
                    $DB['DB_PORT'], $DB['DB_PREFIX']) = $db;
                //缓存数据库配置
                cookie('db_config',$DB);

                //创建数据库
                $dbname = $DB['DB_NAME'];
                unset($DB['DB_NAME']);

                $db  = Db::getInstance($DB);

                $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";

                try{
                    $db->execute($sql);
                }catch (\Think\Exception $e){
                    if(strpos($e->getMessage(),'getaddrinfo failed')!==false){
                        $this->error( '数据库服务器（数据库服务器IP） 填写错误。','很遗憾，创建数据库失败，失败原因');// 提示信息
                    }
                   if(strpos($e->getMessage(),'Access denied for user')!==false){
                       $this->error('数据库用户名或密码 填写错误。','很遗憾，创建数据库失败，失败原因');// 提示信息
                   }else{
                       $this->error( $e->getMessage());// 提示信息
                   }
                }
                session('step',2);
                // $this->error($db->getError());exit;
            }

            //跳转到数据库安装页面
            $this->redirect('step3');
        } else {
                session('error') && $this->error('环境检测没有通过，请调整环境后重试！');

                $step = session('step');
                if($step != 1 && $step != 2){
                   // $this->redirect('step1');
                }

                session('step', 2);
                $this->display();

        }
    }

    //安装第三步，创建数据库
    public function step3($db = null, $seller = null){
        if(IS_POST){


            //检测管理员信息
            if(!is_array($seller) || empty($seller[0]) || empty($seller[1])){
                $this->error('请填写完整管理员信息');
            } else if($seller[1] != $seller[2]){
                $this->error('确认密码和密码不一致');
            } else {
                $info = array();
                list($info['username'], $info['password'], $info['repassword'])
                    = $seller;
                //缓存管理员信息
                session('seller_info', $info);
            }


            //跳转到数据库安装页面
            $this->redirect('step4');
        } else {
                session('error') && $this->error('环境检测没有通过，请调整环境后重试！');

                $step = session('step');
                if($step != 1 && $step != 2){
                   // $this->redirect('step1');
                }

                session('step', 2);
                $this->display();

        }
    }

    //安装第四步，安装数据表，创建配置文件
    public function step4(){
       /* if(session('step') != 2){
            $this->redirect('step2');
        }*/

        $this->display();



            //连接数据库
            $dbconfig = cookie('db_config');
            $db = Db::getInstance($dbconfig);
            //创建数据表
			//dump($dbconfig);die;
            create_tables($db, $dbconfig['DB_PREFIX']);
            //注册创始人帐号
            $auth  = build_auth_key();
            $seller = session('seller_info');

			//dump($admin);die;

            register_administrator($db, $dbconfig['DB_PREFIX'], $seller, $auth);

            //创建配置文件
            $conf   =   write_config($dbconfig, $auth);
            session('config_file',$conf);


        if(session('error')){
            //show_msg();
        } else {
            session('step', 3);

            echo "<script type=\"text/javascript\">setTimeout(function(){location.href='".U('Index/complete')."'},5000)</script>";
            ob_flush();
            flush();
           //$this->redirect('Index/complete');
        }
    }

    public function error($info,$title='安装失败！失败原因：'){
        $this->assign('info',$info);// 提示信息
        $this->assign('title',$title);
        $this->display('error');exit;
    }
}
