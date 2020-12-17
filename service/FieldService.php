<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2020/12/5
 * Time: 19:33
 */

namespace app\cms\service;

use app\cms\libs\module\OperationField;
use app\cms\model\model\Model;
use app\cms\model\model\ModelField;
use app\common\service\BaseService;
use think\facade\Db;

/**
 * 字段管理
 * Class FieldExportService
 * @package app\cms\service
 */
class FieldService extends BaseService
{

    /**
     * 获取字段列表
     * @param $modelid
     * @return array
     */
    static function getFieldData($modelId = 0)
    {
        if (empty($modelId)) return self::createReturn(false, '', '参数错误！');
        $Model = new Model();
        $model = $Model->where("modelid", $modelId)->findOrEmpty();
        if ($model->isEmpty()) {
            return self::createReturn(false, '', '该模型不存在！');
        }
        $ModelField = new ModelField();
        $data = $ModelField->where("modelid", $modelId)->order("listorder", "ASC")->select();
        return self::createReturn(true, $data);
    }

    /**
     * 字段的启用和禁用
     * @param $fieldIds
     * @param $disabled
     * @return array
     */
    static function disabledField($fieldIds, $disabled)
    {
        Db::startTrans();
        $count = 0;

        $ModelField = new ModelField();
        foreach ($fieldIds as $fieldId) {
            $result = $ModelField->doDisable($fieldId, $disabled);
            if ($result['status']) {
                $count++;
            }
        }
        if ($count > 0) {
            Db::commit();
            return self::createReturn(true, '', '操作成功');
        } else {
            Db::rollback();
            return self::createReturn(false, '', '操作失败');
        }
    }

    /**
     * 删除指定字段
     * @param $fieldIds
     * @return array
     */
    static function delFields($fieldIds)
    {
        //字段ID
        Db::startTrans();

        $ModelField = new ModelField();

        foreach ($fieldIds as $index => $fieldid) {
            $res = $ModelField->doDelete($fieldid);
            if (!$res['status']) {
                Db::rollback();
                return self::createReturn(false, '', $res['msg']);
            }
        }
        Db::commit();
        return self::createReturn(true, '', '操作成功');
    }

    /**
     * 编辑字段排序
     * @param array $postData
     * @return array
     */
    static function listOrderFields($postData = [])
    {
        $ModelField = new ModelField();
        $res = $ModelField->transaction(function () use ($postData) {
            foreach ($postData['data'] as $item) {
                ModelField::where('fieldid', $item['fieldid'])
                    ->save(['listorder' => $item['listorder']]);
            }
            return true;
        });
        if ($res) {
            cache('Model', NULL);
            cache('ModelField', NULL);
            return self::createReturn(true, [], '排序更新成功!');
        } else {
            return self::createReturn(false, [], '排序失败!');
        }
    }

    /**
     * 获取可使用的字段
     * @deprecated  请使用 getAvailableFiles
     * @param $modelId
     * @return array
     */
    static function getAllField($modelId,$formtype = '')
    {
        //字段类型过滤
        $ModelField = new ModelField();
        $OperationField = new OperationField();
        if($formtype) {
            //不存在的字段用一个新数组分割开来

            //字段类型过滤
            $all_field = [];
            $no_all_field = [];
            foreach ($ModelField->getFieldTypeList() as $formtype => $name) {
                if (!$OperationField->isEditField($formtype)) {
                    $no_all_field[] = $formtype;
                }
                $all_field[$formtype] = $name;
            }
            //是否可以编辑数据类型
            if(in_array($formtype,$no_all_field)){
                $is_disabled_formtype = 1;
            } else {
                //不存在可编辑数组中
                $is_disabled_formtype = 0;
            }
            $res['all_field'] = $all_field;
            $res['is_disabled_formtype'] = $is_disabled_formtype;
        } else {
            //不存在的字段不进行显示
            $all_field = [];
            foreach ($ModelField->getFieldTypeList() as $formtype => $name) {
                if (!$OperationField->isAddField($formtype, $formtype, $modelId)) {
                    continue;
                }
                $all_field[$formtype] = $name;
            }

            $res['all_field'] = $all_field;
            $res['is_disabled_formtype'] = 1;
        }
        return $res;
    }

