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
        $checkTableName = $tableOperator->existTable($data['tablename']);
        if ($checkTableName) {
            return self::createReturn(false, '', '该表名已经存在!');
        }

        // 检查sql文件是否正常
//        $checkTablesql = $tableOperator->checkTablesql();
//        if (!$checkTablesql) {
//            return createReturn(false, '', '创建模型所需要的SQL文件丢失，创建失败!');
//        }

        $data['add_time'] = time();
        $data['tablename'] = strtolower($data['tablename']);

        $contentModelModel = new ContentModelModel();
        $contentModelModel->startTrans();

        $modelid = $contentModelModel->insertGetId($data);
        if ($modelid) {
            //创建数据表
            // TODO ..
            if ($tableOperator->addTable([], [])) {
                $contentModelModel->commit();
                return self::createReturn(true, [
                    'modelid' => $data['id']
                ], '创建成功');
            } else {
                //表创建失败
                $contentModelModel->rollback();;
//                    $contentModelModel->where("modelid", $data['id'])->delete();
                return self::createReturn(false, '', '数据表创建失败');
            }
        } else {
            return self::createReturn(false, '', '创建模块失败');
        }
    }

    static function editModel($data)
    {

    }
}