<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2020/12/5
 * Time: 19:07
 */

namespace app\cms\service;

use app\cms\model\model\Model;
use app\cms\model\model\ModelField;
use app\common\service\BaseService;
use think\facade\Db;

/**
 * 字段导出管理
 * Class ModelService
 * @package app\cms\service
 */
class FieldExportService extends BaseService
{

    /**
     * 获取模型导出数据
     * @param int $modelid
     * @return array
     */
    static function getExportModelFieldsInfo($modelid = 0){
        $result = [];
        if (!$modelid) {
            //全部自定义的模型
            $Model = new Model();
            $models = $Model->where("type", 0)->field('name,tablename,modelid')->select();
            foreach ($models as $index => $model) {
                $result[] = self::_getModelExportInfo($model['modelid']);
            }
        } else {
            $result[] = self::_getModelExportInfo($modelid);
        }
        return self::createReturn(true, $result);
    }

    /**
     * 获取单表的字段信息
     * @param $table
     * @return array
     */
    static function getExportTableFieldsInfo($table){
        $result = [];
        if (empty($table)) {
            return self::createReturn(false,[],'请输入表名');
        }

        $isTable = Db::query("SHOW TABLES LIKE '{$table}'");
        if (empty($isTable)) {
            return self::createReturn(false,[],'您输入的表不存在');
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

        return self::createReturn(true,$result);
    }


    /**
     * 获取模型导出数据
     * @param $modelId
     * @return array
     */
    static function _getModelExportInfo($modelId)
    {
        $where = [
            ['modelid', '=', $modelId],
            ['issystem', '=', 1], //主表
            ['disabled', '=', 0], //已启用
        ];

        $Model = new Model();
        $ModelField = new ModelField();


        $fields = $ModelField->where($where)->field('modelid,field,name,formtype,tips,setting')->select();
        $fields = empty($fields) ? [] : $fields;
        foreach ($fields as $index => $field) {
            $setting = unserialize($field['setting']);
            $fields[$index]['type'] = self::_getTypeByFromtype($field['formtype'], $setting);
            unset($fields[$index]['formtype']);
            unset($fields[$index]['setting']);
        }
        $tableInfo = $Model->where("modelid", $modelId)->field('name,tablename,modelid')->find();

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
    static function _getTypeByFromtype($formtype, $setting = [])
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


}