<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 9:59
 */

namespace app\cms\model;

use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\Model;

/**
 * 模型管理
 * Class ModelModel
 * @package app\cms\model
 */
class ModelModel extends Model
{
    protected $name = 'model';
    protected $pk = 'modelid';

    const mainTableSql = 'data/Sql/cms_zhubiao.sql'; //模型主表SQL模板文件
    const sideTablesSql = 'data/Sql/cms_zhubiao_data.sql'; //模型副表SQL模板文件
    const modelTablesInsert = 'data/Sql/cms_insert.sql'; //可用默认模型字段
    const membershipModelSql = 'data/Sql/cms_member.sql'; //会员模型

    // 系统数据库配置信息
    protected $dbConfig;
    // 附件目录
    protected $libPath;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->libPath = app_path();
        $config = Config::get('database');
        $this->dbConfig = $config['connections'][$config['default']];
    }

    /**
     * 创建内容模型
     * @param string $tableName 模型主表名称（不包含表前缀）
     * @param string $modelId 模型id
     * @return boolean
     */
    protected function createModel($tableName, $modelId)
    {
        if (empty($tableName) || $modelId < 1) {
            return false;
        }
        //表前缀
        $dbPrefix = $this->dbConfig['prefix'];
        //读取模型主表SQL模板
        $mainTableSqll = file_get_contents($this->libPath . self::mainTableSql);
        //副表
        $sideTablesSql = file_get_contents($this->libPath . self::sideTablesSql);
        //字段数据
        $modelTablesInsert = file_get_contents($this->libPath . self::modelTablesInsert);
        //表前缀，表名，模型id替换
        $sqlSplit = str_replace(array('@cms@', '@zhubiao@', '@modelid@'), array($dbPrefix, $tableName, $modelId), $mainTableSqll . "\n" . $sideTablesSql . "\n" . $modelTablesInsert);

        return $this->sql_execute($sqlSplit);
    }


    /**
     * 检查需要创建的表名是否为系统保留名称
     * @param string $tableName 表名，不带表前缀
     * @return boolean 存在返回false，不存在返回true
     */
    public function checkTablename($tableName)
    {
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
    public function checkTablesql()
    {
        //检查主表结构sql文件是否存在
        if (!is_file($this->libPath . self::mainTableSql)) {
            return false;
        }
        if (!is_file($this->libPath . self::sideTablesSql)) {
            return false;
        }
        if (!is_file($this->libPath . self::modelTablesInsert)) {
            return false;
        }
        if (!is_file($this->libPath . self::membershipModelSql)) {
            return false;
        }
        return true;
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
     * 创建模型
     * @param array $data 提交数据
     * @return boolean
     */
    public function addModel($data)
    {
        if (empty($data)) {
            return false;
        }
        //数据验证
        $validate = new \app\cms\validate\Model();
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }

        // 是否重复模型名称
        $checkName = $this->where('name', '=', $data['name'])->findOrEmpty();
        if (!$checkName->isEmpty()) {
            $this->error = '该模型名称已经存在!';
            return false;
        }

        // 是否重复表名称
        $checkTableName = $this->checkTablename($data['tablename']);
        if (!$checkTableName) {
            $this->error = '该表名已经存在!';
            return false;
        }
        // 检查sql文件是否正常
        $checkTablesql = $this->checkTablesql();
        if (!$checkTablesql) {
            $this->error = '创建模型所需要的SQL文件丢失，创建失败！';
            return false;
        }

        $data['addtime'] = time();
        $this->startTrans();
        $data = $this->create($data);
        if ($data) {
            //强制表名为小写
            $data['tablename'] = strtolower($data['tablename']);
            //添加模型记录
            $modelid = $data->save($data->toArray());
            if ($modelid) {
                //创建数据表
                if ($this->createModel($data['tablename'], $data['modelid'])) {
                    Cache::set("Model", NULL);
                    $this->commit();
                    return $modelid;
                } else {
                    //表创建失败
                    $this->where("modelid", $modelid)->delete();
                    $this->error = '数据表创建失败！';
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 编辑模型
     * @param array $data 提交数据
     * @return boolean
     */
    public function editModel($data, $modelid = 0)
    {
        if (empty($data)) {
            return false;
        }
        //模型ID
        $modelid = $modelid ? $modelid : (int)$data['modelid'];
        if (!$modelid) {
            $this->error = '模型ID不能为空！';
            return false;
        }
        //查询模型数据
        $info = $this->where("modelid", $modelid)->findOrEmpty();
        if ($info->isEmpty()) {
            $this->error = '该模型不存在！';
            return false;
        }
        $data['modelid'] = $modelid;
        //数据验证
        $validate = new \app\cms\validate\Model();
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        // 是否重复模型名称
        $checkName = $this->where('name', '=', $data['name'])
            ->where('modelid', '<>', $data['modelid'])
            ->findOrEmpty();
        if (!$checkName->isEmpty()) {
            $this->error = '该模型名称已经存在!';
            return false;
        }
        // 检查sql文件是否正常
        $checkTablesql = $this->checkTablesql();
        if (!$checkTablesql) {
            $this->error = '创建模型所需要的SQL文件丢失，创建失败！';
            return false;
        }
        if ($data) {
            //强制表名为小写
            $data['tablename'] = strtolower($data['tablename']);
            //是否更改表名
            if ($info['tablename'] != $data['tablename'] && !empty($data['tablename'])) {
                //检查新表名是否存在
                if ($this->table_exists($data['tablename']) || $this->table_exists($data['tablename'] . '_data')) {
                    $this->error = '该表名已经存在！';
                    return false;
                }
                if (false !== $this->where(array("modelid" => $modelid))->save($data)) {
                    //表前缀
                    $dbPrefix = $this->dbConfig['prefix'];
                    //表名更改
                    if (!$this->sql_execute("RENAME TABLE  `{$dbPrefix}{$info['tablename']}` TO  `{$dbPrefix}{$data['tablename']}` ;")) {
                        $this->error = '数据库修改表名失败！';
                        return false;
                    }
                    //修改副表
                    if (!$this->sql_execute("RENAME TABLE  `{$dbPrefix}{$info['tablename']}_data` TO  `{$dbPrefix}{$data['tablename']}_data` ;")) {
                        //主表已经修改，进行回滚
                        $this->sql_execute("RENAME TABLE  `{$dbPrefix}{$data['tablename']}` TO  `{$dbPrefix}{$info['tablename']}` ;");
                        $this->error = '数据库修改副表表名失败！';
                        return false;
                    }
                    //更新缓存
                    self::model_cache(true);
                    return true;
                } else {
                    $this->error = '模型更新失败！';
                    return false;
                }
            } else {
                if (false !== $this->where("modelid", $modelid)->save($data)) {
                    return true;
                } else {
                    $this->error = '模型更新失败！';
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 删除表
     * @param $table string 不带表前缀
     * @return boolean
     */
    public function deleteTable($table)
    {
        if ($this->table_exists($table)) {
            $this->drop_table($table);
        }
        return true;
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
     * 根据模型ID删除模型
     * @param string $modelId 模型id
     * @return boolean
     */
    public function deleteModel($modelId)
    {
        if (empty($modelId)) {
            return false;
        }
        //这里可以根据缓存获取表名
        $modelData = $this->where("modelid", $modelId)->findOrEmpty();
        if ($modelData->isEmpty()) {
            return false;
        }
        //表名
        $modelTable = $modelData['tablename'];
        $this->startTrans();
        //删除模型数据
        $this->where("modelid", $modelId)->delete();
        //更新缓存
        self::model_cache(true);
        //删除所有和这个模型相关的字段
        ModelFieldModel::where("modelid", $modelId)->delete();
        //删除主表
        $this->deleteTable($modelTable);
        if ((int)$modelData['type'] == 0) {
            //删除副表
            $this->deleteTable($modelTable . "_data");
        }
        $this->commit();
        return true;
    }

    /**
     * 模型导入
     * @param array $data 数据
     * @param string $tablename 导入的模型表名
     * @param string $name 模型名称
     * @return int|boolean
     */
    public function import($data, $tablename = '', $name = '')
    {
        if (empty($data)) {
            $this->error = '没有导入数据！';
            return false;
        }
        //解析
        $data = json_decode(base64_decode($data), true);
        if (empty($data)) {
            $this->error = '解析数据失败，无法进行导入！';
            return false;
        }
        //取得模型数据
        $model = $data['model'];
        if (empty($model)) {
            $this->error = '解析数据失败，无法进行导入！';
            return false;
        }
        if ($name) {
            $model['name'] = $name;
        }
        if ($tablename) {
            $model['tablename'] = $tablename;
        }
        //导入
        $modelid = $this->addModel($model);
        if ($modelid) {
            if (!empty($data['field'])) {
                foreach ($data['field'] as $value) {
                    $value['modelid'] = $modelid;
                    if ($value['setting']) {
                        $value['setting'] = unserialize($value['setting']);
                    }
                    $model = new ModelFieldModel();
                    // TODO 添加字段
                    if ($model->addField($value) == false) {
                        $value['setting'] = serialize($value['setting']);
                        $model->where(array('modelid' => $modelid, 'field' => $value['field'], 'name' => $value['name']))->save($value);
                    }
                    unset($model);
                }
            }
            return $modelid;
        } else {
            return false;
        }
    }

    /**
     * 模型导出
     * @param $modelId
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($modelId)
    {
        if (empty($modelId)) {
            $this->error = '请指定需要导出的模型！';
            return false;
        }
        //取得模型信息
        $info = $this->where(array('modelid' => $modelId, 'type' => 0))->findOrEmpty();
        if (empty($info)) {
            $this->error = '该模型不存在，无法导出！';
            return false;
        }
        unset($info['modelid']);
        //数据
        $data = array();
        $data['model'] = $info;
        //取得对应模型字段
        $fieldList = ModelFieldModel::where('modelid', $modelId)->select();
        if (empty($fieldList)) {
            $fieldList = array();
        }
        //去除fieldid，modelid字段内容
        foreach ($fieldList as $k => $v) {
            unset($fieldList[$k]['fieldid'], $fieldList[$k]['modelid']);
        }
        $data['field'] = $fieldList;
        return base64_encode(json_encode($data));
    }

    //兼容方法...
    public function delete_model($modelid)
    {
        return $this->deleteModel($modelid);
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

    /**
     * 根据模型类型取得数据用于缓存
     * @param null $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getModelAll($type = null)
    {
        $where = array('disabled' => 0);
        if (!is_null($type)) {
            $where['type'] = $type;
        }
        $data = self::where($where)->select();
        $Cache = array();
        foreach ($data as $v) {
            $Cache[$v['modelid']] = $v;
        }
        return $Cache;
    }

    /**
     * 生成模型缓存，以模型ID为下标的数组
     * 可用作获取
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function model_cache($isForce = false)
    {
        // 不强制则检查
        if(!$isForce){
            $check = cache('Model');
            if(empty($check)){
                $data = self::getModelAll();
                cache('Model', $data);
                return $data;
            }
            return $check;
        }
        $data = self::getModelAll();
        cache('Model', $data);
        return $data;
    }


    /**
     * 执行SQL
     * @param string $sqls SQL语句
     * @return boolean
     */
    protected function sql_execute($sqls)
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

}
