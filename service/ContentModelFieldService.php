<?php
/**
 * Author: jayinton
 */

namespace app\cms\service;


use app\cms\model\ContentModelFieldModel;
use app\common\service\BaseService;

class ContentModelFieldService extends BaseService
{
    static function addModelField(array $modelField)
    {
        $data = [
            'modelid'       => $modelField['modelid'],
            'name'          => $modelField['name'],
            'tips'          => $modelField['tips'] ?? '',
            'setting'       => $modelField['setting'] ? serialize($modelField['setting']) : serialize([]),
            'form_type'     => $modelField['form_type'],
            'field'         => $modelField['field'],
            'field_type'    => $modelField['field_type'],
            'field_length'  => $modelField['field_length'],
            'default'       => $modelField['default'],
            'field_is_null' => $modelField['field_is_null'] ?? 0,
            'field_key'     => $modelField['field_key'] ?? '',
            'field_extra'   => $modelField['field_extra'] ?? '',
        ];

        $contentFieldModel = new ContentModelFieldModel();
        $res = $contentFieldModel->insert($data);
        if ($res) {
            return self::createReturn(true, null, '添加成功');
        }

        return self::createReturn(false, null, '添加失败');

    }

    static function updateModelField()
    {

    }
}