<?php

namespace app\admin\model\laoban;

use think\Model;


class Choukaxin extends Model
{

    

    

    // 表名
    protected $name = 'chouka';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'tuisongtime_text',
        'choukatime_text',
        'chouendtime_text',
        'dakatime_text',
        'dakalist_text',
        'jilunlist_text',
        'statuslist_text',
        'istuisonglist_text'
    ];
    

    
    public function getDakalistList()
    {
        return ['0' => __('Dakalist 0'), '1' => __('Dakalist 1'), '2' => __('Dakalist 2')];
    }

    public function getJilunlistList()
    {
        return ['1' => __('Jilunlist 1'), '2' => __('Jilunlist 2'), '3' => __('Jilunlist 3'), '4' => __('Jilunlist 4')];
    }

    public function getStatuslistList()
    {
        return ['0' => __('Statuslist 0'), '1' => __('Statuslist 1'), '2' => __('Statuslist 2')];
    }

    public function getIstuisonglistList()
    {
        return ['0' => __('Istuisonglist 0'), '1' => __('Istuisonglist 1')];
    }


    public function getTuisongtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tuisongtime']) ? $data['tuisongtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getChoukatimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['choukatime']) ? $data['choukatime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getChouendtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['chouendtime']) ? $data['chouendtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDakatimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['dakatime']) ? $data['dakatime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDakalistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['dakalist']) ? $data['dakalist'] : '');
        $list = $this->getDakalistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getJilunlistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['jilunlist']) ? $data['jilunlist'] : '');
        $list = $this->getJilunlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatuslistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['statuslist']) ? $data['statuslist'] : '');
        $list = $this->getStatuslistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIstuisonglistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['istuisonglist']) ? $data['istuisonglist'] : '');
        $list = $this->getIstuisonglistList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setTuisongtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setChoukatimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setChouendtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDakatimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
