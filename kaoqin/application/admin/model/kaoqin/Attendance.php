<?php

namespace app\admin\model\kaoqin;

use think\Model;


class Attendance extends Model
{

    

    

    // 表名
    protected $name = 'attendance';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'hobbydata_text',
        'starttime_text',
        'endtime_text',
        'typedata_text'
    ];
    

    
    public function getHobbydataList()
    {
        return ['1' => __('Hobbydata 1'), '2' => __('Hobbydata 2'), '3' => __('Hobbydata 3'), '4' => __('Hobbydata 4'), '5' => __('Hobbydata 5'), '6' => __('Hobbydata 6'), '7' => __('Hobbydata 7')];
    }

    public function getTypedataList()
    {
        return ['0' => __('Typedata 0'), '1' => __('Typedata 1'), '2' => __('Typedata 2')];
    }


    public function getHobbydataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['hobbydata']) ? $data['hobbydata'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getHobbydataList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
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

    protected function setHobbydataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setStarttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEndtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
