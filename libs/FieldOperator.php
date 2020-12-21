<?php
/**
 * Author: jayinton
 */

namespace app\cms\libs;


use app\cms\model\ContentModelModel;
use app\common\service\BaseService;
use think\exception\InvalidArgumentException;
use think\facade\Config;
use think\facade\Db;

/**
 * 字段操作
 * Class FieldOperator
 *
 * @package app\cms\libs
 */
class FieldOperator
{
    // 表名，完整表名
    private $table_name;

    static function getInstanceByModelId($model_id)
    {
        $instance = new self;
        $model = ContentModelModel::where('modelid', $model_id)->findOrEmpty();
        if (!$model) {
            throw new InvalidArgumentException('找不到模型');
        }
        $dbConfig = Config::get('database');
        // 补全字段
        $instance->table_name = $dbConfig['connections'][$dbConfig['default']]['prefix'].$model->tablename;
        return $instance;
    }

    static function getInstanceByTableName($table_name)
    {
        if (empty($table_name)) {
            throw new InvalidArgumentException('参数 table_name 不能为空');
        }
        $instance = new self;
        $instance->table_name = $table_name;

        return $instance;
    }

    /**
     * @return mixed
     */
    function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @param  mixed  $table_name
     */
    function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }

    /**
     * 添加字段
     *
     * @param  array  $fieldConfig
     *
     * @return array
     */
    function addField($fieldConfig = [])
    {
        $tablename = $this->table_name;
        $_field = [
            'field'   => $fieldConfig['field'],
            'type'    => $fieldConfig['type'],
            'default' => $fieldConfig['default'] ?? '',
            'null'    => $fieldConfig['null'] ?? false,
            'comment' => $fieldConfig['comment'] ?? '',
        ];
        if ($this->existField($_field['field'])) {
            return createReturn(false, null, '字段已存在');
        }
        $not_null = !$_field['null'] ? 'NOT NULL' : '';
        $sql = "ALTER TABLE `{$tablename}` ADD `{$_field['field']}` {$_field['type']} {$not_null} DEFAULT '{$_field['default']}'  COMMENT '{$_field['comment']}'";
        try {
            Db::execute($sql);
            return createReturn(true, null, '添加成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }

    /**
     * 编辑字段
     *
     * @param  array  $fieldConfig
     *
     * @return array
     */
    function editFeild($fieldConfig = [])
    {
        $tablename = $this->table_name;
        $_field = [
            'field'     => $fieldConfig['field'],
            'old_field' => $fieldConfig['old_field'], // 原字段名
            'type'      => $fieldConfig['type'],
            'default'   => $fieldConfig['default'] ?? '',
            'null'      => $fieldConfig['null'] ?? false,
            'comment'   => $fieldConfig['comment'] ?? '',
        ];
        if (!$this->existField($_field['field'])) {
            return createReturn(false, null, '字段不存在');
        }
        $not_null = !$_field['null'] ? 'NOT NULL' : '';
        $sql = "ALTER TABLE `{$tablename}` CHANGE `{$_field['old_field']}` `{$_field['field']}` {$_field['type']} {$not_null} DEFAULT '{$_field['default']}'  COMMENT '{$_field['comment']}'";
        try {
            Db::execute($sql);
            return createReturn(true, null, '更新成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }


    /**
     * 删除字段
     *
     * @param $field string 字段名
     *
     * @return array
     */
    function deleteField($field = '')
    {
        if (empty($field)) {
            return createReturn(false, null, '请指定需要删除的字段名称');
        }
        $tablename = $this->table_name;
        $_field = [
            'field' => $field,
        ];
        if (!$this->existField($_field['field'])) {
            return createReturn(false, null, '字段不存在');
        }
        $sql = "ALTER TABLE `{$tablename}` DROP `{$_field['field']}`";
        try {
            Db::execute($sql);
            return createReturn(true, null, '删除成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }

    /**
     * 是否存在该字段
     *
     * @param $field_name
     *
     * @return bool
     */
    function existField($field_name)
    {
        $fields = $this->getFields();
        $map = [];
        foreach ($fields as $i => $item) {
            $map[$item['Field']] = $item['Type'];
        }

        return array_key_exists($field_name, $map);
    }

    /**
     * 获取字段列表
     *
     * @return mixed
     */
    function getFields()
    {
        return Db::query("SHOW COLUMNS FROM ".$this->table_name);
    }
}