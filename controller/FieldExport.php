<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 16:30
 */

namespace app\cms\controller;


use app\cms\model\ModelFieldModel;
use app\cms\model\ModelModel;
use app\common\controller\AdminController;
use think\facade\Db;

/**
 * 数据库表字段导出
 * Class FieldExport
 * @package app\cms\controller
 */
class FieldExport extends AdminController
{
    /**
     * 导出字段页
     */
    function exportModelFields()
    {
        return View('exportModelFields');
    }

    /**
     * 获取模型字段导出数据
     */
    private function _getModelExportInfo($modelId)
    {
        $where = [
            ['modelid', '=', $modelId],
            ['issystem', '=', 1], //主表
            ['disabled', '=', 0], //已启用
        ];

        $fields = ModelFieldModel::where($where)->field('modelid,field,name,formtype,tips,setting')->select();
        $fields = empty($fields) ? [] : $fields;
        foreach ($fields as $index => $field) {
            $setting = unserialize($field['setting']);
            $fields[$index]['type'] = $this->_getTypeByFromtype($field['formtype'], $setting);
            unset($fields[$index]['formtype']);
            unset($fields[$index]['setting']);
        }
        $tableInfo = ModelModel::where("modelid", $modelId)->field('name,tablename,modelid')->find();

        // 数据库配置
        $dbConfig = getDbConfig();
        $result = [
            'tablename'  => $dbConfig['prefix'] . $tableInfo['tablename'],
            'table_name' => $tableInfo['name'],
            'fields'     => $fields
        ];
        return $result;
    }


    /**
     * 根据表单字段类型获取对应的数类型
     * @param $formtype
     * @return string
     */
    private function _getTypeByFromtype($formtype, $setting = [])
    {
        switch ($formtype) {
            case 'author':
            case 'box':
            case 'copyfrom':
            case 'downfile':
            case 'downfiles':
            case 'editor':
            case 'image':
            case 'images':
            case 'keyword':
            case 'omnipotent':
            case 'pages':
            case 'posid':
            case 'tags':
            case 'template':
            case 'text':
            case 'textarea':
            case 'title':
            case 'typeid':
                return 'string';
            case 'islink':
            case 'catid':
            case 'datetime':
                return 'int';
            case 'number':
                if ($setting['decimaldigits'] == 0) {
                    return 'int';
                } else {
                    return 'float';
                }
        }
        return '';
    }

    /**
     * 获取模型导出数据
     *
     * 参数： modelid 为空的时候，获取全部
     */
    function getExportModelFieldsInfo()
    {
        $modelId = $this->request->param('modelid', '');
        $result = [];
        if (!$modelId) {
            //全部自定义的模型
            $models = ModelModel::where("type", 0)->field('name,tablename,modelid')->select();
            foreach ($models as $index => $model) {
                $result[] = $this->_getModelExportInfo($model['modelid']);
            }
        } else {
            $result[] = $this->_getModelExportInfo($modelId);
        }
        return self::makeJsonReturn(true, $result);
    }

    /**
     * 手动填写表名导出
     */
    function exportTableFields()
    {
        return View('exportTableFields');
    }

    /**
     * 获取单表的字段信息
     */
    function getExportTableFieldsInfo()
    {
        $table = trim($this->request->param('tablename'));
        $result = [];
        if (empty($table)) {
            return self::makeJsonReturn(false,[],'请输入表名');
        }

        $isTable = Db::query("SHOW TABLES LIKE '{$table}'");
        if (empty($isTable)) {
            return self::makeJsonReturn(false,[],'您输入的表不存在');
        }
        $dbName = getDbConfig('database');
        $sql = "select *  from information_schema.TABLES as a  where a.TABLE_SCHEMA = '{$dbName}' and a.TABLE_NAME= '{$table}' ";
        $tableInfos = Db::query($sql);
        $tableInfo = $tableInfos[0];

        $sql = "select a.column_name,a.data_type,a.CHARACTER_MAXIMUM_LENGTH,a.column_comment from information_schema.COLUMNS as a  where a.TABLE_SCHEMA = '{$dbName}' and a.TABLE_NAME= '{$table}' ";
        $fields =  Db::query($sql);

        $result_fields = [];
        foreach ($fields as $index => $field) {
            $result_fields [] = [
                'field' => $field['column_name'],
                'name'  => $field['column_comment'],
                'type'  => $field['data_type'],
                'tips'  => '/',
            ];
        }

        $result [] = [
            'tablename'  => $table,
            'table_name' => $tableInfo['TABLE_COMMENT'],
            'fields'     => $result_fields
        ];

        return self::makeJsonReturn(true,$result);
    }
}
