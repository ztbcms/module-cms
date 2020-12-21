<?php
/**
 * Author: jayinton
 */

namespace app\cms\libs;


use think\facade\Config;
use think\facade\Db;

class TableOperator
{
    function addTable()
    {
        // TODO 抽象表对象
    }

    /**
     * 删除表
     * @param $table_name
     *
     * @return array
     */
    function deleteTable($table_name)
    {

        if (empty($table_name)) {
            return self::createReturn(false, null, '请指定需要删除的表明');
        }
        if (!$this->existTable($table_name)) {
            return createReturn(false, null, '表 '.$table_name.' 不存在');
        }
        $sql = "DROP TABLE `{$table_name}`";
        try {
            Db::execute($sql);
            return createReturn(true, null, '删除成功');
        } catch (\Exception $exception) {
            return createReturn(false, null, $exception->getMessage());
        }
    }

    /**
     * 是否存在表
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
}