    static function getAvailableFiled(){
        $return =  [
            [
                'name' => '字符',
                'type' => 'text',
                'sql_type' => 'VARCHAR',
                'length' => 255,// 字段长度
                'setting' => [
                    'default_value' => '',
                ]
            ],
            [
                'name' => '文本',
                'type' => 'textarea',
                'sql_type' => 'TEXT',
                'length' => 512,
                'setting' => [
                    'default_value' => '',
                    'sql_type_list' => ['TEXT', 'MEDIUMTEXT', 'LONGTEXT'],
                ]
            ],
            [
                'name' => '编辑器',
                'type' => 'editor',
                'sql_type' => 'text',
                'length' => 0,
                'setting' => [
                    'default_value' => '',
                    'sql_type_list' => ['TEXT', 'MEDIUMTEXT', 'LONGTEXT'],
                ]
            ],
            [
                'name' => '数字',
                'type' => 'number',
                'sql_type' => 'int',
                'length' => 11,
                'setting' => [
                    'default_value' => '',
                    'decimals' => 0,// 小数点位数，0的时候位int 否则DECIMAL
                    'sql_type_list' => ['INT', 'DECIMAL'],
                ]
            ],
            [
                //TODO
                'name' => '日期',
                'type' => 'time',
                'sql_type' => 'int',
                'length' => 11
            ],
            [
                'name' => '单图片',
                'type' => 'image',
                'sql_type' => 'varchar',
                'length' => 512,
                'setting' => [
                    'default_value' => '',
                    'enable_watermark' => 0,
                ]
            ],
            [
                'name' => '多图片',
                'type' => 'images',
                'sql_type' => 'varchar',
                'length' => 1024,
                'setting' => [
                    'default' => '',
                    'enable_watermark' => 0,
                    'max_item' => 0, // 最大个数
                ]
            ],
            [
                'name' => '单视频',
                'type' => 'video',
                'sql_type' => 'varchar',
                'length' => 512,
                'setting' => [
                    'default' => '',
                ]
            ],
            [
                'name' => '多视频',
                'type' => 'videos',
                'sql_type' => 'varchar',
                'length' => 1024,
                'setting' => [
                    'default' => '',
                    'max_item' => 0, // 最大个数
                ]
            ],
            [
                'name' => '单文件',
                'type' => 'file',
                'sql_type' => 'varchar',
                'length' => 512,
                'setting' => [
                    'default' => '',
                ]
            ],
            [
                'name' => '多文件',
                'type' => 'files',
                'sql_type' => 'varchar',
                'length' => 1024,
                'setting' => [
                    'default' => '',
                    'max_item' => 0, // 最大个数
                ]
            ],
            [
                'name' => '单选',
                'type' => 'radio',
                'sql_type' => 'varchar',
                'length' => 512,
                'setting' => [
                    'default' => '',
                ]
            ],
            [
                'name' => '多选',
                'type' => 'checkbox',
                'sql_type' => 'varchar',
                'length' => 512,
                'setting' => [
                    'default' => '',
                ]
            ],
            [
                'name' => '下拉单选',
                'type' => 'select',
                'sql_type' => 'varchar',
                'length' => 512,
                'setting' => [
                    'default' => '',
                ]
            ],
            [
                'name' => '自定义',
                'type' => 'custom',
                'sql_type' => 'varchar',
                'length' => 255
            ],
        ];

        return self::createReturn(true, $return);
    }


    /**
     * 获取指定字段类型的配置
     * @param $fieldtype
     * @return array
     */
    static function getPublicFieldSetting($fieldtype)
    {
        $OperationFields = new OperationField();
        $fiepath = $OperationFields->fieldPath . $fieldtype . '/';
        //载入对应字段配置文件 config.inc.php
        include $fiepath . 'config.inc.php';
        $settings = array(
            'field_basic_table' => $field_basic_table,
            'field_minlength' => $field_minlength,
            'field_maxlength' => $field_maxlength,
            'field_allow_search' => $field_allow_search,
            'field_allow_fulltext' => $field_allow_fulltext,
            'field_allow_isunique' => $field_allow_isunique,
            'setting' => ''
        );
        return self::createReturn(true, $settings, '获取成功!');
    }

    /**
     * 添加字段
     * @param array $post
     * @param int $modelId
     * @return array
     */
    static function addField($post = [], $modelId = 0)
    {
        if (!isset($post) || empty($post)) {
            return self::createReturn(false, '', '数据不能为空!');
        }

        if(!isset($modelId) || empty($modelId)) {
            return self::createReturn(false, '', '模型ID不能为空');
        }

        $ModelField = new ModelField();
        $res = $ModelField->addField($post);
        if($res['status']) {
            //成功后执行回调
            $field = $post['field'];
            $params = array("modelid" => $modelId, 'field' => $field);
            $ModelField->contentModelEditFieldBehavior($params);
        }
        return $res;
    }

    /**
     * 获取字段详情
     * @deprecated  请使用 getFieldDetail
     * @param $modelId
     * @param $fieldId
     * @return array
     */
    static function getFieldDetails($modelId,$fieldId){

        $Model = new Model();
        $ModelField = new ModelField();

        //模型信息
        $modeData = $Model->where("modelid", $modelId)->findOrEmpty();

        //字段信息
        $fieldWhere[] = ["fieldid", "=", $fieldId];
        $fieldWhere[] = ["modelid", "=", $modelId];
        $fieldData = $ModelField->where($fieldWhere)->findOrEmpty();

        //字段设置
        $setting = unserialize($fieldData['setting']);

        //填补默认值
        $setting = $ModelField->getDefaultSettingData($setting);

        $OperationField = new OperationField();
        return self::createReturn(true,[
            'modeData' => $modeData,
            'setting' => $setting,
            'data' => $fieldData,
            'isEditField' => $OperationField->isEditField($fieldData['field'])
        ],'获取详情信息');

    }

    static function getFieldDetail($modelId, $fieldId)
    {
        $Model = new Model();
        $ModelField = new ModelField();

        //模型信息
        $modeData = $Model->where("modelid", $modelId)->findOrEmpty();

        //字段信息
        $fieldWhere[] = ["fieldid", "=", $fieldId];
        $fieldWhere[] = ["modelid", "=", $modelId];
        $fieldData = $ModelField->where($fieldWhere)->findOrEmpty();

        //字段设置
        $fieldData['setting'] = unserialize($fieldData['setting']);

        //填补默认值
        $fieldData['setting'] = $ModelField->getDefaultSettingData($fieldData['setting']);

        return self::createReturn(true, [
            'model_info' => $modeData,
            'field_info' => $fieldData,
        ]);

    }

    /**
     * 编辑字段
     * @param array $post
     * @param int $modelId
     * @param int $fieldId
     * @return array
     */
    static function editField($post = [], $modelId = 0,$fieldId = 0){
        if (!isset($post) || empty($post)) {
            return self::createReturn(false, '', '数据不能为空！');
        }

        if (!isset($modelId) || empty($modelId))  {
            return self::createReturn(false, '', '模型ID不能为空');
        }

        if (!isset($fieldId) || empty($fieldId)) {
            return self::createReturn(false, '', '字段ID不能为空！');
        }

        $ModelField = new ModelField();
        return $ModelField->editField($post, $fieldId);
    }

}