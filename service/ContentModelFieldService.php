<?php
/**
 * Author: jayinton
 */

namespace app\cms\service;


use app\cms\libs\FieldOperator;
use app\cms\model\ContentModelFieldModel;
use app\cms\model\model\ModelField;
use app\common\service\BaseService;
use think\facade\Db;

class ContentModelFieldService extends BaseService
{
    static function addModelField(array $modelField, $sync_table_structure = false)
    {
        $data = [
            'modelid'       => $modelField['modelid'],
            'name'          => $modelField['name'],
            'form_type'     => $modelField['form_type'],
            'field'         => $modelField['field'],
            'field_type'    => $modelField['field_type'],
            'field_length'  => $modelField['field_length'],
            'default'       => $modelField['default'],
            'field_is_null' => $modelField['field_is_null'] ?? 0,
            'field_key'     => $modelField['field_key'] ?? '',
            'field_extra'   => $modelField['field_extra'] ?? '',
            'tips'          => $modelField['tips'] ?? '',
            'setting'       => $modelField['setting'] ? serialize($modelField['setting']) : serialize([]),
        ];

        //进行数据验证
        $validate = new \app\cms\validate\Field();
        if (!$validate->check($data)) {
            return self::createReturn(false, '', $validate->getError());
        }
        $contentFieldModel = new ContentModelFieldModel();
        $contentFieldModel->startTrans();
        $res = $contentFieldModel->insert($data);
        if (!$res) {
            $contentFieldModel->rollback();
            return self::createReturn(false, null, '添加失败');
        }
        if ($sync_table_structure) {
            $fieldOperator = FieldOperator::getInstanceByModelId($modelField['modelid']);
            $res = $fieldOperator->addField($data);
            if (!$res['status']) {
                $contentFieldModel->rollback();
                return self::createReturn(false, null, $res['msg']);
            }
        }
        $contentFieldModel->commit();
        return self::createReturn(true, null, '添加成功');
    }

    static function updateModelField()
    {

    }

    /**
     * 添加字段
     *
     * @param  array  $post
     * @param  int  $modelId
     *
     * @return array
     */
    static function addField($post = [], $modelId = 0)
    {
        if (!isset($post) || empty($post)) {
            return self::createReturn(false, '', '数据不能为空!');
        }

        if (!isset($modelId) || empty($modelId)) {
            return self::createReturn(false, '', '模型ID不能为空');
        }

        $ModelField = new ModelField();
        $res = $ModelField->addField($post);
        if ($res['status']) {
            //成功后执行回调
            $field = $post['field'];
            $params = array("modelid" => $modelId, 'field' => $field);
            $ModelField->contentModelEditFieldBehavior($params);
        }
        return $res;
    }
}