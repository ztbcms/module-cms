<?php

namespace app\cms\libs\module;

use think\facade\Config;
use app\common\service\BaseService;
use think\facade\Db;

class Operation extends BaseService
{

    const mainTableSql = 'data/Sql/cms_zhubiao.sql'; //模型主表SQL模板文件
//    const sideTablesSql = 'data/Sql/cms_zhubiao_data.sql'; //模型副表SQL模板文件
    const modelTablesInsert = 'data/Sql/cms_insert.sql'; //可用默认模型字段
    const membershipModelSql = 'data/Sql/cms_member.sql'; //会员模型

    // 系统数据库配置信息
    public $dbConfig;
    // 附件目录
    public $libPath;

    public function __construct(array $data = [])
    {
        $this->libPath = app_path();
        $config = Config::get('database');
        $this->dbConfig = $config['connections'][$config['default']];
    }

    /**
     * 删除表
     * @param $table 不带表前缀
     * @return bool
     */
    public function deleteTable($table)
    {
        if ($this->table_exists($table)) {
            $this->drop_table($table);
        }
        return true;
    }

    /**
     * 验证表是否存在
     * @param $tableName
     * @return bool
     */
    public function table_exists($tableName)
    {
        $tables = $this->list_tables();
        return in_array($this->dbConfig['prefix'] . $tableName, $tables) ? true : false;
    }

    /**
     *  读取全部表名
     * @return array
     */
    public function list_tables()
    {
        $tables = array();
        $data = Db::query("SHOW TABLES");
        foreach ($data as $k => $v) {
            $tables[] = $v['Tables_in_' . $this->dbConfig['database']];
        }
        return $tables;
    }

    /**
     * 删除表
     * @param string $tableName 不带表前缀的表名
     * @return mixed
     */
    public function drop_table($tableName)
    {
        $tableName = $this->dbConfig['prefix'] . $tableName;
        return Db::execute("DROP TABLE `$tableName`");
    }

    /**
     * 检查需要创建的表名是否为系统保留名称
     * @param $tableName $tableName 表名，不带表前缀
     * @return bool 存在返回false，不存在返回true
     */
    public function checkTablename($tableName){
        if (!$tableName) {
            return false;
        }
        //检查是否在保留内
        if (in_array($tableName, array("member_group", "member_content"))) {
            return false;
        }
        //检查该表名是否存在
        if ($this->table_exists($tableName)) {
            return false;
        }
        return true;
    }

    /**
     * 检查SQL文件是否存在！
     * @return bool
     */
    public function checkTablesql(){
        //检查主表结构sql文件是否存在
        if (!is_file($this->libPath . self::mainTableSql)) {
            return false;
        }
//        if (!is_file($this->libPath . self::sideTablesSql)) {
//            return false;
//        }
        if (!is_file($this->libPath . self::modelTablesInsert)) {
            return false;
        }
        if (!is_file($this->libPath . self::membershipModelSql)) {
            return false;
        }
        return true;
    }

    /**
     * 创建内容模型
     * @param string $tableName 模型主表名称（不包含表前缀）
     * @param string $modelId 模型id
     * @return boolean
     */
    public function createModel($tableName, $modelId)
    {
        if (empty($tableName) || $modelId < 1) {
            return false;
        }
        //表前缀
        $dbPrefix = $this->dbConfig['prefix'];
        //读取模型主表SQL模板
        $mainTableSqll = file_get_contents($this->libPath . self::mainTableSql);
        //副表
//        $sideTablesSql = file_get_contents($this->libPath . self::sideTablesSql);
        //字段数据
        $modelTablesInsert = file_get_contents($this->libPath . self::modelTablesInsert);
        //表前缀，表名，模型id替换
        $sqlSplit = str_replace(array('@cms@', '@zhubiao@', '@modelid@'), array($dbPrefix, $tableName, $modelId), $mainTableSqll  . "\n" . $modelTablesInsert);

        return $this->sql_execute($sqlSplit);
    }

    /**
     * 执行SQL
     * @param string $sqls SQL语句
     * @return boolean
     */
    public function sql_execute($sqls)
    {
        $sqls = $this->sql_split($sqls);
        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if (trim($sql) != '') {
                    Db::execute($sql);
                }
            }
        } else {
            Db::execute($sqls);
        }
        return true;
    }

    /**
     * SQL语句预处理
     * @param string $sql
     * @return array
     */
    public function sql_split($sql)
    {
        if ($this->dbConfig['charset']) {
            $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . $this->dbConfig['charset'], $sql);
        }
        if ($this->dbConfig['prefix'] != "cms_") {
            $sql = str_replace("cms_", $this->dbConfig['prefix'], $sql);
        }
        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query1) {
                $str1 = substr($query1, 0, 1);
                if ($str1 != '#' && $str1 != '-') {
                    $ret[$num] .= $query1;
                }

            }
            $num++;
        }
        return $ret;
    }
}