<?php

namespace app\admin\controller\laoban;

use app\common\controller\Backend;
use think\Db;
/**
 *
 *
 * @icon fa fa-circle-o
 */
class Dakanew extends Backend
{

    /**
     * Daka模型对象
     * @var \app\admin\model\laoban\Daka
     */
    protected $model = null;



    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\laoban\Daka;
        $this->view->assign("typelistList", $this->model->getTypelistList());
        $this->view->assign("ischidaoList", $this->model->getIschidaoList());
        $this->view->assign("iszaotuiList", $this->model->getIszaotuiList());
    }
     protected function buildparams($searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset/d", 0);
        $limit = $this->request->get("limit/d", 999999);
        //新增自动计算页码
        $page = $limit ? intval($offset / $limit) + 1 : 1;
        if ($this->request->has("page")) {
            $page = $this->request->get("page/d", 1);
        }
        $this->request->get([config('paginate.var_page') => $page]);
        $filter = (array)json_decode($filter, true);
        $op = (array)json_decode($op, true);
        $filter = $filter ? $filter : [];
        $where = [];
        $alias = [];
        $bind = [];
        $name = '';
        $aliasName = '';
        if (!empty($this->model) && $relationSearch) {
            $name = $this->model->getTable();
            $alias[$name] = Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $aliasName = $alias[$name] . '.';
        }
        $sortArr = explode(',', $sort);
        foreach ($sortArr as $index => & $item) {
            $item = stripos($item, ".") === false ? $aliasName . trim($item) : $item;
        }
        unset($item);
        $sort = implode(',', $sortArr);
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$aliasName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $aliasName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        $index = 0;
        foreach ($filter as $k => $v) {
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $k)) {
                continue;
            }
            $sym = $op[$k] ?? '=';
            if (stripos($k, ".") === false) {
                $k = $aliasName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper($op[$k] ?? $sym);
            //null和空字符串特殊处理
            if (!is_array($v)) {
                if (in_array(strtoupper($v), ['NULL', 'NOT NULL'])) {
                    $sym = strtoupper($v);
                }
                if (in_array($v, ['""', "''"])) {
                    $v = '';
                    $sym = '=';
                }
            }

            switch ($sym) {
                case '=':
                case '<>':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $v = is_array($v) ? $v : explode(',', str_replace(' ', ',', $v));
                    $findArr = array_values($v);
                    foreach ($findArr as $idx => $item) {
                        $bindName = "item_" . $index . "_" . $idx;
                        $bind[$bindName] = $item;
                        $where[] = "FIND_IN_SET(:{$bindName}, `" . str_replace('.', '`.`', $k) . "`)";
                    }
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr, function ($v) {
                        return $v != '' && $v !== false && $v !== null;
                    })) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $tableArr = explode('.', $k);
                    if (count($tableArr) > 1 && $tableArr[0] != $name && !in_array($tableArr[0], $alias)
                        && !empty($this->model) && $this->relationSearch) {
                        //修复关联模型下时间无法搜索的BUG
                        $relation = Loader::parseName($tableArr[0], 1, false);
                        $alias[$this->model->$relation()->getTable()] = $tableArr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' TIME', $arr];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
            $index++;
        }
        if (!empty($this->model)) {
            $this->model->alias($alias);
        }
        $model = $this->model;
        foreach ($where as $key=>$value){
            if($value['0'] =="admin_id"){
                //查询这个名字的：
                $admin_info = Db::name('admin')->field('id')->where(['nickname'=>$value['2']])->find();
                if($admin_info){
                    $where[$key]['2'] = $admin_info['id'];
                }
            }
        }


        $where = function ($query) use ($where, $alias, $bind, &$model) {
            if (!empty($model)) {
                $model->alias($alias);
                $model->bind($bind);
            }
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit, $page, $alias, $bind];
    }
    public function index()
    {
    // 设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (!$this->request->isAjax()) {
        return $this->view->fetch();
    }
    // 如果发送的来源是 Selectpage，则转发到 Selectpage
    if ($this->request->request('keyField')) {
        return $this->selectpage();
    }

    // =======================
    // 1) 获取常规查询条件
    // =======================
    [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    $admin_id = $_SESSION['think']['admin']['id'];
    $where2 = [];
    if ($admin_id != "1") {
        $where2['admin_id'] = $admin_id;
    }

    // 处理前端传来的过滤参数（例如：员工名称、年份、月份，以及出勤状态、出勤记录）
    $filter = $this->request->param('filter');
    $chuqinstatusSearch = ''; // 用于搜“出勤状态”
    $chuqinlistSearch   = ''; // 用于搜“出勤记录”

    if ($filter) {
        $filterArr = json_decode($filter, true);

        // 如果要搜员工名称
        if (empty($filterArr['admin_id'])) {
            $this->error("请输入你要查询的员工名称");
        }
        $user_info = Db::name('admin')
            ->where(['username' => $filterArr['admin_id']])
            ->whereOr('nickname', $filterArr['admin_id'])
            ->find();
        if (!$user_info) {
            $this->error("未查询到次员工的信息");
        }

        $year  = isset($filterArr['year']) ? $filterArr['year'] : date('Y');
        $month = isset($filterArr['month']) ? $filterArr['month'] : date('m');

        // 如果前端多传了“出勤状态”或“出勤记录”的搜索值，例如 filter={"chuqinstatus":"请假","chuqinlist":"下班早退"}
        if (isset($filterArr['chuqinstatus'])) {
            $chuqinstatusSearch = trim($filterArr['chuqinstatus']);
        }
        if (isset($filterArr['chuqinlist'])) {
            $chuqinlistSearch = trim($filterArr['chuqinlist']);
        }
    } else {
        // 没有传 filter，默认查当前登录用户+当年当月
        $year  = date('Y');
        $month = date('m');
        $user_info = Db::name('admin')->where(['id' => $admin_id])->find();
    }

    // =======================
    // 2) 生成当月所有日期
    // =======================
    $startDate = date('Y-m-01', strtotime("$year-$month"));
    // 当月的最后一天
    $monthEnd  = date('Y-m-t', strtotime("$year-$month"));
    // 今天日期（用于截断未来日期）
    $todayDate = date('Y-m-d');
    // 统计结束日期：不超过今天
    $endDate   = (strtotime($monthEnd) > strtotime($todayDate)) ? $todayDate : $monthEnd;
    // $allDays   = [];
    // for ($day = strtotime($startDate); $day <= strtotime($endDate); $day = strtotime("+1 day", $day)) {
    //     $allDays[] = date("Y-m-d", $day);
    // }
    $allDays   = [];
    // 原来：for ($day = strtotime($startDate); $day <= strtotime($endDate); $day = strtotime("+1 day", $day))
    for ($day = strtotime($endDate); $day >= strtotime($startDate); $day = strtotime("-1 day", $day)) {
        $allDays[] = date("Y-m-d", $day);
    }

    // =======================
    // 3) 找到用户考勤配置
    // =======================
    $auth_group_access_info = Db::name('auth_group_access')->where(['uid' => $user_info['id']])->find();
    $group_id = isset($auth_group_access_info['group_id']) ? $auth_group_access_info['group_id'] : null;
    $all_kao_attendance = Db::name('attendance')->select();
    $attendance_info = [];
    foreach ($all_kao_attendance as $v) {
        $group_ids = explode(",", $v['group_ids']);
        if (in_array($group_id, $group_ids)) {
            $attendance_info = $v;
            break;
        }
    }
    if (empty($attendance_info)) {
        $this->error("未查询到次员工的考勤配置信息");
    }

    // 用户休息日（如 hobbydata="6,7" 表示周六周日休）
    $workweek = array_map('intval', explode(",", $attendance_info['hobbydata']));
    // 固定节假日 => "Y-m-d" 字符串数组
    $dont_workday = explode(",", $attendance_info['dates']);

    // =======================
    // 4) 获取请假记录
    // =======================
    $approve_info = Db::name('approve')
        ->where(['admin_id' => $user_info['id'], 'status' => "1"])
        ->select();

    // =======================
    // 5) 查询实际打卡记录
    // =======================
    $listItems = $this->model
        ->where($where)
        ->where($where2)->select();
        //->order($sort, $order)
        //->paginate($limit);

    // 转数组并转换 admin_id => 昵称
   // $listItems = $list->items();
    foreach ($listItems as $key => $value) {
        $admin_info = Db::name('admin')
            ->field('nickname')
            ->where(['id' => $value->admin_id])
            ->find();
        $listItems[$key]['admin_id'] = $admin_info ? $admin_info['nickname'] : "用户信息不存在";
    }

    // 把打卡记录映射到 $map[ typelist ][ dateString ]
    $map = [];
    foreach ($listItems as $value) {
        $dateString = date("Y-m-d", $value['dakatime']);
        $typelist   = $value['typelist']; // 0=上班,2=下班
        $map[$typelist][$dateString] = [
            'admin_id'   => $value['admin_id'],
            'createtime' => $value['createtime'],
            'typelist'   => $typelist,
        ];
    }

    $now_week_arr = ["星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"];

    // =======================
    // 6) 组装 finalData
    // =======================
    $finalData = [];

    foreach ($allDays as $d) {
        $timestamp = strtotime($d);
        $weekDay   = date('w', $timestamp); // 0=周日,1=周一,...6=周六
        if ($weekDay == 0) {
            $weekDay = 7; // 让周日变成7
        }
        $weekStr = $now_week_arr[$weekDay - 1];

        // ---------- (A) 周末假 ----------
        if (!in_array($weekDay, $workweek)) {
            $finalData[] = [
                'daka_date'    => $d,
                'admin_id'     => $user_info['nickname'],
                'chuqinstatus' => '正常休假',
                'chuqinlist'   => '周末假',
                'week'         => $weekStr,
                'createtime'   => '',
                'typelist'     => '-',
                'year'=>$year,
                'month'=>$month
            ];
            continue;
        }

        // ---------- (B) 节假日 ----------
        if (in_array($d, $dont_workday)) {
            $finalData[] = [
                'daka_date'    => $d,
                'admin_id'     => $user_info['nickname'],
                'chuqinstatus' => '正常休假',
                'chuqinlist'   => '节日假',
                'week'         => $weekStr,
                'createtime'   => '',
                'typelist'     => '-',
                'year'=>$year,
                'month'=>$month
            ];
            continue;
        }

        // ---------- (C) 请假 ----------
        $isLeave = false;
        $currentDayStart = strtotime($d . ' 00:00:00');
        $currentDayEnd   = strtotime($d . ' 23:59:59');
        foreach ($approve_info as $ap) {

            $ap_start = $ap['starttime'];
            $ap_end   = $ap['endtime'];
            // 只要当天与请假区间有交集
            if ($currentDayStart <= $ap_end && $currentDayEnd >= $ap_start) {
                $finalData[] = [
                    'daka_date'    => $d,
                    'admin_id'     => $user_info['nickname'],
                    'chuqinstatus' => '请假',
                    'chuqinlist'   => isset($ap['type']) ? $ap['type'] : '请假',
                    'week'         => $weekStr,
                    'createtime'   => '',
                    'typelist'     => '-',
                    'year'=>$year,
                    'month'=>$month
                ];
                $isLeave = true;
                break;
            }
        }
        if ($isLeave) {
            continue;
        }

        // ---------- (D) 工作日 + 未请假 => 判断上班 / 下班 ----------
        $expectedStartTimeStr = $d . " " . date("H:i:s", $attendance_info['starttime']);
        $expectedEndTimeStr   = $d . " " . date("H:i:s", $attendance_info['endtime']);
        $expectedStartTime    = strtotime($expectedStartTimeStr);
        $expectedEndTime      = strtotime($expectedEndTimeStr);

        // 上班记录 (typelist=0)
        $recordMorning = isset($map[0][$d]) ? $map[0][$d] : null;
        $dayRecord = [
            'daka_date' => $d,
            'admin_id'  => $user_info['nickname'],
            'week'      => $weekStr,
             'year'=>$year,
            'month'=>$month
        ];
        if ($recordMorning) {
            $dayRecord['createtime']   = $recordMorning['createtime'];
            $dayRecord['typelist']     = $recordMorning['typelist'];


            if ($recordMorning['createtime'] > $expectedStartTime) {
                $dayRecord['chuqinstatus'] = '上班异常出勤';
                $dayRecord['chuqinlist']   = '上班迟到';
            } else {
                $dayRecord['chuqinstatus'] = '上班正常出勤';
                $dayRecord['chuqinlist']   = '按时上班';
            }
        } else {
            $dayRecord['chuqinstatus'] = '上班缺勤';
            $dayRecord['chuqinlist']   = '上班缺卡';
            $dayRecord['createtime']   = '';
            $dayRecord['typelist']     = '-';

        }
        $finalData[] = $dayRecord;

        // 下班记录 (typelist=2)
        $recordEvening = isset($map[2][$d]) ? $map[2][$d] : null;
        $eveningRecord = [
            'daka_date' => $d,
            'admin_id'  => $user_info['nickname'],
            'week'      => $weekStr,
            'year'=>$year,
            'month'=>$month
        ];
        if ($recordEvening) {
            $eveningRecord['createtime']   = $recordEvening['createtime'];
            $eveningRecord['typelist']     = $recordEvening['typelist'];

            if ($recordEvening['createtime'] < $expectedEndTime) {
                $eveningRecord['chuqinstatus'] = '下班异常出勤';
                $eveningRecord['chuqinlist']   = '下班早退';
            } else {
                $eveningRecord['chuqinstatus'] = '下班正常出勤';
                $eveningRecord['chuqinlist']   = '按时下班';
            }
        } else {
            $eveningRecord['chuqinstatus'] = '下班缺勤';
            $eveningRecord['chuqinlist']   = '下班缺卡';
            $eveningRecord['createtime']   = '';
            $eveningRecord['typelist']     = '-';
        }
        $finalData[] = $eveningRecord;
    }

    // =======================
    // 7) 在 finalData 基础上，做“出勤状态 / 出勤记录”搜索(可选)
    // =======================
    if ($chuqinstatusSearch !== '') {
        $finalData = array_filter($finalData, function($row) use ($chuqinstatusSearch) {
            // 模糊包含 或 完整匹配 均可，根据需要
            return mb_strpos($row['chuqinstatus'], $chuqinstatusSearch) !== false;
        });
    }
    if ($chuqinlistSearch !== '') {
        $finalData = array_filter($finalData, function($row) use ($chuqinlistSearch) {
            return mb_strpos($row['chuqinlist'], $chuqinlistSearch) !== false;
        });
    }

    // 重新索引
    $finalData = array_values($finalData);

    // =======================
    // 8) 统计汇总：异常出勤、请假、缺勤
    // =======================
    $summary = [
        '异常出勤' => 0,
        '请假'   => 0,
        '上班缺勤'   => 0,
        '下班缺勤'   => 0,
    ];
    foreach ($finalData as $row) {
        // 若是“上班异常出勤”或“下班异常出勤”，都算“异常出勤”
        if (in_array($row['chuqinstatus'], ['上班异常出勤','下班异常出勤'])) {
            $summary['异常出勤']++;
        }
        // 若是“请假”
        if ($row['chuqinstatus'] === '请假') {
            $summary['请假']++;
        }
        // 若是“上班缺勤”或“下班缺勤”
        if (in_array($row['chuqinstatus'], ['上班缺勤'])) {
            $summary['上班缺勤']++;
        }
        if (in_array($row['chuqinstatus'], ['下班缺勤'])) {
            $summary['下班缺勤']++;
        }
    }
   // var_dump($summary);
    // 拼成想要的字符串，也可以直接返回数组
    // 例如： "异常出勤: 3次, 请假: 3次, 缺勤: 2次"
    $summaryStr = "异常出勤：{$summary['异常出勤']}次，" .
                  "请假：{$summary['请假']}次，" .
                  "上班缺勤：{$summary['上班缺勤']}次.".
                  "下班缺勤：{$summary['下班缺勤']}次";


    // =======================
    // 最终返回
    // =======================
    $result = [
        'total'   => count($finalData),
        'rows'    => $finalData,
        // 多带一个 summary 信息
        'summary' => $summaryStr,
        // 如果想前端拿到更详细结构，可把数组也带上:
        // 'summaryDetail' => $summary,
    ];
    return json($result);
}


    public function shangbandaka(){
        $params['admin_id'] = $_SESSION['think']['admin']['id'];
        $day_time = strtotime(date("Y-m-d"));
        //查询是不是已经打卡过：
        $daka_info = Db::name('daka')->where(['dakatime'=>$day_time,'typelist'=>"0",'admin_id'=>$params['admin_id']])->find();
        if($daka_info){
             $this->error("你今天已经打过出勤卡了");
        }

        $params['dakatime'] = $day_time;
        $params['createtime'] = time();
        $params['typelist'] = "0";
        $params['year'] = date("Y");
        $params['month'] = date("m");
        $params['iszaotui'] = "0";
        //这里需要判断用户是不是迟到了：先查询用户所在的考勤组：
        $user_group_id = Db::name('auth_group_access')->where(['uid'=>$params['admin_id']])->find();



        $attendance = Db::name('attendance a')->field('b.*')->join('kao_attendanceshift b','a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();
        if(!$attendance){
             $this->error("还没有设置对应的考勤组跟班次");
        }
        $now_dakatime = time();
        //不能超过多少时间打卡：
        $zuizaodaka_time = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['starttime']))-60*$attendance['startbefore'];

        if($zuizaodaka_time>$now_dakatime){
            $this->error("最早打卡时间为：".date("H:i:s",$zuizaodaka_time));
        }
        //最多可以迟到 到什么时候：
        $zuiduochidao = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['starttime']))+60*$attendance['startmiss'];
        //最多可以迟到 到什么时候算缺勤：
        //$zuiduoqueqin = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['starttime']))+60*$attendance['startbelate'];




        $params['isqueqin'] = "0";
        $params['ischidao'] = "0";
        // if($now_dakatime>$zuiduoqueqin){
        //      $params['isqueqin'] = "1";

        // }
        if($now_dakatime>$zuiduochidao){
             $params['ischidao'] = "1";
        }

        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }


            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success("打卡成功！");
    }
    public function chouchadaka() {
        if (false === $this->request->isPost()) {
            $this->view->assign("dakatime",date("H:i:s"));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        $params['admin_id'] = $_SESSION['think']['admin']['id'];
        //这里需要判断用户是不是迟到了：先查询用户所在的考勤组：
        $user_group_id = Db::name('auth_group_access')->where(['uid'=>$params['admin_id']])->find();
        $attendance = Db::name('attendance a')->field('b.startmiss,b.starttime,b.startbelate,b.startbefore,b.endbefore,b.onechou,b.twochou,b.threechou,b.fourchou')->join('kao_attendanceshift b','a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();

        $day_time = strtotime(date("Y-m-d"));
        //查询是不是已经打卡过：
        $chouka_count = Db::name('daka')->where(['dakatime'=>$day_time,'typelist'=>"1",'admin_id'=>$params['admin_id']])->count();
        if($chouka_count){
             $this->error("你今天已经抽查打卡了");
        }

        $params['dakatime'] = $day_time;
        $params['createtime'] = time();
        $params['typelist'] = "1";
        $params['year'] = date("Y");
        $params['month'] = date("m");
        $params['iszaotui'] = "0";
        //这里需要判断用户是不是迟到了：先查询用户所在的考勤组：
        $user_group_id = Db::name('auth_group_access')->where(['uid'=>$params['admin_id']])->find();




        $now_dakatime = time();
        //不能超过多少时间打卡：
        $zuizaodaka_time = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['starttime']))-60*$attendance['startbefore'];
        if($zuizaodaka_time>$now_dakatime){
            $this->error("最早打卡时间为：".date("H:i:s",$zuizaodaka_time));
        }
        //最多可以迟到 到什么时候：
        $zuiduochidao = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['starttime']))+60*$attendance['startmiss'];
        //最多可以迟到 到什么时候算缺勤：
        $zuiduoqueqin = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['starttime']))+60*$attendance['startbelate'];




        $params['isqueqin'] = "0";
        $params['ischidao'] = "0";
        if($now_dakatime>$zuiduoqueqin){
             $params['isqueqin'] = "1";

        }
        if($now_dakatime>$zuiduochidao){
             $params['ischidao'] = "1";
        }

        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }


            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success("抽查打卡成功！");
    }
    public function xiabandaka(){
        if (false === $this->request->isPost()) {
            $this->view->assign("dakatime",date("H:i:s"));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        $params['admin_id'] = $_SESSION['think']['admin']['id'];
        $day_time = strtotime(date("Y-m-d"));
        //查询是不是已经打卡过：
        $daka_info = Db::name('daka')->where(['dakatime'=>$day_time,'typelist'=>"2",'admin_id'=>$params['admin_id']])->find();
        if($daka_info){
             $this->error("你今天已经打过下班卡了");
        }

        $params['dakatime'] = $day_time;
        $params['createtime'] = time();
        $params['typelist'] = "2";
        $params['year'] = date("Y");
        $params['month'] = date("m");
        $params['ischidao'] = "0";
        //这里需要判断用户是不是迟到了：先查询用户所在的考勤组：
        $user_group_id = Db::name('auth_group_access')->where(['uid'=>$params['admin_id']])->find();



        $attendance = Db::name('attendance a')->field('b.*')->join('kao_attendanceshift b','a.attendanceshift_id=b.id')->where('FIND_IN_SET(:value, a.group_ids)', ['value' => $user_group_id['group_id']])->find();

        $now_dakatime = time();
        //不能超过多少时间打卡：
        $zuizaodaka_time = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['endtime']))-60*$attendance['endbefore'];
        if($zuizaodaka_time>$now_dakatime){
            $this->error("最早打下班卡时间为：".date("H:i:s",$zuizaodaka_time));
        }
        //最多可以早退 到什么时候：
        $zuiduozaotui= strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['endtime']))-60*$attendance['endmiss'];
        //最多可以早退 到什么时候算缺勤：
        //$zuiduoqueqin = strtotime(date("Y-m-d")." ".date("H:i:s",$attendance['endtime']))-60*$attendance['endlate'];




        $params['isqueqin'] = "0";
        $params['iszaotui'] = "0";
        // if($zuiduoqueqin>$now_dakatime){
        //      $params['isqueqin'] = "1";

        // }
        if($zuiduozaotui>$now_dakatime){
             $params['iszaotui'] = "1";
        }



        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }


            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success("下班打卡成功！");
    }


    //出勤打卡：
     public function add()
    {
        if (false === $this->request->isPost()) {
            $typelist = $this->request->get('typelist');
            $this->view->assign("typelist",$typelist);
            $this->view->assign("dakatime",date("H:i:s"));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');

        $admin_id = $_SESSION['think']['admin']['id'];
        $admin_info = Db::name('admin')->field('typedata')->where(['id'=>$admin_id])->find();

        if($admin_info['typedata']=="0"){
            $this->error("你只可以在tg上打卡！");
        }

        if($params['typelist']=="0"){
            //上班打卡：
            $this->shangbandaka();
        }elseif ($params['typelist']=="1") {
             $this->chouchadaka();
        }else{
             $this->xiabandaka();
        }

    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
