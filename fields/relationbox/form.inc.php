<?php

/**
 * 关联栏目字段类型表单组合处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @param type $fieldinfo 字段配置
 * @return type
 */

function relationbox($field, $value, $fieldinfo)
{
    //错误提示
    $errortips = $fieldinfo['errortips'];
    if ($fieldinfo['minlength']) {
        //验证规则
        $this->formValidateRules['info['.$field.']'] = ["required" => true];
        //验证不通过提示
        $this->formValidateMessages['info['.$field.']'] = ["required" => $errortips ? $errortips : $fieldinfo['name']."不能为空！"];
    }
    //扩展配置
    $setting = unserialize($fieldinfo['setting']);
    if (is_null($value) || $value == '') {
        $value = $setting['defaultvalue'];
    }
    $catid = $setting['options'];
    $catInfo = getCategory($catid);
    $model = Content\Model\ContentModel::getInstance($catInfo['modelid']);
    $records=$model->where(['status' => 99])->select();
    foreach ($records as $record){
        $option[$record['id']] = $record[$setting['fieldkey']];
    }
    $values = explode(',', $value);
    $value = [];
    foreach ($values as $_k) {
        if ($_k != '')
            $value[] = $_k;
    }
    $value = implode(',', $value);
    switch ($setting['boxtype']) {
        case 'radio':
            $string = \Form::radio($option, $value, "name='info[$field]' {$fieldinfo['formattribute']}", $setting['width'], $field);
            break;

        case 'checkbox':
            $string = \Form::checkbox($option, $value, "name='info[$field][]' {$fieldinfo['formattribute']}", 1, $setting['width'], $field);
            break;

        case 'select':
            $string = \Form::select($option, $value, "name='info[$field]' id='$field' {$fieldinfo['formattribute']}");
            break;

        case 'multiple':
            $string = \Form::select($option, $value, "name='info[$field][]' id='$field ' size=2 multiple='multiple' style='height:60px;' {$fieldinfo['formattribute']}");
            break;
    }
    //如果设置了关联表，显示管理按钮
    if ($setting['relation'] == 1) {
        $id = $fieldinfo['fieldid'];
        $url = U('Content/BoxField/list', ['modelid' => $fieldinfo['modelid'], 'fieldid' => $fieldinfo['fieldid']]);
        $title = '管理'.$fieldinfo['name'];
        $string .= "<span style='margin-left: 20px;'><input type='button' onClick=\"omnipotent({$id},'{$url}','{$title}')\" class='btn btn-default' value='管理".$fieldinfo['name']."'></span>";
    }
    return $string;
}