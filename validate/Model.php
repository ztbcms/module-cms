<?php

namespace app\cms\validate;

use think\Validate;

class Model extends Validate
{

    protected $rule = [
        'name'      => ['require'],
        'table' => ['require', 'regex' => '/^[a-zwd_]+$/i'],
    ];

    protected $message = [
        'name.require'      => '模型名称不能为空！',
        'table.regex'   => '模型表键名只支持英文！',
        'table.require' => '表名不能为空！',
    ];


}
