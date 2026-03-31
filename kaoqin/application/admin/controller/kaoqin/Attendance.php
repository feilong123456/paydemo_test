<?php

namespace app\admin\controller\kaoqin;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\common\controller\Backend;
use fast\Random;
use fast\Tree;
use think\Db;
use think\Validate;

/**
 * 考勤组设置

 *
 * @icon fa fa-circle-o
 */
class Attendance extends Backend
{

    /**
     * Attendance模型对象
     * @var \app\admin\model\kaoqin\Attendance
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\kaoqin\Attendance;
        $this->childrenAdminIds = $this->auth->getChildrenAdminIds($this->auth->isSuperAdmin());
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin());

        $groupList = collection(AuthGroup::where('id', 'in', $this->childrenGroupIds)->select())->toArray();

        Tree::instance()->init($groupList);
        $groupdata = [];
        if ($this->auth->isSuperAdmin()) {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
            foreach ($result as $k => $v) {
                $groupdata[$v['id']] = $v['name'];
            }
        } else {
            $result = [];
            $groups = $this->auth->getGroups();
            foreach ($groups as $m => $n) {
                $childlist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n['id']));
                $temp = [];
                foreach ($childlist as $k => $v) {
                    $temp[$v['id']] = $v['name'];
                }
                $result[__($n['name'])] = $temp;
            }
            $groupdata = $result;
        }

        $this->view->assign('groupdata', $groupdata);
        $this->view->assign("hobbydataList", $this->model->getHobbydataList());
        $this->view->assign("typedataList", $this->model->getTypedataList());
    }
    
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
            foreach ($list as $ks=>$vs){
                $group_ids_arr = Db::name('auth_group')->where(['id'=>array('in',$vs['group_ids'])])->select();
                $group_ids_str = "";
                foreach ($group_ids_arr as $kv=>$vv){
                    
                    $group_ids_str .=$vv['name'].",";
                }
                $Attendanceshift = Db::name('attendanceshift')->field('name')->where(['id'=>$vs['attendanceshift_id']])->find();
                if($Attendanceshift){
                    $list[$ks]['attendanceshift_id'] = $Attendanceshift['name'];
                }else{
                    $list[$ks]['attendanceshift_id'] = "--考勤班次被删除--";
                }
                
                $list[$ks]['group_ids'] = substr($group_ids_str,0,-1);
                
                
            }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
    
    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        $group = $this->request->post("group/a");
        
        $params['group_ids'] = implode(",",$group);
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
        $this->success();
    }

    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
         
            $groupids = explode(",",$row->group_ids);
           
            $row->sstarttime = date("H:i:s",$row->starttime); 
            $row->sendtime = date("H:i:s",$row->endtime); 
            
            if(!empty($row->dates)){
                //字符串转数组：
                $dates_arr = explode(", ",$row->dates);
                $defaultDatesJson = json_encode($dates_arr);
                $this->view->assign("defaultDatesJson", $defaultDatesJson);
            }else{
                $dates_arr = array();
                $defaultDatesJson = json_encode($dates_arr);
                $this->view->assign("defaultDatesJson", $defaultDatesJson);
            }
            $this->view->assign("row", $row);
            $this->view->assign("groupids", $groupids);
      
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        $group = $this->request->post("group/a");
        
        $params['group_ids'] = implode(",",$group);
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
