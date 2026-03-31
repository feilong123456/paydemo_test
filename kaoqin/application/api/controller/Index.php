<?php

namespace app\api\controller;

use think\Db;
use app\common\controller\Api;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    public function kaoqinadmin(){
        
    }
    /**
     * 首页
     *
     */
    public function index()
    {
        //打卡查询：
        $year = $this->request->get('year');
        $month = $this->request->get('month');
        $admin_id = $this->request->get('admin_id');
        $daka = Db::name('daka')->where(['admin_id'=>$admin_id,'year'=>$year,'month'=>$month,'isqueqin'=>1])->select();
        $this->success("查询成功",$daka);
    }
    public function userinfo()
    {
        //打卡查询：
        $year = $this->request->get('year');
        $month = $this->request->get('month');
        $admin_id = $this->request->get('admin_id');
        $daka = Db::name('daka')->where(['admin_id'=>$admin_id,'year'=>$year,'month'=>$month,'ischidao'=>"1"])->select();
        $this->success("查询成功",$daka);

    }
    public function changuserinfo()
    {
        $daka_id = $this->request->get('daka_id');
        $dakatime = $this->request->get('dakatime');
        $createtime = $this->request->get('createtime');
        $update_data = array(
            'dakatime'=>$dakatime,
            'createtime'=>$createtime,
            'ischidao'=>"0",
            'isqueqin'=>"0",
        );
        Db::name('daka')->where(['id'=>$daka_id])->update($update_data);
    }

    public function userchouka()
    {
        $year = $this->request->get('year');
        $month = $this->request->get('month');
        $admin_id = $this->request->get('admin_id');
        $daka = Db::name('chouka')->where(['admin_id'=>$admin_id,'year'=>$year,'month'=>$month,'statuslist'=>"1"])->find();
        $this->success("200",$daka);
    }
    public function changuserchouka()
    {
        $chouka_id = $this->request->get('chouka_id');
        $dakatime = $this->request->get('dakatime');
        $update_data = array(
            'dakatime'=>$dakatime,
            'statuslist'=>"1",
        );
        Db::name('chouka')->where(['id'=>$chouka_id])->update($update_data);
    }

    public function getWorkingDays($year, $month,$hobbydata=array()) {

        $count = 0;
        // 获取该月的天数
        $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
        //获取今天是第几天：
        $jinri = date("j");



        for ($day = 1; $day < $jinri; $day++) {
            $timestamp = mktime(0, 0, 0, $month, $day, $year);
            $weekDay = date('w', $timestamp);
            if($weekDay==0){
                $weekDay=7;
             }
             if(!empty($hobbydata)){
                 if(in_array($weekDay,$hobbydata)){
                     $count++;
                 }
             }else{
                 $count++;
             }



        }
        return $count;
    }

    /**
     * 月报逻辑
     */
    public function yuebao()
    {

     $year = date('Y'); // 当前年份
     $month = date('m'); // 当前月份



        //固定考勤时间：
        $year = date('Y');
        $month = date('m');
        $workingDays =$this->getWorkingDays($year, $month);
        $start_time = strtotime(date('Y-m-01', strtotime (date ("Y-m-d"))));  //月初
        //$end_time =   strtotime(date('Y-m-d', strtotime (date ('Y-m-01', strtotime (date ("Y-m-d"))) . "+1 month -1 day")));  //月末
        //$daka_endtime =  strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
        $end_time =  strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
        $map['id']  = ['>',1];
        $map['status'] = "normal";
        //$map['id'] = "6";
        $admin_info = Db::name('admin')->field('id')->where($map)->select();

        foreach ($admin_info as $key=>$value){

            //查询迟到总次数：
            $chouka = Db::name('chouka')->where(['admin_id'=>$value['id'],'year'=>$year,'month'=>$month,'istuisonglist'=>"1",'statuslist'=>array('in',array('0','2')),'chouendtime'=>array('<',$end_time)])->count();
            if($chouka){
                $chouka_count = $chouka;
            }else{
                $chouka_count = "0";
            }

            $user_group_id = Db::name('auth_group_access')->where(['uid'=>$value['id']])->find();
            $attendance_user = Db::name('attendance a')->field('a.hobbydata')->join('kao_attendanceshift b','a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();
            if($attendance_user){
                 $now_week =  date('w',time());
                 $how_week = $now_week;
                 if($now_week==0){
                     $how_week=7;
                 }
                 $hobbydata = explode(",",$attendance_user['hobbydata']);
                //查看员工今天上班不
                 $workingDays =$this->getWorkingDays($year, $month,$hobbydata);

            }
            $needday = $workingDays;

            //查询是不是存在这个年-月的日报，否则就删除后再新增：
            $find_yuebao_where = array(
                'admin_id'=>$value['id'],
                'year'=>$year,
                'month'=>$month,

            );
            $find_yuebao_info = Db::name('yuebao')->field('id')->where($find_yuebao_where)->find();
            if($find_yuebao_info){
                Db::name('yuebao')->where(['id'=>$find_yuebao_info['id']])->delete();
            }

            //查询实际出勤：
            $shijichuqin =0;
            //查询迟到天数：
            $shijichidao =0;
            //查询抽到迟到天数：
            $shijichoukachidao =0;
            //早退天数：
            $shijizaotui =0;


            //查询未抽卡次数：
            $weichoucha_num = Db::name('chouka')->where(['statuslist'=>"0",'admin_id'=>$value['id'],'choukatime'=>array('between',array($start_time,$end_time))])->count();

            //查询考勤信息：
            $daka_info = Db::name('daka')->where(['admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->select();

            //打了多少次的成功，且没有迟到的上班卡
            $shangban_daka_info = Db::name('daka')->where(['typelist'=>"0",'ischidao'=>"0",'admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->count();

            $chidao_num = $needday-$shangban_daka_info;

            //打了多少次成功的，且没有早退的下班卡：
            $xiaban_daka_info = Db::name('daka')->where(['typelist'=>"2",'iszaotui'=>"0",'admin_id'=>$value['id'],'createtime'=>array('between',array($start_time,$end_time))])->count();

            $zaotui_num = $needday-$xiaban_daka_info;

            if($daka_info){

                foreach($daka_info as $kakey=>$kavalue){
                    //迟到：
                    if($kavalue['typelist'] =="0" && $kavalue['ischidao'] =="1"){
                        $shijichidao +=1;
                    }
                    //抽卡迟到：
                    if($kavalue['typelist'] =="1" && $kavalue['ischidao'] =="1"){
                        $shijichoukachidao +=1;
                    }
                    //早退：
                    if($kavalue['typelist'] =="2" && $kavalue['iszaotui'] =="1"){
                        $shijizaotui +=1;
                    }
                }
            }

            //请假次数：
            $all_qingjiacishu = 0;

            $all_qingjiatianshu = "";
            //请假天数：
            $approve_select = Db::name('approve')->field('id,admin_id,endtime,starttime')->where(['admin_id'=>$value['id'],'status' => "1",'starttime'=> ['>', $start_time], 'endtime'=>['<', $end_time]])->select();


            $all_qingjia = 0;

            if($approve_select){
                $all_qingjiacishu = count($approve_select);
                foreach($approve_select as $kes=>$vas){
                    $all_qingjia +=$vas['endtime']-$vas['starttime'];
                }
                if($all_qingjia>0){
                    $all_qingjiatianshu = $this->secondsToTime($all_qingjia);
                }
            }
            if($all_qingjia>0){
               $all_qingjiatianshu_qingjia = $this->secondsToTime2($all_qingjia);
            }else{
                $all_qingjiatianshu_qingjia = 0;
            }

            $shijichuqin =$needday-$all_qingjiatianshu_qingjia;


            $add_data = array(
                'admin_id'=>$value['id'],
                'year'=>$year,
                'month'=>$month,
                'needday'=>$needday,
                'shijiday'=>$shijichuqin,
                'chidaoday'=>$chidao_num-$all_qingjiatianshu_qingjia,
                'choukaday'=>$chouka_count,
                'zaotuiday'=>$zaotui_num-$all_qingjiatianshu_qingjia,
                'qingjiaci'=>$all_qingjiacishu,
                'qingjiaday'=>$all_qingjiatianshu,
                'weidakanum'=>$weichoucha_num, //未打卡次数
                'remake'=>"",
                "createtime"=>time()
            );


            Db::name('yuebao')->insert($add_data);
        }

        $msg = $year."-".$month."月报申请成功！";
        $this->success($msg);
    }



    /**
     * 给每个人分配抽查打卡数据信息：
     *
     */

    public function chouka(){
        $map['id']  = ['>',1];
        $map['status'] = "normal";

        $admin_info2 = Db::name('admin')->field('id')->where($map)->select();

        $shangban_weidaka_str = "";


        foreach ($admin_info2 as $key=>$value) {
            $admin_id = $value['id'];
            $find_where2 = array(
               'admin_id'=>$admin_id,

            );

            $user_group_id = Db::name('auth_group_access')->where(['uid'=>$value['id']])->find();
            $attendance_user = Db::name('attendance a')->field('a.hobbydata,a.dates,b.*')->join('kao_attendanceshift b','a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();

            if($attendance_user){
                 $now_week =  date('w',time());
                 $how_week = $now_week;
                 if($now_week==0){
                     $how_week=7;
                 }
                 $hobbydata = explode(",",$attendance_user['hobbydata']);
                  if(!in_array($how_week,$hobbydata)){
                     //今天需要上班的人，需要抽查打卡的：

                      //这个人不需要抽查打卡
                     unset($admin_info2[$key]);
                     continue;
                 }
                 //是不是今天休息了：
                  $start_time = strtotime(date("Y-m-d 00:00:00"));

                  $end_time = strtotime(date("Y-m-d 23:59:59"));

                  $approve_find = Db::name('approve')->where(['status' => "1",'starttime'=> ['<', $end_time], 'endtime'=>['>', $start_time],'admin_id'=>$value['id']])->find();
                  if($approve_find){
                      unset($admin_info2[$key]);
                      continue;
                  }
                 //查看今日是不是公休日:
                  if(!empty($attendance_user['dates'])){
                      $dates_arr = explode(",",$attendance_user['dates']);
                      if(count($dates_arr)>0){
                          for($i=0;$i<count($dates_arr);$i++){
                              $dates_arr[$i] = trim($dates_arr[$i]);//去掉空格
                          }
                      }
                      //查询今日是不是公休：
                    //   if(in_array($jinri,$dates_arr)){
                    //      unset($admin_info2[$key]);
                    //      continue;
                    //   }
                  }

                 //根据用户需要抽卡多次生成对应的打卡时间：
                 $timedraw = $attendance_user['timedraw'];

                 $choukajian = $attendance_user['choukajian'];
                 $isadvance = $attendance_user['isadvance'];
                 $year = date('Y');
                 $month = date('m');
                 $day = date('d');

                //是不是存在了：
                $find_where = array(
                  'admin_id'=>$admin_id,
                  'year'=>$year,
                  'month'=>$month,
                  'day'=>$day,
                  //'jilunlist'=>$jilunlist,
                  //'dijige'=>$kes+1,
                //   'istuisonglist'=>"0"
                );
                $find_result = Db::name('chouka')->where($find_where)->find();
               if($find_result){
                    Db::name('chouka')->where($find_where)->delete();
                }

                 //第一轮：
                 if($attendance_user['onechou']>0){
                     $start_time = $attendance_user['onestarttime'];
                     $end_time = $attendance_user['oneendtime'];
                     $jilunlist = "1";
                     $num_of_times = $attendance_user['onechou'];
                     $diyilun_result = $this->shijianda($start_time,$end_time,$num_of_times,$year,$month,$day,$jilunlist,$timedraw,$admin_id,$choukajian,$isadvance);

                 }
                 //第二轮：
                 if($attendance_user['twochou']>0){
                     $start_time = $attendance_user['twostarttime'];
                     $end_time = $attendance_user['twoendtime'];
                     $jilunlist = "2";
                     $num_of_times = $attendance_user['twochou'];
                     $diyilun_result = $this->shijianda($start_time,$end_time,$num_of_times,$year,$month,$day,$jilunlist,$timedraw,$admin_id,$choukajian,$isadvance);

                 }
                 //第三轮：
                 if($attendance_user['threechou']>0){
                     $start_time = $attendance_user['threestarttime'];
                     $end_time = $attendance_user['threeendtime'];
                     $jilunlist = "3";
                     $num_of_times = $attendance_user['threechou'];
                     $diyilun_result = $this->shijianda($start_time,$end_time,$num_of_times,$year,$month,$day,$jilunlist,$timedraw,$admin_id,$choukajian,$isadvance);
                 }
                 //第四轮：
                 if($attendance_user['fourchou']>0){
                     $start_time = $attendance_user['fourstarttime'];
                     $end_time = $attendance_user['fourendtime'];
                     $jilunlist = "4";
                     $num_of_times = $attendance_user['fourchou'];
                     $diyilun_result = $this->shijianda($start_time,$end_time,$num_of_times,$year,$month,$day,$jilunlist,$timedraw,$admin_id,$choukajian,$isadvance);
                 }


            }else{
                //这个人不需要抽查打卡
                unset($admin_info2[$key]);
            }
        }
        $msg = $year."-".$month."-".$day."抽卡数据生成成功！";
        $this->success($msg);
    }
    protected function generateRandomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }


    protected function shijianda($start_time,$end_time,$num_of_times,$year,$month,$day,$jilunlist,$timedraw,$admin_id,$choukajian,$isadvance){
        $start_time = strtotime(date("Y-m-d"). " ".date("H:i:s",$start_time));
        $end_time = strtotime(date("Y-m-d"). " ".date("H:i:s",$end_time));
        // 生成随机时间点
        $one_start_time =0;
        $qujian2= $end_time-$start_time;
        $cishu2 = intval($qujian2/$num_of_times);
        $end_time = $end_time-($cishu2);
        //提前区分成对应几个时间区间：
        $time_qujian = array();

        $qujian = $end_time-$start_time;
        $cishu = intval($qujian/$num_of_times);

        for ($i = 0; $i < $num_of_times; $i++) {
            if($i==0){
                 $one_start_time = $start_time;
            }else{
                $one_start_time = $start_time+($cishu*($i+1));
            }

            $one_end_time = $one_start_time+$cishu;
            $time_qujian[] = array($one_start_time,$one_end_time);
        }

       // exit();
        foreach($time_qujian as $kes=>$ves){

          $new_starttime = $ves[0];
          $new_end_time =  $ves[1];


          $random_timestamp = mt_rand($new_starttime, $new_end_time);

            $add_chouka = array(
                 'tuisongtime'=>$random_timestamp-(60*$isadvance),
                  'choukatime'=> $random_timestamp,
                  'chouendtime'=> $random_timestamp+(60*$timedraw),
                  'admin_id'=>$admin_id,
                  'dakatime'=>0,
                  'dakalist'=>"2",
                  'year'=>$year,
                  'month'=>$month,
                  'day'=>$day,
                  'jilunlist'=>$jilunlist,
                  'dijige'=>$kes+1,
                  'token'=>$this->generateRandomString()
            );
            Db::name('chouka')->insert($add_chouka);
        }
        return true;
    }
     /**
         * 将秒进行格式化
         *@param  $inputSeconds  秒数
         *@return array
         */
     protected  function secondsToTime($inputSeconds) {
            $secondsInAMinute = 60;
            $secondsInAnHour  = 60 * $secondsInAMinute;
            $secondsInADay    = 24 * $secondsInAnHour;

            // extract days
            $days = floor($inputSeconds / $secondsInADay);

            // extract hours
            $hourSeconds = $inputSeconds % $secondsInADay;
            $hours = floor($hourSeconds / $secondsInAnHour);

            // extract minutes
            $minuteSeconds = $hourSeconds % $secondsInAnHour;
            $minutes = floor($minuteSeconds / $secondsInAMinute);

            // extract the remaining seconds
            $remainingSeconds = $minuteSeconds % $secondsInAMinute;
            $seconds = ceil($remainingSeconds);

            // return the final array
            $obj = array(
                'd' => (int) $days,
                'h' => (int) $hours,
                'm' => (int) $minutes,
                's' => (int) $seconds,
            );
            return $days."天".$hours."小时".$minutes."分钟".$seconds."秒";
        }
     protected  function secondsToTime2($inputSeconds) {
            $secondsInAMinute = 60;
            $secondsInAnHour  = 60 * $secondsInAMinute;
            $secondsInADay    = 24 * $secondsInAnHour;

            // extract days
            $days = floor($inputSeconds / $secondsInADay);

            // extract hours
            $hourSeconds = $inputSeconds % $secondsInADay;
            $hours = floor($hourSeconds / $secondsInAnHour);

            // extract minutes
            $minuteSeconds = $hourSeconds % $secondsInAnHour;
            $minutes = floor($minuteSeconds / $secondsInAMinute);

            // extract the remaining seconds
            $remainingSeconds = $minuteSeconds % $secondsInAMinute;
            $seconds = ceil($remainingSeconds);

            // return the final array
            $obj = array(
                'd' => (int) $days,
                'h' => (int) $hours,
                'm' => (int) $minutes,
                's' => (int) $seconds,
            );
            if($days>0){
                if($hours>0){
                    return $days+1;
                }else{
                    return $days;
                }
            }else{
                if($hours>0){
                    return 1;
                }
            }

        }

}
