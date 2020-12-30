<?php
/**
 * Author: jayinton
 */

namespace app\cms\libs;

use app\cms\model\ContentModelModel;
use think\exception\InvalidArgumentException;
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
        // 补全字段
        $instance->table_name = $model->table;
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
     * 构建字段
     * @param $fieldConfig
     *
     * @return array
     */
    function buildFieldConfig($fieldConfig){
        switch (strtolower($fieldConfig['field_type'])) {
            case 'int':
                $type = "int({$fieldConfig['field_length']}) ";
                if (isset($fieldConfig['setting']) && isset($fieldConfig['setting']['is_unsigned'])) {
                    $type .= ' unsigned ';
                }
                break;
            case 'decimal':
                // 整数+小数最大位数为11
                $decimals_amount = $fieldConfig['setting']['decimals_amount'] ?? 1;
                $type = "decimal({$fieldConfig['field_length']},{$decimals_amount}) ";
                if (isset($fieldConfig['setting']) && isset($fieldConfig['setting']['is_unsigned'])) {
                    $type .= ' unsigned ';
                }
                break;
            case 'varchar':
                $type = "varchar({$fieldConfig['field_length']})";
                break;
            default:
                // TEXT MEDIUMTEXT LONGTEXT
                $type = strtolower($fieldConfig['field_type']);
        }
        return [
            'field'   => $fieldConfig['field'],
            'type'    => $type,
            'default' => $fieldConfig['default'] ?? ($fieldConfig['null'] ? null : ''),
            'null'    => $fieldConfig['field_is_null'] == 1,
            'comment' => $fieldConfig['name'] ?? '',
            'key'     => $fieldConfig['field_key'] ?? '', // PRI => PRIMARY KEY, UNI => UNIQUE KEY, MUL=>KEY
            'extra'   => $fieldConfig['field_extra'] ?? '',// AUTO_INCREMENT
        ];
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
        $_field = $this->buildFieldConfig($fieldConfig);
        if ($this->existField($_field['field'])) {
            return createReturn(false, null, '字段已存在');
        }
        $not_null_str = !$_field['null'] ? 'NOT NULL' : '';
        $default_str = '';
        if (is_null($_field['default'])) {
            if ($_field['null']) {
                $default_str = "DEFAULT NULL";
            }
        } else {
            if ($_field['default'] !== '') {
                $default_str = "DEFAULT '{$_field['default']}'";
            }
        }
        $comment_str = !empty($_field['comment']) ? "COMMENT '{$_field['comment']}'" : '';
        $sql = "ALTER TABLE `{$tablename}` ADD `{$_field['field']}` {$_field['type']} {$not_null_str} {$default_str} {$comment_str}";
        try {
            Db::execute($sql);
            // TODO 字段添加索引
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
        $_field = $this->buildFieldConfig($fieldConfig);
        if (!$this->existField($_field['field'])) {
            return createReturn(false, null, '字段不存在');
        }
        $old_field_str = $fieldConfig['old_field'];
        $not_null_str = !$_field['null'] ? 'NOT NULL' : '';
        $default_str = '';
        if (is_null($_field['default'])) {
            if ($_field['null']) {
                $default_str = "DEFAULT NULL";
            }
        } else {
            if ($_field['default'] !== '') {
                $default_str = "DEFAULT '{$_field['default']}'";
            }
        }
        $comment_str = !empty($_field['comment']) ? "COMMENT '{$_field['comment']}'" : '';
        $sql = "ALTER TABLE `{$tablename}` CHANGE `{$old_field_str}` `{$_field['field']}` {$_field['type']} {$not_null_str} {$default_str}  {$comment_str};";
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