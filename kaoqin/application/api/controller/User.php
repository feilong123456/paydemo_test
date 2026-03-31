<?php

namespace app\api\controller;

use think\Db;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Config;
use think\Env;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['userinfo','login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'chouchadaka', 'naozhong', 'apidaka', 'teams_callback', 'teams_bind'];
    protected $noNeedRight = '*';
    private $link = "";

    private $PUSHOVER_API_URL = "https://api.pushover.net/1/messages.json";
    private $PUSHOVER_APP_TOKEN = "a2huogdbbak99peo8iae61k25jkw25";
    private $PUSHOVER_USER_KEY = "uw6ixrfr5m7zdkw7pzf5f4wgf1fqau";

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
        $token = Env::get('token.api_token', '6908898078:AAF7Vg4Fs-7zzCMd9aIsuU7AxfsVwdnpgmA');

        $this->link = 'https://api.telegram.org/bot' . $token;

    }

    public function userinfo(){
        $user_info = Db::name('admin')->where(['id' => array('>', '1')])->select();
        return $user_info;

    }

    /**
     * 发送 Pushover 通知
     *
     * @param string $message 消息内容
     * @param string $title 消息标题 (可选)
     * @param int $priority 优先级 0=正常, 1=高, 2=紧急 (可选)
     * @param string $sound 声音类型 (可选)
     * @param string $url 点击链接 (可选)
     * @param string $urlTitle 链接标题 (可选)
     * @return array|false 成功返回响应数组，失败返回 false
     */
    function sendPushoverNotification($user_key, $message, $title = '系统通知', $priority = 0, $sound = 'pushover', $url = '', $urlTitle = '')
    {
        // 构建请求参数
        $params = [
            'token' => $this->PUSHOVER_APP_TOKEN,
            'user' => $user_key,
            'message' => $message,
            'title' => $title,
            'priority' => $priority,
            'sound' => $sound
        ];

        // 添加可选参数
        if (!empty($url)) {
            $params['url'] = $url;
        }
        if (!empty($urlTitle)) {
            $params['url_title'] = $urlTitle;
        }

        // 发送请求
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->PUSHOVER_API_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 检查请求是否成功
        if ($response === false || $httpCode !== 200) {
            return false;
        }

        $result = json_decode($response, true);
        return $result;
    }

    /**
     * 发送简单通知
     *
     * @param string $message 消息内容
     * @param string $title 消息标题
     * @return array|false 成功返回响应数组，失败返回 false
     */
    function sendSimplePushover($user_key, $message, $title = '系统通知')
    {
        return $this->sendPushoverNotification($user_key, $message, $title);
    }

    /**
     * 发送重要通知
     *
     * @param string $message 消息内容
     * @param string $title 消息标题
     * @return array|false 成功返回响应数组，失败返回 false
     */
    function sendImportantPushover($user_key, $message, $title = '重要通知')
    {
        return $this->sendPushoverNotification($user_key, $message, $title, 1, 'siren');
    }

    /**
     * 发送紧急通知
     *
     * @param string $message 消息内容
     * @param string $title 消息标题
     * @return array|false 成功返回响应数组，失败返回 false
     */
    function sendEmergencyPushover($user_key, $message, $title = '紧急通知')
    {
        return $this->sendPushoverNotification($user_key, $message, $title, 2, 'siren');
    }

    /**
     * 发送带链接的通知
     *
     * @param string $message 消息内容
     * @param string $url 链接地址
     * @param string $urlTitle 链接标题
     * @param string $title 消息标题
     * @return array|false 成功返回响应数组，失败返回 false
     */
    function sendPushoverWithLink($user_key, $message, $url, $urlTitle, $title = '系统通知')
    {
        return $this->sendPushoverNotification($user_key, $message, $title, 0, 'pushover', $url, $urlTitle);
    }

    /**
     * 推送消息到 Teams（后台定时/抽查时调用 Bot 的 /api/notify）
     * @param array $admin_row 后台员工行，需含 teams_conversation_id, teams_service_url, teams_tenant_id
     * @param string $message 正文
     * @param array|null $card 可选卡片 { title, text, buttons: [{ title, value?|url? }] }
     */
    protected function pushToTeams($admin_row, $message, $card = null, &$error = null)
    {
        $conversationId = isset($admin_row['teams_conversation_id']) ? $admin_row['teams_conversation_id'] : '';
        $serviceUrl = isset($admin_row['teams_service_url']) ? $admin_row['teams_service_url'] : '';
        if (empty($conversationId) || empty($serviceUrl)) {
            $error = [
                'reason' => 'missing_conversation_or_service_url',
                'conversationId' => $conversationId,
                'serviceUrl' => $serviceUrl,
            ];
            return false;
        }
        // 优先读取 [teams] 段配置，兼容旧的 [team] 段配置
        //测试：https://kaoqin_team_bot.kwmv.top  正式：http://kaoqin_team_bot.pzcyhxprds.top
        $botUrl = \think\Env::get('teams.bot_service_url', \think\Env::get('team.bot_service_url', 'https://kaoqin_team_bot.kwmv.top'));
        $payload = [
            'conversationId' => $conversationId,
            'serviceUrl' => $serviceUrl,
            'tenantId' => isset($admin_row['teams_tenant_id']) ? $admin_row['teams_tenant_id'] : '',
            'message' => $message,
            'title' => isset($card['title']) ? $card['title'] : '考勤通知',
        ];
        if (!empty($card)) {
            $payload['card'] = $card;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rtrim($botUrl, '/') . '/api/notify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $error = [
            'code' => $code,
            'response' => $response,
            'botUrl' => $botUrl,
            'payload' => $payload,
        ];
        \think\Log::info("Teams 推送 result: code={$code}, response=" . substr($response, 0, 200));
        return ($code >= 200 && $code < 300);
    }

    /**
     * 会员中心
     */
    public function index()
    {

        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    //主动给机器人发消息：
    public function naozhong()
    {
        // 添加日志记录，确认方法被调用
        $current_time = time();
        $current_datetime = date('Y-m-d H:i:s', $current_time);
        \think\Log::info("=== naozhong方法开始执行 ===");
        \think\Log::info("当前时间戳：{$current_time}，格式化时间：{$current_datetime}");

        $user_info = Db::name('admin')->where(['id' => array('>', '1')])->select();
        \think\Log::info("查询到用户数量：" . count($user_info));


        $year = date('Y');

        $month = date('m');
        $day = date('d');
        \think\Log::info("查询条件 - 年：{$year}，月：{$month}，日：{$day}，当前时间戳：{$current_time}");

        $total_push_count = 0; // 总推送数量
        $total_user_count = 0; // 处理用户数量

        foreach ($user_info as $key => $value) {
            $total_user_count++;
            \think\Log::info("处理用户 ID：{$value['id']}，用户名：{$value['username']}，chatid：{$value['chatid']}");

            $parameter = array(
                'chat_id' => "",
                'parse_mode' => 'HTML',
                'text' => "",
            );

            //查看用户是不是需要抽查打卡：
            $chouka_info = Db::name('chouka')
                ->where([
                    'admin_id' => $value['id'],
                    'istuisonglist' => "0",
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'tuisongtime' => array('<=', $current_time)
                ])
                ->select();

            \think\Log::info("用户 {$value['id']} 查询到的抽卡记录数量：" . count($chouka_info));
            \think\Log::info("用户 {$value['id']} 执行的sql：" . Db::name('chouka')->getLastSql());

            if ($chouka_info) {
                foreach ($chouka_info as $ckey => $cvalue) {
                    $total_push_count++;
                    \think\Log::info("=== 开始处理抽卡记录 ===");
                    \think\Log::info("抽卡记录ID：{$cvalue['id']}");
                    \think\Log::info("推送时间戳：{$cvalue['tuisongtime']}，格式化：".date('Y-m-d H:i:s', $cvalue['tuisongtime']));
                    \think\Log::info("抽卡时间戳：{$cvalue['choukatime']}，格式化：".date('Y-m-d H:i:s', $cvalue['choukatime']));
                    \think\Log::info("抽卡结束时间戳：{$cvalue['chouendtime']}，格式化：".date('Y-m-d H:i:s', $cvalue['chouendtime']));
                    \think\Log::info("用户抽卡方式设置：{$value['choukadata']}");

                    $baseUrl = preg_replace('/\?.*$/', '', Env::get('token.api_url', ''));
                    $url = $baseUrl . "?choukaid=" . $cvalue['id'] . "&token=" . $cvalue['token'];
                    $choukatime = date("H:i:s", $cvalue['choukatime']);
                    \think\Log::info("生成的抽卡链接：{$url}");
                    \think\Log::info("抽卡时间格式化：{$choukatime}");


                    if($value['username']=="xiaowu" || $value['id']=="3"){
                        if ($value['choukadata'] == "1") {
                            \think\Log::info("使用Web打卡方式推送");

                            $inline_keyboard_arr2 = array(
                                array('text' => "跳转web打抽查卡", "url" => $url),
                            );
                            $keyboard = [
                                'inline_keyboard' => [

                                    $inline_keyboard_arr2,
                                ]
                            ];
                            $parameter = array(
                                'chat_id' => $value['chatid'],
                                'parse_mode' => 'HTML',
                                'text' => "请在 " . $choukatime . " 之前点击下方按钮，跳转到web打卡" . ":",
                                'reply_markup' => $keyboard,
                                'disable_web_page_preview' => true,
                            );

                            \think\Log::info("发送Telegram消息参数：" . json_encode($parameter));
                            $resutl = $this->sendTelegramWithRetry('sendMessage', $parameter);
                            $tg_ok = (is_array($resutl) && isset($resutl['ok']) && $resutl['ok'] === true);

                        } elseif ($value['choukadata'] == "0") {
                            \think\Log::info("使用TG打卡方式推送");

                            $inline_keyboard_arr2 = array(
                                array('text' => "立即打抽查卡", "callback_data" => "chouchaka###" . $cvalue['id']),
                            );
                            $keyboard = [
                                'inline_keyboard' => [

                                    $inline_keyboard_arr2,
                                ]
                            ];
                            $parameter = array(
                                'chat_id' => $value['chatid'],
                                'parse_mode' => 'HTML',
                                'text' => "TG抽查打卡" . ":" . $choukatime,
                                'reply_markup' => $keyboard,
                                'disable_web_page_preview' => true,
                            );

                            \think\Log::info("发送Telegram消息参数：" . json_encode($parameter));
                            $resutl = $this->sendTelegramWithRetry('sendMessage', $parameter);
                            $tg_ok = (is_array($resutl) && isset($resutl['ok']) && $resutl['ok'] === true);
                        } else {
                            \think\Log::info("使用默认打卡方式推送");

                            $inline_keyboard_arr2 = array(
                                array('text' => "立即打抽查卡", "callback_data" => "chouchaka###" . $cvalue['id']),
                            );
                            $keyboard = [
                                'inline_keyboard' => [

                                    $inline_keyboard_arr2,
                                ]
                            ];
                            $parameter = array(
                                'chat_id' => $value['chatid'],
                                'parse_mode' => 'HTML',
                                'text' => '<a href="' . $url . '">' . "TG抽查打卡" . ":" . $choukatime . '</a>',
                                'reply_markup' => $keyboard,
                                'disable_web_page_preview' => true,
                            );

                            \think\Log::info("发送Telegram消息参数：" . json_encode($parameter));
                            $resutl = $this->http_post_data('sendMessage', json_encode($parameter));
                            \think\Log::info("Telegram API返回结果：" . json_encode($resutl));
                            $resutl_decoded = json_decode($resutl, true);
                            $tg_ok = (is_array($resutl_decoded) && isset($resutl_decoded['ok']) && $resutl_decoded['ok'] === true);
                        } 
                    }

                    $teams_ok = false;
                    // 若该员工已绑定 Teams，同时推送到 Teams
                    if (!empty($value['teams_conversation_id']) && !empty($value['teams_service_url'])) {
                        
                        $teamsMsg = "请在 " . $choukatime . " 之前完成抽查打卡。";
                        $teamsCard = [
                            'title' => '抽查打卡',
                            'text' => $teamsMsg . " 抽卡时间：" . $choukatime,
                            'buttons' => [],
                        ];
                        if ($value['choukadata'] == "1") {
                            $teamsCard['buttons'][] = ['title' => '跳转web打抽查卡', 'url' => $url];
                        } else {
                            $teamsCard['buttons'][] = ['title' => '立即打抽查卡', 'value' => 'chouchaka###' . $cvalue['id']];
                        }
                        $teamsError = null;
                        $teams_ok = $this->pushToTeams($value, $teamsMsg, $teamsCard, $teamsError);
                        // var_dump([
                        //     'teams_ok'   => $teams_ok,
                        //     'teamsError' => $teamsError,
                        // ]);
                    }

                    if (!empty($value['userkey'])) {
                        $message = "有一个临近的抽卡信息，请去tg查看确定！抽卡时间:" . $choukatime.",抽卡链接：".$url ;
                        $user_key = $value['userkey'];
                        \think\Log::info("用户有Pushover配置，userkey：{$user_key}，消息：{$message}");
                        //$this->sendSimplePushover($user_key,$message, $title = '你有一个需要抽卡的信息通知');
                        $result = $this->sendImportantPushover($user_key,$message, $title = '你有一个需要抽卡的信息通知');
                        
                    } else {
                        \think\Log::info("用户没有配置Pushover userkey");
                    }

                    // 仅当至少一种渠道（TG 或 Teams）推送成功时才标记为已推送，否则下次定时任务会重试
                    $push_success = (isset($tg_ok) && $tg_ok) || $teams_ok;
                    if ($push_success) {
                        $update_result = Db::name('chouka')->where(['id' => $cvalue['id']])->update(['istuisonglist' => "1"]);
                        \think\Log::info("更新抽卡记录推送状态结果：" . ($update_result ? '成功' : '失败'));
                    } else {
                        \think\Log::info("TG/Teams 均未推送成功，不更新推送状态，下次将重试");
                    }
                    \think\Log::info("=== 抽卡记录处理完成 ===");
                }
            } else {
                \think\Log::info("用户 {$value['id']} 没有需要推送的抽卡记录");
            }
        }

        \think\Log::info("=== naozhong方法执行完成 ===");
        \think\Log::info("总共处理用户数量：{$total_user_count}");
        \think\Log::info("总共推送消息数量：{$total_push_count}");



        
    }

    public function chouchadaka()
    {
        \think\Log::error('进来了 ');
        $data = json_decode(file_get_contents('php://input'), TRUE); //读取json并对其格式化

        // 检查JSON解析是否成功
        if (json_last_error() !== JSON_ERROR_NONE) {
            \think\Log::error('JSON解析错误: ' . json_last_error_msg());
            $this->error('JSON解析失败');
        }

        \think\Log::error('收到的消息： ' .json_encode($data));
        Db::name('tgrizhi')->insert(['log'=>json_encode($data)]);

        // 检查数据结构
        if (!is_array($data)) {
            \think\Log::error('数据格式错误，不是数组');
            $this->error('数据格式错误');
        }

        if(array_key_exists("callback_query",$data)){
            $this->huidiaobacks($data);
            exit();
        }

        // 检查message结构是否存在
        if (!isset($data['message']) || !is_array($data['message'])) {
            \think\Log::error('message结构不存在或格式错误');
            $this->error('message结构错误');
        }

        // 检查必要的字段是否存在
        if (!isset($data['message']['chat']['id']) ||
            !isset($data['message']['from']['id']) ||
            !isset($data['message']['from']['username'])) {
            \think\Log::error('必要字段缺失');
            $this->error('必要字段缺失');
        }

        $chatid = $data['message']['chat']['id'];//获取chatid
        $userid = $data['message']['from']['id'];//获取userid
        $username = $data['message']['from']['username'];//用户名称


        // 检查text字段是否存在，如果不存在则跳过文本处理
        if (!isset($data['message']['text'])) {
            \think\Log::error('text字段不存在，跳过文本处理');
            $this->success('处理完成');
        }

        $message = $data['message']['text'];//获取message

        if (strpos($message, '/start') !== false) {
            $this->starthou($chatid,$userid,$message);
        }

        if (strpos($message, '上班打卡') !== false) {
            //5177985370
            if($userid !="5177985370"){
                $this->xiaoxi("禁止tg打卡",$chatid);
                exit();
            }
            $quanxian = "上班打卡";
            $user_info = $this->quanxian($chatid, $userid, $quanxian,$username);
            $this->liangcijiaoyan($quanxian,$chatid);
            //$this->zhixingdaka($chatid, $userid,$user_info,$quanxian);
        }

        if (strpos($message, '下班打卡') !== false) {
            //5177985370
            if($userid !="5177985370"){
                $this->xiaoxi("禁止tg打卡",$chatid);
                exit();
            }
            $quanxian = "下班打卡";
            $user_info = $this->quanxian($chatid, $userid, $quanxian,$username);
            $this->liangcijiaoyan($quanxian,$chatid);
            //$this->zhixingdaka($chatid, $userid,$user_info,$quanxian);
        }

        $this->success('处理完成');
    }

    protected function liangcijiaoyan($quanxian, $chatid)
    {
        if ($quanxian == "上班打卡") {
            $tx = "立即打上班卡";
        } else {
            $tx = "立即打下班卡";
        }
        $inline_keyboard_arr2 = array(
            array('text' => $tx, "callback_data" => "quedingdakaba###" . $quanxian),
        );
        $keyboard = [
            'inline_keyboard' => [

                $inline_keyboard_arr2,
            ]
        ];
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => '打卡确认:  🔵  <b>' . $quanxian . "</b>  🔵  ,当前系统时间为：🐸<b>" . date("Y-m-d H:i:s") . "</b>🐸",
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true,
        );

        $this->http_post_data('sendMessage', json_encode($parameter));
    }

    protected function zhixingdaka($chatid, $userid, $user_info, $quanxian)
    {
        //查询今天是否打卡，今天是否上班，今天是否休息了：

        //今日请假：
        $this->qingjia($chatid, $userid, $user_info);
        //是不是需要今日打卡：
        $this->xuyaoshangban($chatid, $userid, $user_info);
        if ($quanxian == "上班打卡") {
            $dakatype = "0";
        } else {
            $dakatype = "2";
        }

        //判断是否已经打卡过
        $this->yijingchuqin($chatid, $userid, $user_info, $dakatype);
        //执行打卡：
        $this->zailaiquedingdaka($chatid, $userid, $user_info, $dakatype);


    }

    //执行打卡：
    protected function zailaiquedingdaka($chatid, $userid, $user_info, $dakatype)
    {

        $params = array();
        $params['admin_id'] = $user_info['id'];
        $params['dakatime'] = strtotime(date("Y-m-d"));
        $params['createtime'] = time();
        $params['typelist'] = $dakatype;
        $params['year'] = date("Y");
        $params['month'] = date("m");
        $params['iszaotui'] = "0";
        $params['isqueqin'] = "0";
        $params['ischidao'] = "0";

        //这里需要判断用户是不是迟到了：先查询用户所在的考勤组：
        $user_group_id = Db::name('auth_group_access')->where(['uid' => $user_info['id']])->find();

        $attendance = Db::name('attendance a')->field('b.*')->join('kao_attendanceshift b', 'a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();

        if (!$attendance) {
            $this->xiaoxi("你还没有设置对应的考勤组跟班次,需要联系老板楚歌！", $chatid);
        }
        $now_dakatime = time();

        if ($dakatype == "0") {
            //不能超过多少时间打卡：
            $zuizaodaka_time = strtotime(date("Y-m-d") . " " . date("H:i:s", $attendance['starttime'])) - 60 * $attendance['startbefore'];

            if ($zuizaodaka_time > $now_dakatime) {
                $this->xiaoxi("最早打卡时间为：" . date("H:i:s", $zuizaodaka_time), $chatid);
            }
            //最多可以迟到 到什么时候：
            $zuiduochidao = strtotime(date("Y-m-d") . " " . date("H:i:s", $attendance['starttime'])) + 60 * $attendance['startmiss'];

            if ($now_dakatime > $zuiduochidao) {
                $params['ischidao'] = "1";
            }
            if ($params['ischidao'] == "1") {
                $text = "上班打卡成功，并且很严肃的告知你：你迟到了！";
            } else {
                $text = "上班打卡成功!";
            }
        } else {
            //不能超过多少时间打卡：
            $zuizaodaka_time = strtotime(date("Y-m-d") . " " . date("H:i:s", $attendance['endtime'])) - 60 * $attendance['endbefore'];
            if ($zuizaodaka_time > $now_dakatime) {
                $this->xiaoxi("最早打下班卡时间为：" . date("H:i:s", $zuizaodaka_time), $chatid);
            }
            //最多可以早退 到什么时候：
            $zuiduozaotui = strtotime(date("Y-m-d") . " " . date("H:i:s", $attendance['endtime'])) - 60 * $attendance['endmiss'];
            if ($zuiduozaotui > $now_dakatime) {
                $params['iszaotui'] = "1";
            }

            if ($params['iszaotui'] == "1") {
                $text = "下班打卡成功，并且很严肃的告知你：你这属于早退了！";
            } else {
                $text = "下班打卡成功!";
            }
            //最多可以早退 到什么时候算缺勤：
            //$zuiduoqueqin = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['endtime']))-60*$attendance['endlate'];
        }

        // if($zuiduoqueqin>$now_dakatime){
        //      $params['isqueqin'] = "1";

        // }


        $insert_result = Db::name('daka')->insert($params);
        if ($insert_result) {
            $this->xiaoxi($text, $chatid);
        } else {
            $this->xiaoxi("打卡失败,请联系技术人员", $chatid);


        }
    }


    //请假：
    protected function qingjia($chatid, $userid, $user_info)
    {

        $start_time = strtotime(date("Y-m-d 00:00:00"));

        $end_time = strtotime(date("Y-m-d 23:59:59"));

        $approve_select = Db::name('approve')->where(['status' => "1", 'starttime' => ['<', $end_time], 'endtime' => ['>', $start_time], 'admin_id' => $user_info['id']])->find();

        if ($approve_select) {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $user_info['nickname'] . "你今天不是请假了吗？",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
    }

    //是否上班：
    protected function xuyaoshangban($chatid, $userid, $user_info)
    {
        $user_group_id = Db::name('auth_group_access')->where(['uid' => $user_info['id']])->find();
        $attendance_user = Db::name('attendance a')->field('a.hobbydata,a.dates')->join('kao_attendanceshift b', 'a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();
        $jinri = date("Y-m-d", time());
        if ($attendance_user) {
            $now_week = date('w', time());
            $how_week = $now_week;
            if ($now_week == 0) {
                $how_week = 7;
            }

            $hobbydata = explode(",", $attendance_user['hobbydata']);

            //查看员工今天上班不
            if (!in_array($how_week, $hobbydata)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $user_info['nickname'] . "你今天不是休息时间吗？",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            //查看今日是不是公休日:
            if (!empty($attendance_user['dates'])) {
                $dates_arr = explode(",", $attendance_user['dates']);
                if (count($dates_arr) > 0) {
                    for ($i = 0; $i < count($dates_arr); $i++) {
                        $dates_arr[$i] = trim($dates_arr[$i]);//去掉空格
                    }
                }
                //查询今日是不是公休：
                if (in_array($jinri, $dates_arr)) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $user_info['nickname'] . "你今天不是公休日的时间吗？",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
            }


        } else {
            $this->xiaoxi("当前没有你的考勤班次信息，请联系老板", $chatid);
        }
    }

    //已经打卡过：
    protected function yijingchuqin($chatid, $userid, $user_info, $dakatype = "0")
    {
        $dakatime = strtotime(date("Y-m-d"));
        $daka_info = Db::name('daka')->where(['admin_id' => $user_info['id'], 'dakatime' => $dakatime, 'typelist' => $dakatype])->find();
        if ($daka_info) {
            if ($dakatype == "0") {
                //上班打卡：
                $text = $user_info['nickname'] . ",你已经打过上班卡了！只允许打一次上班卡！";
            } else {
                //下班打卡：
                $text = $user_info['nickname'] . ",你已经打过下班卡了！只允许打一次下班卡！";
            }

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $text,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        return true;
    }


    protected function quanxian($chatid, $userid, $quanxian, $username)
    {


        $username = "@" . $username;


        $user_info = Db::name('admin')->where(['status' => "normal", "chatid" => $userid])->find();

        if (!$user_info) {

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $username . "想要成为天使员工?请联系：楚歌 @fu_008",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        } else {
            if ($user_info['typedata'] == "1") {
                $this->xiaoxi($user_info['nickname'] . ", 你不被允许用tg打卡上下班！", $chatid);
            }

            if ($user_info['id'] == "1") {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "老板天生不需要上班的！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            return $user_info;
        }


    }

    public function starthou($chatid, $userid, $message)
    {
        $keyboard2 = [
            'keyboard' => [
                [
                    ['text' => '上班打卡'],
                    ['text' => '下班打卡'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,

        ];
        $encodedKeyboard2 = json_encode($keyboard2);
        $parameter = array(
            'chat_id' => $chatid,
            'text' => "你好:" . "欢迎使用天使内部考勤后台！",
            'reply_markup' => $encodedKeyboard2
        );

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();
    }


    public function huidiaobacks($data)
    {
        $text = $data['callback_query']['data'];
        $chatid = $data['callback_query']['message']['chat']['id'];
        $from_id = $data['callback_query']['from']['id'];
        $message_id = $data['callback_query']['message']['message_id'];

        if (strpos($text, 'quedingdakaba') !== false) {
            $qudao2 = explode("###", $text);
            $quanxian = $qudao2[1];
            $user_info = Db::name('admin')->where(['status' => "normal", "chatid" => $from_id])->find();

            $this->zhixingdaka($chatid, $from_id, $user_info, $quanxian);
        }


        if (strpos($text, 'chouchaka') !== false) {
            $qudao = explode("###", $text);
            //执行抽卡逻辑
            $chouka_info = Db::name('chouka')->where(['id' => $qudao['1']])->find();


            if ($chouka_info) {

                $msg = $this->quedingdaka($chouka_info, "0");

                $this->xiaoxi($msg, $chatid, '1', $data['callback_query']['id']);

            } else {
                $msg = "没有查询到你的抽查打卡记录信息";
                $this->xiaoxi($msg, $chatid, '1', $data['callback_query']['id']);
            }
        }
    }

    public function apidaka()
    {
        $choukaid = $_POST['choukaid'];
        $token = $_POST['token'];
        $type = "1";
        $chouka_info = Db::name('chouka')->where(['id' => $choukaid])->find();
        if ($token != $chouka_info['token']) {
            $this->error("token校验失败");
        }
        $update_data = array(
            'dakatime' => time(),
            'dakalist' => $type,
            'statuslist' => "1"
        );

        if ($chouka_info['statuslist'] != "0") {

            $this->error("已经打过这个抽查打卡了,请勿重复打卡");
        }
        $year = $chouka_info['year'];
        $month = $chouka_info['month'];
        $day = $chouka_info['day'];
        $choukatime = strtotime($year . "-" . $month . "-" . $day . " " . date("H:i:s", $chouka_info['choukatime']));

        $choukaendtime = strtotime($year . "-" . $month . "-" . $day . " " . date("H:i:s", $chouka_info['chouendtime']));


        if (time() > $choukaendtime) {
            $update_data['statuslist'] = "2";
        }

        $result = Db::name('chouka')->where(['id' => $chouka_info['id']])->update($update_data);
        if ($result) {

            $this->success("抽查打卡成功");
        } else {

            $this->error("抽查打卡异常");
        }
    }

    /**
     * Teams 绑定：Bot 收到用户发「绑定 绑定码」后调用，将 conversation 绑定到后台员工
     * POST: conversationId, serviceUrl, tenantId(可选), bindCode
     */
    public function teams_bind()
    {
        $conversationId = $this->request->post('conversationId');
        $serviceUrl = $this->request->post('serviceUrl');
        $tenantId = $this->request->post('tenantId', '');
        $bindCode = $this->request->post('bindCode', '');
        if (empty($conversationId) || empty($serviceUrl) || empty($bindCode)) {
            $this->error('缺少参数：conversationId、serviceUrl、bindCode');
        }
        $bindCode = trim($bindCode);
        $admin = Db::name('admin')->where(['status' => 'normal', 'teams_bind_code' => $bindCode])->find();
        if (!$admin) {
            $this->error('绑定码无效或已失效');
        }
        Db::name('admin')->where('id', $admin['id'])->update([
            'teams_conversation_id' => $conversationId,
            'teams_service_url' => $serviceUrl,
            'teams_tenant_id' => $tenantId,
            'teams_bind_code' => null,
        ]);
        $this->success('绑定成功', ['nickname' => $admin['nickname']]);
    }

    /**
     * Teams 考勤回调：Bot 把用户消息/按钮事件转发到此，返回要回复的文案和卡片
     * POST: type=message|invoke, conversationId, serviceUrl, tenantId?, from={id,name}, text?, value?
     * 返回: { code, msg, data: { message?, card?: { title, text, buttons } } }
     */
    public function teams_callback()
    {
        $input = $this->request->post();
        $type = isset($input['type']) ? $input['type'] : 'message';
        $conversationId = isset($input['conversationId']) ? $input['conversationId'] : '';
        $tenantId = isset($input['tenantId']) ? $input['tenantId'] : '';
        $fromId = isset($input['from']['id']) ? $input['from']['id'] : '';
        $fromName = isset($input['from']['name']) ? $input['from']['name'] : '';
        $text = isset($input['text']) ? trim($input['text']) : '';
        $value = isset($input['value']) ? $input['value'] : '';

        if (empty($conversationId)) {
            $this->error('缺少 conversationId');
        }

        // 申请绑定：用户发「申请绑定账号 用户名」，仅返回自己的 tenantId / teams_user_id 让管理员预配置
        if ($type === 'message' && preg_match('/^申请绑定账号\s*(\S+)$/u', $text, $m)) {
            $usernameApply = trim($m[1]);
            if (empty($usernameApply)) {
                $this->success('处理完成', ['message' => '请发送「申请绑定账号 用户名」，例如：申请绑定账号 xiaowu']);
                return;
            }
            if (empty($tenantId) || empty($fromId)) {
                $this->success('处理完成', ['message' => '未获取到你的 Teams 标识，请稍后重试或联系管理员手动配置。']);
                return;
            }
            $msg = "你申请绑定的后台账号为：{$usernameApply}\n"
                . "请将下面这条消息转发给管理员，由管理员在后台预先配置你的 Teams 绑定信息：\n"
                . "【系统记录】tenantId={$tenantId}，teams_user_id={$fromId}\n\n"
                . "管理员配置完成后，你再发送「绑定 {$usernameApply}」即可完成绑定。";
            $this->success('处理完成', ['message' => $msg]);
            return;
        }

        // 绑定：用户发「绑定 用户名」，仅允许绑定到管理员已预配置好 tenantId + teams_user_id 的账号
        if ($type === 'message' && preg_match('/^绑定\s*(\S+)$/u', $text, $m)) {
            $username = trim($m[1]);
            if (empty($username)) {
                $this->success('处理完成', ['message' => '请发送「绑定 用户名」，例如：绑定 xiaowu']);
                return;
            }
            if (empty($tenantId) || empty($fromId)) {
                $this->success('处理完成', ['message' => '未获取到你的 Teams 标识，请稍后重试或联系管理员确认配置。']);
                return;
            }
            // 必须由管理员事先在 admin 表中配置好 teams_tenant_id + teams_user_id
            $admin = Db::name('admin')->where([
                'status' => 'normal',
                'username' => $username,
                'teams_tenant_id' => $tenantId,
                'teams_user_id' => $fromId,
            ])->find();
            if (!$admin) {
                $this->success('处理完成', ['message' => '未找到与你当前 Teams 身份匹配的账号（' . $username . '）。请确认管理员已在后台为你配置好了 Teams 绑定信息后再尝试绑定。']);
                return;
            }
            Db::name('admin')->where('id', $admin['id'])->update([
                'teams_conversation_id' => $conversationId,
                'teams_service_url' => isset($input['serviceUrl']) ? $input['serviceUrl'] : '',
                // 以管理员预配置为准，仅在为空时补全
                'teams_tenant_id' => $admin['teams_tenant_id'] ?: $tenantId,
            ]);
            $this->success('处理完成', ['message' => '绑定成功，你好 ' . $admin['nickname'] . '！可以发送「上班打卡」「下班打卡」进行考勤。']);
            return;
        }

        // 根据 Teams 身份 / conversationId 找后台员工（已绑定 Teams 的）
        $user_info = null;
        if (!empty($tenantId) && !empty($fromId)) {
            $user_info = Db::name('admin')->where([
                'status' => 'normal',
                'teams_tenant_id' => $tenantId,
                'teams_user_id' => $fromId,
            ])->find();
        }
        if (!$user_info) {
            $user_info = Db::name('admin')->where([
                'status' => 'normal',
                'teams_conversation_id' => $conversationId,
            ])->find();
        }
        if (!$user_info) {
            $this->success('处理完成', ['message' => '请先绑定考勤账号：可先发送「申请绑定账号 用户名」获取绑定信息发给管理员预配置，然后发送「绑定 用户名」完成绑定。例如：申请绑定账号 xiaowu，绑定 xiaowu。']);
            return;
        }

        // 按钮：确认上班/下班打卡
        if ($type === 'invoke' && (strpos($value, 'quedingdakaba') !== false)) {
            $parts = explode('###', $value);
            $quanxian = isset($parts[1]) ? $parts[1] : '';
            if ($quanxian !== '上班打卡' && $quanxian !== '下班打卡') {
                $this->success('处理完成', ['message' => '无效操作']);
                return;
            }
            $msg = $this->teams_zhixingdaka_return($user_info, $quanxian);
            $this->success('处理完成', ['message' => $msg]);
            return;
        }

        // 按钮：抽查打卡
        if ($type === 'invoke' && strpos($value, 'chouchaka') !== false) {
            \think\Log::info('Teams 抽查打卡回调: type=' . $type . ', value=' . $value . ', admin_id=' . $user_info['id']);
            $parts = explode('###', $value);
            $choukaId = isset($parts[1]) ? intval($parts[1]) : 0;
            if ($choukaId > 0) {
                $chouka_info = Db::name('chouka')->where(['id' => $choukaId])->find();
            } else {
                // 兜底：如果按钮没有携带具体ID（例如客户端只发了“立即打抽查卡”），
                // 则按当前员工、最近一条未打卡且已推送的抽查记录来处理
                $now = time();
                $chouka_info = Db::name('chouka')
                    ->where([
                        'admin_id' => $user_info['id'],
                        'statuslist' => '0',
                    ])
                    ->where('tuisongtime', '<=', $now)
                    ->order('tuisongtime', 'desc')
                    ->find();
            }
            if (!$chouka_info) {
                $this->success('处理完成', ['message' => '没有查询到你的抽查打卡记录']);
                return;
            }
            $msg = $this->quedingdaka($chouka_info, '0');
            $this->success('处理完成', ['message' => $msg]);
            return;
        }

        // 文本：上班打卡 / 下班打卡 -> 返回确认卡片
        if ($type === 'message' && ($text === '上班打卡' || $text === '下班打卡')) {
            $quanxian = $text;
            $err = $this->teams_quanxian_check($user_info);
            if ($err !== null) {
                $this->success('处理完成', ['message' => $err]);
                return;
            }
            if ($user_info['id'] == '1') {
                $this->success('处理完成', ['message' => '老板天生不需要上班的！']);
                return;
            }
            $tx = $quanxian === '上班打卡' ? '立即打上班卡' : '立即打下班卡';
            $confirmText = '打卡确认: 🔵 ' . $quanxian . ' 🔵 ，当前系统时间：' . date('Y-m-d H:i:s');
            $this->success('处理完成', [
                'message' => $confirmText,
                'card' => [
                    'title' => '考勤确认',
                    'text' => $confirmText,
                    'buttons' => [['title' => $tx, 'value' => 'quedingdakaba###' . $quanxian]],
                ],
            ]);
            return;
        }

        // 未知指令
        $this->success('处理完成', ['message' => '可发送：上班打卡、下班打卡。首次使用可先发送「申请绑定账号 用户名」获取绑定信息给管理员配置，然后发送「绑定 用户名」绑定账号，例如：申请绑定账号 xiaowu，绑定 xiaowu。']);
    }

    /**
     * 获取当前用户的 Teams 绑定码（需登录）
     * 员工在后台调用后，到 Teams 里对机器人发送「绑定 绑定码」即可完成绑定
     */
    public function get_teams_bind_code()
    {
        $adminId = $this->auth->id;
        if (!$adminId) {
            $this->error('请先登录');
        }
        $code = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        Db::name('admin')->where('id', $adminId)->update(['teams_bind_code' => $code]);
        $this->success('获取成功', ['bindCode' => $code, 'hint' => '在 Teams 中向考勤机器人发送：绑定 ' . $code]);
    }

    /** Teams 用：权限/班次检查，返回错误文案或 null */
    protected function teams_quanxian_check($user_info)
    {
        if ($user_info['typedata'] == '1') {
            return $user_info['nickname'] . '，你不被允许用 TG/Teams 打卡上下班！';
        }
        $user_group_id = Db::name('auth_group_access')->where(['uid' => $user_info['id']])->find();
        if (!$user_group_id) {
            return '当前没有你的考勤班次信息，请联系老板';
        }
        $attendance_user = Db::name('attendance a')->field('a.hobbydata,a.dates')->join('kao_attendanceshift b', 'a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();
        if (!$attendance_user) {
            return '当前没有你的考勤班次信息，请联系老板';
        }
        $now_week = date('w', time());
        $how_week = $now_week == 0 ? 7 : $now_week;
        $hobbydata = explode(',', $attendance_user['hobbydata']);
        if (!in_array($how_week, $hobbydata)) {
            return $user_info['nickname'] . '你今天不是休息时间吗？';
        }
        $jinri = date('Y-m-d', time());
        if (!empty($attendance_user['dates'])) {
            $dates_arr = array_map('trim', explode(',', $attendance_user['dates']));
            if (in_array($jinri, $dates_arr)) {
                return $user_info['nickname'] . '你今天不是公休日的时间吗？';
            }
        }
        $start_time = strtotime(date('Y-m-d 00:00:00'));
        $end_time = strtotime(date('Y-m-d 23:59:59'));
        $approve = Db::name('approve')->where(['status' => '1', 'starttime' => ['<', $end_time], 'endtime' => ['>', $start_time], 'admin_id' => $user_info['id']])->find();
        if ($approve) {
            return $user_info['nickname'] . '你今天不是请假了吗？';
        }
        return null;
    }

    /** Teams 用：执行上下班打卡，返回结果文案（不发 TG） */
    protected function teams_zhixingdaka_return($user_info, $quanxian)
    {
        $dakatype = $quanxian === '上班打卡' ? '0' : '2';
        $dakatime = strtotime(date('Y-m-d'));
        $daka_info = Db::name('daka')->where(['admin_id' => $user_info['id'], 'dakatime' => $dakatime, 'typelist' => $dakatype])->find();
        if ($daka_info) {
            return $dakatype === '0' ? ($user_info['nickname'] . '，你已经打过上班卡了！') : ($user_info['nickname'] . '，你已经打过下班卡了！');
        }
        $user_group_id = Db::name('auth_group_access')->where(['uid' => $user_info['id']])->find();
        $attendance = Db::name('attendance a')->field('b.*')->join('kao_attendanceshift b', 'a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();
        if (!$attendance) {
            return '你还没有设置对应的考勤组跟班次，需要联系老板！';
        }
        $now_dakatime = time();
        $params = [
            'admin_id' => $user_info['id'],
            'dakatime' => $dakatime,
            'createtime' => $now_dakatime,
            'typelist' => $dakatype,
            'year' => date('Y'),
            'month' => date('m'),
            'iszaotui' => '0',
            'isqueqin' => '0',
            'ischidao' => '0',
        ];
        if ($dakatype === '0') {
            $zuizaodaka_time = strtotime(date('Y-m-d') . ' ' . date('H:i:s', $attendance['starttime'])) - 60 * $attendance['startbefore'];
            if ($zuizaodaka_time > $now_dakatime) {
                return '最早打卡时间为：' . date('H:i:s', $zuizaodaka_time);
            }
            $zuiduochidao = strtotime(date('Y-m-d') . ' ' . date('H:i:s', $attendance['starttime'])) + 60 * $attendance['startmiss'];
            if ($now_dakatime > $zuiduochidao) {
                $params['ischidao'] = '1';
            }
            $insert = Db::name('daka')->insert($params);
            return $insert ? ($params['ischidao'] === '1' ? '上班打卡成功，并且很严肃的告知你：你迟到了！' : '上班打卡成功!') : '打卡失败，请联系技术人员';
        }
        $zuizaodaka_time = strtotime(date('Y-m-d') . ' ' . date('H:i:s', $attendance['endtime'])) - 60 * $attendance['endbefore'];
        if ($zuizaodaka_time > $now_dakatime) {
            return '最早打下班卡时间为：' . date('H:i:s', $zuizaodaka_time);
        }
        $zuiduozaotui = strtotime(date('Y-m-d') . ' ' . date('H:i:s', $attendance['endtime'])) - 60 * $attendance['endmiss'];
        if ($zuiduozaotui > $now_dakatime) {
            $params['iszaotui'] = '1';
        }
        $insert = Db::name('daka')->insert($params);
        return $insert ? ($params['iszaotui'] === '1' ? '下班打卡成功，并且很严肃的告知你：你这属于早退了！' : '下班打卡成功!') : '打卡失败，请联系技术人员';
    }

    /**
     * 确定打卡：
     */
    public function quedingdaka($chouka_info, $type = "0")
    {


        $update_data = array(
            'dakatime' => time(),
            'dakalist' => $type,
            'statuslist' => "1"
        );

        if ($chouka_info['statuslist'] != "0") {
            return "已经打过这个抽查打卡了,请勿重复打卡";
        }
        $year = $chouka_info['year'];
        $month = $chouka_info['month'];
        $day = $chouka_info['day'];

        $choukatime = strtotime($year . "-" . $month . "-" . $day . " " . date("H:i:s", $chouka_info['choukatime']));

        $choukaendtime = strtotime($year . "-" . $month . "-" . $day . " " . date("H:i:s", $chouka_info['chouendtime']));


        if (time() > $choukaendtime) {
            $update_data['statuslist'] = "2";
        }
        $result = Db::name('chouka')->where(['id' => $chouka_info['id']])->update($update_data);
        if ($result) {
            return "抽查打卡成功！";
        } else {
            return "抽查打卡异常！";
        }
    }

    public function xiaoxi($msg, $chatid, $type = "0", $answer = "")
    {
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $msg
        );
        $this->http_post_data('sendMessage', json_encode($parameter));
        if ($type == "1") {
            $parameter = array(
                'callback_query_id' => $answer,
                'text' => "",
            );
            $this->http_post_data('answerCallbackQuery', json_encode($parameter));
        }

        exit();
    }

	/**
	 * 发送Telegram消息，失败自动重试一次
	 *
	 * @param string $action API 动作，例如 sendMessage
	 * @param array $parameter 已构造好的参数数组
	 * @param int $maxAttempts 最大尝试次数，默认2（首次+重试1次）
	 * @param int $delayMs 重试前延迟毫秒
	 * @return mixed 最终返回的解码结果数组或原始字符串
	 */
	protected function sendTelegramWithRetry($action, array $parameter, $maxAttempts = 3, $delayMs = 300)
	{
		$attempt = 0;
		$lastDecoded = null;
		do {
			$attempt++;
			\think\Log::info("Telegram 发送尝试({$attempt}/{$maxAttempts})：" . json_encode($parameter));
			if ($attempt === $maxAttempts) {
				// 最后一次尝试走代理
				\think\Log::info("Telegram 最后一次尝试将使用代理 45.76.191.111:23128");
				$result = $this->http_post_data_with_proxy($action, json_encode($parameter), '45.76.191.111:23128');
			} else {
				$result = $this->http_post_data($action, json_encode($parameter));
			}
			\think\Log::info("Telegram API返回结果：" . json_encode($result));
			$decoded = json_decode($result, true);
			if (is_array($decoded) && isset($decoded['ok']) && $decoded['ok'] === true) {
				return $decoded;
			}
			$lastDecoded = $decoded;
			if ($attempt < $maxAttempts) {
				usleep($delayMs * 1000);
			}
		} while ($attempt < $maxAttempts);
		return $lastDecoded !== null ? $lastDecoded : $result;
	}

    public function http_post_data($action, $data_string)
    {
        //这里，
        /*$sql= "insert into wolive_tests (content) values ('".json_encode($data)."')";
        $this->pdo->exec($sql);*/

        $url = $this->link . "/" . $action . "?";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

                'Content-Type: application/json; charset=utf-8',

                'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }

	/**
	 * 使用代理发送 Telegram POST 请求
	 * @param string $action
	 * @param string $data_string JSON 字符串
	 * @param string $proxy 形如 ip:port
	 * @return string
	 */
	protected function http_post_data_with_proxy($action, $data_string, $proxy)
	{
		$url = $this->link . "/" . $action . "?";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen($data_string))
		);
		// 代理设置
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// 可选：更宽松的 SSL 校验以避免部分代理环境下握手失败
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		ob_start();
		curl_exec($ch);
		$return_content = ob_get_contents();
		ob_end_clean();
		return $return_content;
	}

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @param string $account 账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     * @param string $code 验证码
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @param string $email 邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @param string $platform 平台名称
     * @param string $code Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo' => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     */
    public function resetpwd()
    {
        $type = $this->request->post("type", "mobile");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
