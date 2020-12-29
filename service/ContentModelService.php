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
     * @param $model_data
     *
     * @return array
     * @throws \Exception
     */
    static function addModel($model_data)
    {
        $data = [
            'name'                => $model_data['name'] ?? '',
            'description'         => $model_data['description'] ?? '',
            'table'               => strtolower($model_data['table'] ?? ''),
            'category_template'   => $model_data['category_template'] ?? '',
            'list_template'       => $model_data['list_template'] ?? '',
            'show_template'       => $model_data['show_template'] ?? '',
            'list_customtemplate' => $model_data['list_customtemplate'] ?? '',
            'add_customtemplate'  => $model_data['add_customtemplate'] ?? '',
            'edit_customtemplate' => $model_data['edit_customtemplate'] ?? '',
            'engine'              => $model_data['engine'] ?? 'InnoDB',
            'charset'             => $model_data['charset'] ?? 'utf8mb4',
            'create_time'         => time()
        ];

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

    /**
     * 编辑模型
     *
     * @param $model_data
     *
     * @return array
     */
    static function editModel($model_data)
    {
        $modelid = $model_data['modelid'];
        if (empty($modelid)) {
            return self::createReturn(false, '', '缺少参数 modelid');
        }
        $model = ContentModelModel::where('modelid', $modelid)->find()->toArray();
        $data = [
            'name'                => $model_data['name'] ?? $model['name'],
            'description'         => $model_data['description'] ?? $model['description'],
            'table'               => strtolower($model_data['table'] ?? $model['table']),
            'category_template'   => $model_data['category_template'] ?? $model['category_template'],
            'list_template'       => $model_data['list_template'] ?? $model['list_template'],
            'show_template'       => $model_data['show_template'] ?? $model['show_template'],
            'list_customtemplate' => $model_data['list_customtemplate'] ?? $model['list_customtemplate'],
            'add_customtemplate'  => $model_data['add_customtemplate'] ?? $model['add_customtemplate'],
            'edit_customtemplate' => $model_data['edit_customtemplate'] ?? $model['edit_customtemplate'],
            'engine'              => $model_data['engine'] ?? $model['engine'],
            'charset'             => $model_data['charset'] ?? $model['charset'],
            'update_time'         => time(),
        ];
        //数据验证
        $validate = new \app\cms\validate\Model();
        if (!$validate->check($data)) {
            return self::createReturn(false, '', $validate->getError());
        }
        $tableOperator = TableOperator::getInstanceByTableName($model['table']);
        $contentModelModel = new ContentModelModel();
        $contentModelModel->startTrans();
        try {
            $contentModelModel->where('modelid', $modelid)->save($data);
            // 是否重复模型名称
            if ($data['name'] !== $model['name']) {
                $checkName = ContentModelModel::where([
                    ['name', '=', $data['name']],
                    ['modelid', '<>', $modelid]
                ])->find();

                if ($checkName) {
                    return self::createReturn(false, '', "该模型名称 {$data['name']} 已经存在".$data['name'].'-'.$model['name']);
                }
                // 修改说明
                $res = $tableOperator->updateTableComment($model['table'], $data['name']);
                if (!$res['status']) {
                    $contentModelModel->rollback();
                    return self::createReturn(false, '', '修改说明失败');
                }
            }
            // 是否重复表名称
            if ($data['table'] !== $model['table']) {
                // 修改表名
                $res = $tableOperator->renameTable($model['table'], $data['table']);
                if (!$res['status']) {
                    $contentModelModel->rollback();
                    return self::createReturn(false, '', $res['msg']);
                }
            }

            $contentModelModel->commit();
            return self::createReturn(true, null, '修改成功');
        } catch (\Exception $e) {
            $contentModelModel->rollback();
            throw $e;
        }
    }

    /**
     * 删除模型
     * @param $modelid
     */
    static function deleteModel($modelid){
        //TODO  删除模型、模型字段
    }
}