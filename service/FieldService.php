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
     * @param $modelId
     * @return array
     */
    static function getAllField($modelId)
    {
        //字段类型过滤
        $ModelField = new ModelField();
        $OperationField = new OperationField();
        $all_field = [];
        foreach ($ModelField->getFieldTypeList() as $formtype => $name) {
            if (!$OperationField->isAddField($formtype, $formtype, $modelId)) {
                continue;
            }
            $all_field[$formtype] = $name;
        }
        return $all_field;
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

}