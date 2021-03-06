<?php
/**
 * Author: jayinton
 */

namespace app\cms\service;


use app\cms\libs\FieldOperator;
use app\cms\model\ContentModelFieldModel;
use app\cms\model\model\Model;
use app\cms\model\model\ModelField;
use app\common\service\BaseService;
use think\exception\InvalidArgumentException;

/**
 * 模型字段
 *
 * @package app\cms\service
 */
class ContentModelFieldService extends BaseService
{
    /**
     * 添加字段
     * @param  array  $modelField
     * @param  false  $sync_table_structure
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function addModelField(array $modelField, $sync_table_structure = false)
    {
        $contentFieldModel = new ContentModelFieldModel();
        $name_exist = $contentFieldModel->where([
            ['modelid', '=', $modelField['modelid']],
            ['name', '=', $modelField['name']],
        ])->find();
        if ($name_exist) {
            return self::createReturn(false, null, '字段已存在,请勿重复操作');
        }

        // 识别字段类型
        $field_type = $modelField['field_type'] ?? '';
        if (empty($field_type)) {
            if (isset($modelField['setting']) && isset($modelField['setting']['sql_type'])) {
                // setting 中指定
                $field_type = $modelField['setting']['sql_type'];
            } else {
                // 模型类型兜底
                $field_type_info = self::getFormTypeInfoByFormType($modelField['form_type']);
                $field_type = $field_type_info['sql_type'];
            }
        }
        // 默认值
        $default_value = '';
        if (!isset($modelField['default'])) {
            if (isset($modelField['setting']) && isset($modelField['setting']['default_value'])) {
                // setting 中指定
                $default_value = $modelField['setting']['default_value'];
            } else {
                // 模型类型兜底
                $field_type_info = self::getFormTypeInfoByFormType($modelField['form_type']);
                $default_value = $field_type_info['default_value'];
            }
        }
        $data = [
            'modelid'       => $modelField['modelid'],
            'name'          => $modelField['name'],
            'form_type'     => $modelField['form_type'],
            'field'         => $modelField['field'],
            'field_type'    => $field_type,
            'field_length'  => $modelField['field_length'],
            'default'       => $default_value,
            'field_is_null' => $modelField['field_is_null'] ?? 0,
            'field_key'     => $modelField['field_key'] ?? '',
            'field_extra'   => $modelField['field_extra'] ?? '',
            'tips'          => $modelField['tips'] ?? '',
            'setting'       => $modelField['setting'] ? serialize($modelField['setting']) : serialize([]),
            'create_time'   => time(),
            'enable_edit_show' => $modelField['enable_edit_show'] ?? 1,
            'enable_delete' => $modelField['enable_delete'] ?? 1,
            'enable_list_show' => $modelField['enable_list_show'] ?? 1,
        ];
        //进行数据验证
        $validate = new \app\cms\validate\Field();
        if (!$validate->check($data)) {
            return self::createReturn(false, '', $validate->getError());
        }
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

    /**
     * 更新字段
     *
     * @param  array  $modelField
     * @param  bool  $sync_table_structure
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function updateModelField(array $modelField, $sync_table_structure = false)
    {
        $contentFieldModel = new ContentModelFieldModel();

        $fieldid = $modelField['fieldid'];
        if (empty($fieldid)) {
            return self::createReturn(false, null, '参数异常');
        }
        // 识别字段类型
        $field_type = $modelField['field_type'] ?? '';
        if (empty($field_type)) {
            if (isset($modelField['setting']) && isset($modelField['setting']['sql_type'])) {
                // setting 中指定
                $field_type = $modelField['setting']['sql_type'];
            } else {
                // 模型类型兜底
                $field_type_info = self::getFormTypeInfoByFormType($modelField['form_type']);
                $field_type = $field_type_info['sql_type'];
            }
        }
        // 默认值
        $default_value = '';
        if (!isset($modelField['default'])) {
            if (isset($modelField['setting']) && isset($modelField['setting']['default_value'])) {
                // setting 中指定
                $default_value = $modelField['setting']['default_value'];
            } else {
                // 模型类型兜底
                $field_type_info = self::getFormTypeInfoByFormType($modelField['form_type']);
                $default_value = $field_type_info['default_value'];
            }
        }
        $modelFieldInfo = $contentFieldModel->where('fieldid', $fieldid)->find()->toArray();
        $data = [
            'modelid'       => $modelField['modelid'],
            'name'          => $modelField['name'],
            'form_type'     => $modelField['form_type'],
            'field'         => $modelField['field'],
            'field_type'    => $field_type,
            'field_length'  => $modelField['field_length'],
            'default'       => $default_value,
            'field_is_null' => $modelField['field_is_null'] ?? 0,
            'field_key'     => $modelField['field_key'] ?? '',
            'field_extra'   => $modelField['field_extra'] ?? '',
            'tips'          => $modelField['tips'] ?? '',
            'setting'       => $modelField['setting'] ? serialize($modelField['setting']) : serialize([]),
            'enable_edit_show' => $modelField['enable_edit_show'] ?? 1,
            'enable_list_show' => $modelField['enable_list_show'] ?? 1,
            'update_time'   => time(),
        ];

        //进行数据验证
        $validate = new \app\cms\validate\Field();
        if (!$validate->check($data)) {
            return self::createReturn(false, null, '参数异常');
        }

        $contentFieldModel->startTrans();
        $res = $contentFieldModel->where('fieldid', $fieldid)->save($data);
        if (!$res) {
            $contentFieldModel->rollback();
            return self::createReturn(false, null, '添加失败');
        }
        if ($sync_table_structure) {
            $data['old_field'] = $modelFieldInfo['field'];// 旧字段
            $fieldOperator = FieldOperator::getInstanceByModelId($modelField['modelid']);
            $res = $fieldOperator->editFeild($data);
            if (!$res['status']) {
                $contentFieldModel->rollback();
                return self::createReturn(false, null, $res['msg']);
            }
        }
        $contentFieldModel->commit();
        return self::createReturn(true, null, '更新成功');
    }

    /**
     * 删除字段
     *
     * @param $fieldid string 字段ID
     * @param $sync_table_structure boolean 是否同步修改表结构
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function deleteModelField($fieldid, $sync_table_structure = false)
    {
        if (empty($fieldid)) {
            return self::createReturn(false, null, '参数异常');
        }
        $contentFieldModel = new ContentModelFieldModel();
        $contentFieldModel->startTrans();
        $modelField = $contentFieldModel->where('fieldid', $fieldid)->find();
        if (!$modelField) {
            $contentFieldModel->rollback();
            return self::createReturn(false, null, '找不到字段');
        }
        $modelField = $modelField->toArray();
        $contentFieldModel->where('fieldid', $fieldid)->delete();
        if ($sync_table_structure) {
            $fieldOperator = FieldOperator::getInstanceByModelId($modelField['modelid']);
            $res = $fieldOperator->deleteField($modelField['field']);
            if (!$res['status']) {
                $contentFieldModel->rollback();
                return self::createReturn(false, null, $res['msg']);
            }
        }
        $contentFieldModel->commit();
        return self::createReturn(true, null, '添加成功');
    }

    /**
     * 获取可用的表单类型
     *
     * @return array
     */
    static function getAvailableFormTypeList()
    {
        $return = [
            [
                'name'          => '字符',
                'type'          => 'text',
                'sql_type'      => 'VARCHAR',
                'length'        => 255,// 字段长度
                'default_value' => '',
                'setting'       => []
            ],
            [
                'name'          => '文本',
                'type'          => 'textarea',
                'sql_type'      => 'TEXT',
                'length'        => 0,
                'default_value' => '',
                'setting'       => [
//                    'sql_type' => '',//'TEXT', 'MEDIUMTEXT', 'LONGTEXT'
                ]
            ],
            [
                'name'          => '编辑器',
                'type'          => 'editor',
                'sql_type'      => 'text',
                'length'        => 0,
                'default_value' => '',
                'setting'       => [
//                    'sql_type' => '',//'TEXT', 'MEDIUMTEXT', 'LONGTEXT'
                ]
            ],
            [
                'name'          => '数字',
                'type'          => 'number',
                'sql_type'      => 'int',
                'length'        => 11,
                'default_value' => '0',
                'setting'       => [
                    'decimals_amount' => 0,// 小数点位数，0的时候位int 否则DECIMAL
                    'is_unsigned'     => 0,// 是否无符号 0否/1是
                    //                    'sql_type' => 'INT',//'INT', 'DECIMAL'
                ]
            ],
            //            [
            //                //TODO
            //                'name' => '日期',
            //                'type' => 'time',
            //                'sql_type' => 'int',
            //                'length' => 11
            //            ],
            [
                'name'          => '单图片',
                'type'          => 'image',
                'sql_type'      => 'varchar',
                'length'        => 512,
                'default_value' => '',
                'setting'       => [
                    'enable_watermark' => 0,
                ]
            ],
            [
                'name'          => '多图片',
                'type'          => 'images',
                'sql_type'      => 'varchar',
                'length'        => 1024,
                'default_value' => '',
                'setting'       => [
                    'enable_watermark' => 0,
                    'max_amount'       => 0, // 最大个数
                ]
            ],
            [
                'name'          => '单视频',
                'type'          => 'video',
                'sql_type'      => 'varchar',
                'length'        => 512,
                'default_value' => '',
                'setting'       => []
            ],
            [
                'name'          => '多视频',
                'type'          => 'videos',
                'sql_type'      => 'varchar',
                'length'        => 1024,
                'default_value' => '',
                'setting'       => [
                    'max_amount' => 0, // 最大个数
                ]
            ],
            //
            [
                'name'          => '单文件',
                'type'          => 'file',
                'sql_type'      => 'varchar',
                'length'        => 512,
                'default_value' => '',
                'setting'       => []
            ],
            [
                'name'          => '多文件',
                'type'          => 'files',
                'sql_type'      => 'varchar',
                'length'        => 1024,
                'default_value' => '',
                'setting'       => [
                    'max_amount' => 0, // 最大个数
                ]
            ],
            [
                'name'          => '单选',
                'type'          => 'radio',
                'sql_type'      => 'varchar',
                'length'        => 512,
                'default_value' => '',
                'setting'       => [
                    'options' => ''// 格式： 选项名称1|选项值1 ，每行一个
                ]
            ],
            [
                'name'          => '多选',
                'type'          => 'checkbox',
                'sql_type'      => 'varchar',
                'length'        => 512,
                'default_value' => '',
                'setting'       => [
                    'options'    => '',// 格式： 选项名称1|选项值1 ，每行一个
                    'max_amount' => ''
                ]
            ],
            [
                'name'          => '下拉单选',
                'type'          => 'select',
                'sql_type'      => 'varchar',
                'length'        => 512,
                'default_value' => '',
                'setting'       => [
                    'options' => ''
                ]
            ],
            [
                'name'          => '日期时间',
                'type'          => 'datetime',
                'sql_type'      => 'int',
                'length'        => 11,
                'default_value' => '',
                'setting'       => [
                    'format' => 'Y-m-d H:i:s' //Y-m-d H:i
                ]
            ]
        ];

        return self::createReturn(true, $return);
    }

