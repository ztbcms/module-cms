<?php
/**
 * Author: jayinton
 */

namespace app\cms\service;


use app\cms\libs\TableOperator;
use app\cms\model\ContentModelModel;
use app\cms\model\model\Model;
use app\common\service\BaseService;

class ContentModelService extends BaseService
{
    private static $instance = null;

    static function getModel($model_id)
    {
        $Model = new Model();
        $data = $Model->where("modelid", $model_id)->findOrEmpty();
        if ($data) {
            return self::createReturn(true, $data->toArray());
        }
        return self::createReturn(false, null, '找不到信息');
    }

    /**
     * 添加模型
     *
     * @param $data
     *
     * @return array
     */
    static function addModel($data)
    {
        if (empty($data) && !isset($data)) {
            return createReturn(false, '', '提交数据不能为空！');
        }

        //数据验证
        $validate = new \app\cms\validate\Model();
        if (!$validate->check($data)) {
            return self::createReturn(false, '', $validate->getError());
        }

        // 是否重复模型名称
        $checkName = ContentModelModel::where([
            ['name', '=', $data['name']]
        ])->findOrEmpty();
        if (!$checkName->isEmpty()) {
            return self::createReturn(false, '', '该模型名称已经存在!');
        }

        // 是否重复表名称
        $tableOperator = new TableOperator();
        $checkTableName = $tableOperator->existTable($data['table']);
        if ($checkTableName) {
            return self::createReturn(false, '', '该表名已经存在!');
        }

        // 默认模型
        $defaultModelInfo = require app_path().'data/default_model.php';

        $data['table'] = strtolower($data['table']);

        $contentModelModel = new ContentModelModel();
        $contentModelModel->startTrans();

        try {
            $modelid = $contentModelModel->insertGetId($data);
            if ($modelid) {
                //创建数据表、添加字段信息
                $defaultModelInfo['table']['name'] = $data['name'];
                $defaultModelInfo['table']['table'] = $data['table'];
                $res = $tableOperator->addTable($defaultModelInfo['table'], $defaultModelInfo['fields']);
                // 添加字段信息
                if ($res['status']) {
                    foreach ($defaultModelInfo['fields'] as $fieldConfig) {
                        $fieldConfig['modelid'] = $modelid;
                        $res1 = ContentModelFieldService::addModelField($fieldConfig);
                        if (!$res1) {
                            $contentModelModel->rollback();
                            return self::createReturn(false, '', '字段信息创建失败');
                        }
                    }
                    $contentModelModel->commit();
                    return self::createReturn(true, [
                        'modelid' => $modelid
                    ], '创建成功');
                } else {
                    //表创建失败
                    $contentModelModel->rollback();
                    return self::createReturn(false, '', '数据表创建失败');
                }
            } else {
                return self::createReturn(false, '', '创建模块失败');
            }
        } catch (\Exception $e) {
            $contentModelModel->rollback();
            throw $e;
        }
    }

    static function editModel($data)
    {

    }
}