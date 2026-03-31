<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;
class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

// 获取用户代理字符串


// 使用简单的字符串查找判断设备类型
    public function getDeviceType($userAgent) {
    if (strpos($userAgent, 'iPhone') !== false) {
        return '手机';
    } elseif (strpos($userAgent, 'iPad') !== false) {
        return '手机';
    } elseif (strpos($userAgent, 'Android') !== false) {
        return '手机';
    } elseif (strpos($userAgent, 'Windows') !== false) {
        return '电脑';
    } elseif (strpos($userAgent, 'Macintosh') !== false) {
        return '电脑';
    } else {
        return '电脑';
    }
}




    
    public function index()
    {
        
        
        if (isset($_GET['choukaid']) && intval($_GET['choukaid']) > 0) {
            if (isset($_GET['token'])) {
                 //查看下是不是token相等
                 $chouka_info = Db::name('chouka')->where(['id'=>$_GET['choukaid']])->find();
                 if($chouka_info['token'] != $_GET['token']){
                     return $this->view->fetch('indexs');
                 }
            } else {
                return $this->view->fetch('indexs');
            }
        } else {
            return $this->view->fetch('indexs');
        }
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        // 获取设备类型
       
        if(!isset($_GET['teshu'])){
             $deviceType =$this->getDeviceType($userAgent);
             //查询用户的抽卡方式：
             $user_info = Db::name('admin')->where(['id'=>$chouka_info['admin_id']])->find();
             if($user_info['choukadata']=="1"){
                 if($deviceType!="电脑"){
                     return $this->view->fetch('indexs');
                 }
             }
        }
        $this->assign([
            'choukaid'=>$_GET['choukaid'],
            'token'=>$_GET['token'],
            'dakatime'=>date("H:i:s",$chouka_info['choukatime'])
        ]);
        return $this->view->fetch();
        
    }

}
