<?php

namespace app\cms\validate;

use think\Validate;

/**
 * 字段验证器
 * Class Field
 * @package app\cms\validate
 */
class Field extends Validate
{
    protected $rule = [
        'modelid'  => ['require'],
        'form_type' => ['require'],
        'field'    => ['require', 'regex' => '/^[a-z_0-9]+$/i'],
        'field_type'    => ['require'],
        'name'     => ['require'],
    ];

    protected $message = [
        'modelid.require'  => '请选择模型',
        'form_type.require' => '字段表单类型不能为空',
        'field.require'    => '字段名称必须填写',
        'field_type.require'    => '字段类型必须填写',
        'field.regex'      => '字段名只支持英文',
        'name.require'     => '字段别名必须填写',
    ];
}
