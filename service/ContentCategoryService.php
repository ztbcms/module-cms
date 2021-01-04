<?php
/**
 * Author: jayinton
 */

namespace app\cms\service;


use app\common\service\BaseService;

class ContentCategoryService extends BaseService
{
    function addContentCategory($model_data){
        $data = [
            'catname'                => $model_data['name'] ?? '',
            'type'         => $model_data['type'] ?? '',
            'modelid'         => $model_data['modelid'] ?? '',
            'parentid'   => $model_data['parentid'] ?? '',
            'arrchildid'   => $model_data['arrchildid'] ?? '',
            'catdir'   => $model_data['catdir'] ?? '',
            'parentdir'   => $model_data['parentdir'] ?? '',
            'url'   => $model_data['url'] ?? '',
            'setting'   => serialize($model_data['setting']) ?? serialize([]),
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
}