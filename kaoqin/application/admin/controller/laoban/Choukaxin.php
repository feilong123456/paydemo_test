<?php

namespace app\admin\controller\laoban;

use app\common\controller\Backend;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Choukaxin extends Backend
{

    /**
     * Choukaxin模型对象
     * @var \app\admin\model\laoban\Choukaxin
     */
    protected $model = null;
    protected $riqi = array();
    protected $chaxunshi = 0;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Admin');
       
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
     
 
        if(count($where)>0){
            for($ik=0;$ik<count($where);$ik++){
               
                if($where[$ik][0]=="riqi"){
                 
                     $date = strtotime($where[$ik][2]);
                     $this->chaxunshi = strtotime(date("Y-m-d",$date));
                     $this->riqi['year'] = date('Y',$date);
                     $this->riqi['month'] = date('m',$date);
                     $this->riqi['day'] = date('d',$date);
                     unset($where[$ik]);
                }
               
            }
            for($ik=0;$ik<count($where);$ik++){
                if($where[$ik][0]=="id"){

                     unset($where[$ik]);
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
        $date2 =$this->chaxunshi=strtotime(date("Y-m-d"));
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            
             $this->assign(['date2'=>$date2]);
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
       
        $where3 =$this->riqi;

        
        $where2 = array();
       
        $where2['id'] = array('>',1);
        $where2['status'] = "normal";
       
        if(count($where3)<=0){
           
            $where3['year'] = date('Y',$date2);
            $where3['month'] = date('m',$date2);
            $where3['day'] = date('d',$date2);
        }
 

       
        $list = $this->model 
            ->where($where)
            ->where($where2)
            ->order($sort, $order)
            ->paginate($limit);
 
            foreach ($list as $key=>$value) {
                $weidakacishu =0;
                $chidaocishu =0;
                $zhengchangdaka = 0;
                //查询需要打卡多少次=》默认今日：
                $chouka = Db::name('chouka')->where(['admin_id'=>$value['id']])->where($where3)->select();
           
                //查询员工今日的上班打卡状态以及下班打卡的状态：
                
                //上班：
                $daka_shang = Db::name('daka')->where(['admin_id'=>$value['id'],'dakatime'=>$date2,'typelist'=>"0"])->find();
                if(!$daka_shang){
                      $list[$key]['daka_shang'] ="未打卡";
                }else{
                    if($daka_shang['ischidao']=="0"){
                         $list[$key]['daka_shang'] ="正常打卡";
                    }else{
                          $list[$key]['daka_shang'] ="迟到打卡";
                    }
                    
                }
                
                //下班：
                $daka_xia = Db::name('daka')->where(['admin_id'=>$value['id'],'dakatime'=>$date2,'typelist'=>"2"])->find();
                if(!$daka_xia){
                      $list[$key]['daka_xia'] ="未打卡";
                }else{
                    if($daka_xia['iszaotui']=="0"){
                         $list[$key]['daka_xia'] ="正常打卡";
                    }else{
                          $list[$key]['daka_xia'] ="早退打卡";
                    }
                    
                }
                
           
                if(!$chouka){
                    unset($list[$key]);
                  
                    continue;
                    $list[$key]['weidakacishu'] =$weidakacishu;
                    $list[$key]['chidaocishu'] =$chidaocishu;
                    $list[$key]['zhengchangdaka'] =$zhengchangdaka;
                    
                    $list[$key]['riqi'] = "未进行分配抽卡";
                    $list[$key]['choukacishu'] = 0;
               
                }
                
                    foreach ($chouka as $ke=>$ve){
                        if($ve['statuslist']=="0"){
                             
                              $weidakacishu +=1;
                        }
                        if($ve['statuslist']=="2"){
                             
                              $chidaocishu +=1;
                        }
                        if($ve['statuslist']=="1"){
                             
                              $zhengchangdaka +=1;
                        }
                        
                        
                    }
                     $list[$key]['choukaid'] =$chouka[0]['id'];;
                    $list[$key]['weidakacishu'] =$weidakacishu;
                    $list[$key]['chidaocishu'] =$chidaocishu;
                    $list[$key]['zhengchangdaka'] =$zhengchangdaka;
                    
                    $list[$key]['riqi'] = date("Y-m-d",$chouka[0]['choukatime']);
                    $list[$key]['choukacishu'] = count($chouka);
                
                
            }
            
        $finalList = [];
        foreach ($list as $item) {
            $finalList[] = $item;
        }
   
   
        $result = ['total' => count($finalList), 'rows' => $finalList];    
            
        //$result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
