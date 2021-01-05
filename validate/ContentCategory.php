<?php

namespace app\cms\validate;

use think\Validate;

class ContentCategory extends Validate
{
    protected $rule
        = [
            'catname' => ['require'],
            'modelid' => ['require'],
            'type'    => ['require'],
            'catdir'  => ['require'],
        ];

    protected $message
        = [
            'catname.require' => '请填写栏目名称',
            'modelid.require' => '请选择模型',
            'type.require'    => '请选择类型',
            'catdir.require'  => '请填写栏目英文名称',
        ];
}
