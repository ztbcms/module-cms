<?php
/**
 * Author: jayinton
 */

namespace app\cms\libs;


use app\cms\model\ContentModelModel;
use think\exception\InvalidArgumentException;
use think\facade\Config;
use think\facade\Db;

class TableOperator
{
    private $table_name = '';

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
     * 添加表
     *
     * @param  array  $tableConfig
     * @param  array  $fieldConfigList
     *
     * @return array
     */
    function addTable($tableConfig = [], $fieldConfigList = [])
    {
        $sql = $this->buildSql($tableConfig, $fieldConfigList);
        try {
            Db::execute($sql);
            return createReturn(true, null, '添加成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }

    /**
     * 构建sql
     *
     * @param  array  $tableConfig
     * @param  array  $fieldConfigList
     *
     * @return string
     */
    function buildSql($tableConfig = [], $fieldConfigList = [])
    {
        $_tableConfig = [
            'table'   => $tableConfig['table'] ?? '',
            'engine'  => $tableConfig['engine'] ?? 'InnoDB',
            'charset' => $tableConfig['charset'] ?? 'utf8mb4',
            'comment' => $tableConfig['name'] ?? '',
        ];

        $_fieldConfigList = [];
        foreach ($fieldConfigList as $fieldConfig) {
            switch (strtolower($fieldConfig['field_type'])) {
                case 'int':
                    $type = "int({$fieldConfig['field_length']}) ";
                    if (isset($fieldConfig['setting']) && isset($fieldConfig['setting']['is_unsigned'])) {
                        $type .= ' unsigned ';
                    }
                    break;
                case 'decimal':
                    // 整数+小数最大位数为11，
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
                    $type = strtolower($fieldConfig['field_type']);
            }
            $_fieldConfigList [] = [
                'field'   => $fieldConfig['field'],
                'type'    => $type,
                'default' => $fieldConfig['default'] ?? ($fieldConfig['null'] ? null : ''),
                'null'    => $fieldConfig['field_is_null'] == 1,
                'comment' => $fieldConfig['name'] ?? '',
                'key'     => $fieldConfig['field_key'] ?? '', // PRI => PRIMARY KEY, UNI => UNIQUE KEY, MUL=>KEY
                'extra'   => $fieldConfig['field_extra'] ?? '',// AUTO_INCREMENT
            ];
        }

        // 根据配置生成sql,并执行
        $sql_first = "CREATE TABLE `{$_tableConfig['table']}` (";
        $sql_last = ") ENGINE={$_tableConfig['engine']} CHARSET={$_tableConfig['charset']} COMMENT='{$_tableConfig['comment']}'";
        $sql_fields = [];
        $sql_key = [];

        foreach ($_fieldConfigList as $config) {
            $_null_str = !$config['null'] ? ' NOT NULL ' : '';
            $default_str = '';
            if (is_null($config['default'])) {
                if ($config['null']) {
                    $default_str = "DEFAULT NULL";
                }
            } else {
                if ($config['default'] !== '') {
                    $default_str = "DEFAULT '{$config['default']}'";
                }
            }

            $comment_str = !empty($config['comment']) ? "COMMENT '{$config['comment']}'" : '';
            $sql_fields [] = "`{$config['field']}` {$config['type']} {$_null_str} {$default_str} {$config['extra']} {$comment_str}";

            if (!empty($config['key'])) {
                switch ($config['key']) {
                    case 'PRI':
                        $sql_key [] = "PRIMARY KEY (`{$config['field']}`)";
                        break;
                    case 'UNI':
                        $sql_key [] = "UNIQUE KEY `{$config['field']}` (`{$config['field']}`)";
                        break;
                    case 'MUL':
                        $sql_key [] = "KEY `{$config['field']}` (`{$config['field']}`)";
                        break;
                }
            }
        }
        foreach ($sql_key as $item) {
            $sql_fields [] = $item;
        }

        $sql_field = join(',', $sql_fields);
        return $sql_first.$sql_field.$sql_last;
    }

    /**
     * 重命名表名
     *
     * @param $old_table_name string 旧表名
     * @param $table_name string  新表名
     *
     * @return array
     */
    function renameTable($old_table_name, $table_name)
    {
        if (empty($table_name)) {
            return createReturn(false, null, '请指定表名');
        }
        if ($this->existTable($table_name)) {
            return createReturn(false, null, '表 '.$table_name.' 已存在');
        }
        $sql = "RENAME TABLE  `{$old_table_name}` TO  `{$table_name}`;";
        var_dump($sql);
        try {
            Db::execute($sql);
            return createReturn(true, null, '修改成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }

    /**
     * 更新表说明
     *
     * @param $table_name
     *
     * @param  string  $comoment
     *
     * @return array
     */
    function updateTableComment($table_name, string $comoment)
    {
        if (empty($table_name)) {
            return createReturn(false, null, '请指定表名');
        }
        if (!$this->existTable($table_name)) {
            return createReturn(false, null, '表 '.$table_name.' 已存在');
        }

        $old_table_name = $this->table_name;
        $sql = "ALTER TABLE  `{$table_name}` COMMENT='{$comoment}';";
        try {
            Db::execute($sql);
            return createReturn(true, null, '修改成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }

    /**
     * 删除表
     *
     * @param $table_name
     *
     * @return array
     */
    function deleteTable($table_name)
    {
        if (empty($table_name)) {
            return createReturn(false, null, '请指定表名');
        }
        if (!$this->existTable($table_name)) {
            return createReturn(false, null, '表 '.$table_name.' 不存在');
        }
        $sql = "DROP TABLE `{$table_name}`;";
        try {
            Db::execute($sql);
            return createReturn(true, null, '删除成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }

    /**
     * 是否存在表
     *
     * @param $table_name
     *
     * @return bool
     */
    function existTable($table_name)
    {
        $tables = $this->getTables();
        foreach ($tables as $t_name) {
            if (strtolower($t_name) == strtolower($table_name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取数据库全部表名
     *
     * @return array
     */
    function getTables()
    {
        $tables = [];
        $data = Db::query("SHOW TABLES");
        $config = Config::get('database');
        $dbConfig = $config['connections'][$config['default']];
        foreach ($data as $k => $v) {
            $tables[] = $v['Tables_in_'.$dbConfig['database']];
        }
        return $tables;
    }

    /**
     * 清空表
     *
     * @param $table_name
     *
     * @return array
     */
    function truncateTable($table_name)
    {
        if (!$this->existTable($table_name)) {
            return createReturn(false, null, '表 '.$table_name.' 不存在');
        }
        $sql = "TRUNCATE TABLE `{$table_name}`;";
        try {
            Db::execute($sql);
            return createReturn(true, null, '清空成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }
}