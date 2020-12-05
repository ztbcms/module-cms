<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 16:30
 */

namespace app\cms\controller;

use app\cms\service\FieldExportService;
use app\common\controller\AdminController;

/**
 * 数据库表字段导出
 * Class FieldExport
 * @package app\cms\controller
 */
class FieldExport extends AdminController
{

    /**
     * 导出字段页
     * @return array|\think\response\View
     */
    function exportModelFields()
    {
        $action = input('action', '', 'trim');
        $modelId = input('modelid', '');

        if($action == 'getExportModelFieldsInfo') {
            //获取模型导出数据
           return FieldExportService::getExportModelFieldsInfo($modelId);
        }

        return View('exportModelFields');
    }

    /**
     * 手动填写表名导出
     */
    function exportTableFields()
    {
        $action = input('action', '', 'trim');
        $tablename = input('tablename', '');

        if($action == 'getExportTableFieldsInfo') {
            //获取单表的字段信息
            return FieldExportService::getExportTableFieldsInfo($tablename);
        }

        return View('exportTableFields');
    }
}
