<?php

namespace app\admin\model\laoban;

use think\Model;


class Daka extends Model
{

    

    

    // 表名
    protected $name = 'daka';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'dakatime_text',
        'typelist_text',
        'ischidao_text',
        'iszaotui_text'
    ];
    

    
    public function getTypelistList()
    {
        return ['0' => __('Typelist 0'), '1' => __('Typelist 1'), '2' => __('Typelist 2')];
    }

    public function getIschidaoList()
    {
        return ['0' => __('Ischidao 0'), '1' => __('Ischidao 1')];
    }

    public function getIszaotuiList()
    {
        return ['0' => __('Iszaotui 0'), '1' => __('Iszaotui 1')];
    }


    public function getDakatimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['dakatime']) ? $data['dakatime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTypelistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typelist']) ? $data['typelist'] : '');
        $list = $this->getTypelistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIschidaoTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['ischidao']) ? $data['ischidao'] : '');
        $list = $this->getIschidaoList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIszaotuiTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['iszaotui']) ? $data['iszaotui'] : '');
        $list = $this->getIszaotuiList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setDakatimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