    /**
     * 根据表单类型获取其表单类型秒速
     *
     * @param $form_type
     *
     * @return mixed
     */
    static function getFormTypeInfoByFormType($form_type)
    {
        $list = self::getAvailableFormTypeList()['data'];
        foreach ($list as $item) {
            if (strtolower($item['type']) == strtolower($form_type)) {
                return $item;
            }
        }
        throw new InvalidArgumentException('找不到表单类型');
    }

    /**
     * 获取字段详情
     *
     * @param $fieldid
     *
     * @return array
     */
    static function getFieldDetail($fieldid)
    {
        $Model = new Model();
        $ModelField = new ModelField();

        //字段信息
        $fieldWhere[] = ["fieldid", "=", $fieldid];
        $field_info = $ModelField->where($fieldWhere)->find();

        if (empty($field_info)) {
            return self::createReturn(false, null, '找不到信息');
        }

        $field_info = $field_info->toArray();
        //模型信息
        $model_info = $Model->where("modelid", $field_info['modelid'])->find();

        if (empty($field_info)) {
            return self::createReturn(false, null, '找不到信息');
        }
        $model_info = $model_info->toArray();

        //字段设置
        $field_info['setting'] = unserialize($field_info['setting']);

        return self::createReturn(true, [
            'model_info' => $model_info,
            'field_info' => $field_info,
        ]);
    }


    /**
     * 获取字段列表
     *
     * @param $modelid int|string 模型ID
     *
     * @return array
     */
    static function getModelFieldList($modelid)
    {
        if (empty($modelid)) {
            return self::createReturn(false, '', '参数错误');
        }
        $Model = new Model();
        $model = $Model->where("modelid", $modelid)->findOrEmpty();
        if ($model->isEmpty()) {
            return self::createReturn(false, '', '该模型不存在');
        }
        $ModelField = new ModelField();
        $list = $ModelField->where("modelid", $modelid)->order("listorder", "ASC")->select()->toArray();
        foreach ($list as &$item){
            $item['setting'] = unserialize($item['setting']);
        }
        return self::createReturn(true, $list);
    }

    /**
     * 获取可编辑的字段
     * @param $modelid
     */
    static function getEditableModelFieldList($modelid)
    {
        $list = self::getModelFieldList($modelid)['data'];
        $result = [];
        foreach ($list as $item) {
            if ($item['enable_edit_show'] == 1) {
                $result [] = $item;
            }
        }
        return self::createReturn(true, $result);
    }
}