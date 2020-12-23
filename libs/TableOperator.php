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

    function buildSql($tableConfig = [], $fieldConfigList = [])
    {
        $_tableConfig = [
            'table'   => $tableConfig['table'] ?? '',
            'engine'  => $tableConfig['engine'] ?? 'InnoDB',
            'charset' => $tableConfig['charset'] ?? 'utf8mb4',
            'comment' => $tableConfig['comment'] ?? '',
        ];

        $_fieldConfigList = [];
        foreach ($fieldConfigList as $fieldConfig) {
            $_fieldConfigList [] = [
                'field'   => $fieldConfig['field'],
                'type'    => $fieldConfig['type'],
                'default' => $fieldConfig['default'] ?? ($fieldConfig['null'] ? null : ''),
                'null'    => $fieldConfig['null'] ?? false,
                'comment' => $fieldConfig['comment'] ?? '',
                'key'     => $fieldConfig['key'] ?? '', // PRI => PRIMARY KEY, UNI => UNIQUE KEY, MUL=>KEY
                'extra'   => $fieldConfig['extra'] ?? '',// AUTO_INCREMENT
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
            $sql_fields [] = "`{$config['field']}` {$config['type']} {$_null_str} {$default_str} {$config['extra']}";

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

    function renameTable($table_name)
    {
        if (empty($table_name)) {
            return createReturn(false, null, '请指定表名');
        }
        if (!$this->existTable($table_name)) {
            return createReturn(false, null, '表 '.$table_name.' 已存在');
        }

        $old_table_name = $this->table_name;
        $sql = "RENAME TABLE  `{$old_table_name}` TO  `{$table_name}`;";
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