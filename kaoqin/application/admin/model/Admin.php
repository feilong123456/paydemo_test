<?php

namespace app\admin\model;

use think\Model;
use think\Session;

class Admin extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $hidden = [
        'password',
        'salt',
         'typedata_text',
         'CHOUKAdata_text'
    ];

    public static function init()
    {
        self::beforeWrite(function ($row) {
            $changed = $row->getChangedData();
            //如果修改了用户或或密码则需要重新登录
            if (isset($changed['username']) || isset($changed['password']) || isset($changed['salt'])) {
                $row->token = '';
            }
        });
    }
    public function getTypedataList()
    {
        return ['0' => "tg", '1' => "web", '2' => "两者均可"];
    }
    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getChoukadataList()
    {
        return ['0' => "tg", '1' => "web"];
    }
    public function getChoukadataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['choukadata']) ? $data['choukadata'] : '');
        $list = $this->getChoukadataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
