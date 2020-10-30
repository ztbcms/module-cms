<?php
/**
 * Created by FHYI.
 * Date 2020/10/30
 * Time 14:24
 */

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
        'formtype' => ['require'],
        'field'    => ['require', 'regex' => '/^[a-z_0-9]+$/i'],
        'name'     => ['require'],
    ];

    protected $message = [
        'modelid.require'  => '请选择模型！',
        'formtype.require' => '字段类型不能为空！',
        'field.require'    => '字段名称必须填写！',
        'field.regex'      => '字段名只支持英文！',
        'name.require'     => '字段别名必须填写！',
    ];
}
