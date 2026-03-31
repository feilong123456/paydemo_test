<?php

namespace app\admin\model\kaoqin;

use think\Model;


class Attendanceshift extends Model
{

    

    

    // 表名
    protected $name = 'attendanceshift';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'typelist_text',
        'worktime_text',
        'starttime_text',
        'endtime_text',
        'typedata_text',
        'reststarttime_text',
        'restendtime_text'
    ];
    

    
    public function getTypelistList()
    {
        return ['0' => __('Typelist 0'), '1' => __('Typelist 1')];
    }

    public function getTypedataList()
    {
        return ['0' => __('Typedata 0'), '1' => __('Typedata 1')];
    }


    public function getTypelistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typelist']) ? $data['typelist'] : '');
        $list = $this->getTypelistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getWorktimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['worktime']) ? $data['worktime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['starttime']) ? $data['starttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['endtime']) ? $data['endtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReststarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['reststarttime']) ? $data['reststarttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRestendtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['restendtime']) ? $data['restendtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setWorktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setStarttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEndtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setReststarttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setRestendtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
