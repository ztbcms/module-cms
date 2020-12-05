<?php
/**
 * Created by FHYI.
 * Date 2020/10/30
 * Time 14:20
 */

namespace app\cms\model;

use think\facade\Db;
use think\Model;

/**
 * 基类
 * Class BaseModel
 * @package app\cms\model
 */
class BaseModel extends Model
{
    /**
     * 验证表是否存在
     * @param $tableName
     * @return bool
     */
    public function table_exists($tableName)
    {
        $tables = $this->list_tables();
        return in_array( getDbConfig()['prefix'] . $tableName, $tables) ? true : false;
    }



    /**
     * 读取全部表名
     * @return array
     */
    public function list_tables()
    {
        $tables = array();
        $data = Db::query("SHOW TABLES");
        foreach ($data as $k => $v) {
            $tables[] = $v['Tables_in_' . getDbConfig()['database']];
        }
        return $tables;
    }


    /**
     * 获取表字段
     * $table 不带表前缀
     */
    public function get_fields($table)
    {
        $fields = array();
        $table = getDbConfig()['prefix'] . $table;
        $data = Db::query("SHOW COLUMNS FROM $table");
        foreach ($data as $v) {
            $fields[$v['Field']] = $v['Type'];
        }
        return $fields;
    }

    /**
     * 检查字段是否存在
     * $table 不带表前缀
     */
    public function field_exists($table, $field)
    {
        $fields = $this->get_fields($table);
        return array_key_exists($field, $fields);
    }

    /**
     * 执行一段SQL
     * @param $sql
     * @return mixed
     */
    public function execute($sql){
        return Db::execute($sql);
    }
}
