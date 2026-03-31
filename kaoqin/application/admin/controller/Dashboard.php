<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
    
        
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        //先查询有多少人：
        //查询今天有没有请假的：
        //查询这些人是不是要上班
        //查询出勤的人里面是不是迟到了
        //早退了
        //日报的：
        $admin_id = $_SESSION['think']['admin']['id'];
        
        $map['id']  = ['>',1];
        $map['status'] = "normal";
     
        $start_time = strtotime(date("Y-m-d 00:00:00",strtotime("-1 day")));
     
        $end_time = strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
        




        $approve_select = Db::name('approve')->field('id,admin_id,endtime,starttime')->where(['status' => "1",'starttime'=> ['<', $end_time], 'endtime'=>['>', $start_time]])->group('admin_id')->select();
        
    
        $qingren = array();
        if($approve_select){
            foreach ($approve_select as $qk=>$qv){
                $qingren[] = $qv['admin_id'];
            }
        }
        $qingren_str = "";
        $map2 = array();
        if(count($qingren)>0){
            $qingren_str = implode(",",$qingren);
            //$map2['id']  = array('neq',$qingren_str);
        }
        $map2['createtime']=array('<',$end_time);
        
        $admin_info = Db::name('admin')->field('id')->where($map)->where($map2)->select();
        $yinggaichuqin_str = "";
        foreach ($admin_info as $kes=>$vas){
            $yinggaichuqin_str .=$vas['id'].",";
        }
       
        
        $approve_count = count($approve_select);
        $shijichuqin_count = 0;  //实际出勤人数(查打了卡的人数)
        $chidaorenshu_count = 0;  //迟到人数
        $zaotuirenshu_count = 0;  //早退人数
        
        $dalexiabanka = 0;   //打了下班卡人数
        $weiribaorenshu_count =0;  //没有提日报人数
        
        $weidaxiabanka_count = 0; //没有打下班卡
        
        $kaoqinchouchaweidaka_count =0;  //考勤抽查未打卡人数
        
        if(count($qingren)>0){
            $qingren_str = implode(",",$qingren);
            $map2['id']  = array('neq',$qingren_str);
        }
        $admin_info2 = Db::name('admin')->field('id')->where($map)->where($map2)->select();
        
        $shangban_weidaka_str = "";
        
       
        foreach ($admin_info2 as $key=>$value) {
            $user_group_id = Db::name('auth_group_access')->where(['uid'=>$value['id']])->find();
            $attendance_user = Db::name('attendance a')->field('a.hobbydata')->join('kao_attendanceshift b','a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();
            if($attendance_user){
                 $now_week =  date('w',$start_time);
                 $how_week = $now_week; 
                 if($now_week==0){
                     $how_week=7;
                 }
                 $hobbydata = explode(",",$attendance_user['hobbydata']);
                //查看员工今天上班不
                 if(!in_array($how_week,$hobbydata)){
                    unset($admin_info[$key]);
                    unset($admin_info2[$key]);
                    continue;
                 }
            }
             
             
             
             
            
            //查看用户是不是今天休息：
            
            
            
             //查询日报信息：
             $ribao_info = Db::name('ribao')->field('id')->where(['admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->find();  
             if(!$ribao_info){
              
                  $weiribaorenshu_count +=1;
             }
           
             $daka_info = Db::name('daka')->field('createtime,ischidao,iszaotui,typelist')->where(['typelist'=>array('in','0,2'),'admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->select();  
             
             //查询打卡没有：
             $daka_info_daka = Db::name('daka')->field('createtime,ischidao,iszaotui,typelist,admin_id')->where(['typelist'=>array('in','0'),'admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->find(); 
             if(!$daka_info_daka){
                  $shangban_weidaka_str .=$value['id']."|";
                  
                  $chidaorenshu_count += 1;
             }else{
                // var_dump(Db::name('daka')->getlastsql());
             }
             //查询打下班卡没有：
             $xiaban_info_daka = Db::name('daka')->field('createtime,ischidao,iszaotui,typelist')->where(['typelist'=>array('in','2'),'admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->find(); 
             if(!$xiaban_info_daka){
                
                  $weidaxiabanka_count += 1;
             }
             
             if($daka_info){
                 foreach ($daka_info as $key2=>$value2){
                     if($value2['typelist'] == "0"){
                        //$shijichuqin_count += 1;
                          //查询是否迟到：
                        if($value2['ischidao'] == "1"){
                           $shangban_weidaka_str .= $value['id'].",";   
                           $chidaorenshu_count += 1;
                        }
                     }
                     
                     if($value2['typelist']=="2"){
                        
                           $dalexiabanka += 1;
                             //查询是否早退：
                           if($value2['iszaotui'] == "1" ){
                               $weidaxiabanka_count +=1;
                           }
                     }
                 }
                
   
             }
            
             //这里需要查询下总共需要当天抽查打卡多少次：
             //查询用户角色：kao_auth_group_access
           

             $attendance = Db::name('attendance a')->field('b.onechou,b.twochou,b.threechou,b.fourchou')->join('kao_attendanceshift b','a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();
             
             if($attendance){
                  $all_chou = $attendance['onechou']+$attendance['twochou']+$attendance['threechou']+$attendance['fourchou'];
             }else{
                  $all_chou = 0;
             }
             if($all_chou>0){
                 //这里查询用户是不是今天抽查打卡对应了那么多次：
              $daka_chouka = Db::name('daka')->field('createtime,ischidao,iszaotui,typelist')->where(['typelist'=>array('in','1'),'admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->count();  
                if($daka_chouka<$all_chou){
                  $kaoqinchouchaweidaka_count +=1;  
                }
             }
             
            
           
        }
        $approve_count = $approve_count;      //请假人数
        $yinggai_count = count($admin_info);  //应该出勤人数：
        
        $shijichuqin_count = $yinggai_count-$approve_count;
        
    
        $this->view->assign([
            'yinggai_count'=>$yinggai_count,//."==>".$yinggaichuqin_str                //需要打卡的人数  
            'shijichuqin_count'=>$shijichuqin_count,        //应出勤人数-请假人数
            'chidaorenshu_count'=>$chidaorenshu_count,//."==>".$shangban_weidaka_str      //未打卡+打卡迟到人数
            'weidaxiabanka_count'=>$weidaxiabanka_count,    //早退+没有打卡
            'kaoqinchouchaweidaka_count'=>$kaoqinchouchaweidaka_count,
            'approve_count'=>$approve_count,//."==>".$qingren_str
            'weiribaorenshu_count'=>$weiribaorenshu_count,
            'admin_id'=>$admin_id
        ]);


        return $this->view->fetch();
    }
   

}
