<?php
/**
 * User: FHYI
 * Date: 2020/10/29
 */

namespace app\cms\validate;

use think\Validate;

class Model extends Validate
{

    protected $rule = [
        'name'      => ['require'],
        'tablename' => ['require', 'regex' => '/^[a-zwd_]+$/i'],
    ];

    protected $message = [
        'name.require'      => '模型名称不能为空！',
        'tablename.regex'   => '模型表键名只支持英文！',
        'tablename.require' => '表名不能为空！',
    ];


}
