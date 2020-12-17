<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2020/12/5
 * Time: 11:35
 */

namespace app\cms\libs\module;

use app\cms\model\model\Model;
use app\cms\model\model\ModelField;
use app\common\service\BaseService;
use think\facade\Db;

class OperationField extends BaseService
{

    //字段类型存放路径
    public $fieldPath = '';

    //不显示的字段类型（字段类型）
    public $not_allow_fields = array('catid', 'typeid', 'title', 'keyword', 'template', 'username', 'tags');
    //允许添加但必须唯一的字段（字段名）
    public $unique_fields = array('pages', 'readpoint', 'author', 'copyfrom', 'islink', 'posid');
    //禁止被禁用（隐藏）的字段列表（字段名）
    public $forbid_fields = array(/*'catid',  'title' , 'updatetime', 'inputtime', 'url', 'listorder', 'status', 'template', 'username', 'allow_comment', 'tags' */);
    //禁止被删除的字段列表（字段名）
    public $forbid_delete = array(/*'catid', 'typeid', 'title', 'thumb', 'keyword', 'keywords', 'updatetime', 'tags', 'inputtime', 'posid', 'url', 'listorder', 'status', 'template', 'username', 'allow_comment'*/);
    //可以追加 JS和CSS 的字段（字段名）
    public $att_css_js = array('text', 'textarea', 'box', 'number', 'keyword', 'typeid');

    // 数据库配置
    public $dbConfig;

    //初始化
    public function __construct(array $data = [])
    {
        $this->fieldPath = app_path() . '/fields/';
        $this->dbConfig = getDbConfig();
    }

    /**
     * 根据模型ID，返回表名
     * @param $modelid
     * @param int $issystem
     * @return string
     */
    public function getModelTableName($modelid, $issystem = 1){
        //读取模型配置
        $model_cache = Model::model_cache(true);
        if (!isset($model_cache[$modelid])) {
            return '';
        }
        //表名获取
        $model_table = $model_cache[$modelid]['tablename'];
        //完整表名获取 判断主表 还是副表
        $tablename = $issystem ? $model_table : $model_table . "_data";
        return $tablename;
    }


    /**
     * 检查字段是否存在
     * $table 不带表前缀
     */
    function field_exists($table, $field)
    {
        $fields = $this->get_fields($table);
        return array_key_exists($field, $fields);
    }

    /**
     * 获取表字段
     * $table 不带表前缀
     */
    function get_fields($table)
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
     * 添加字段
     * @param int $modelid
     * @param array $data
     * @param array $oldData
     * @return array
     */
    function createField($modelid = 0,$data = [],$oldData = []){

        if(!is_numeric($modelid)) {
            return createReturn(false, '', 'modelid必须为数字！');
        }

        //完整表名获取 判断主表 还是副表
        $ModelField = new ModelField();

        $Operation = new Operation();
        $tablename = $this->getModelTableName($modelid, $data['issystem']);
        if (!$Operation->table_exists($tablename)) {
            return createReturn(false, '', '数据表不存在！');
        }

        //检查字段是否存在
        if ($this->field_exists($tablename, $data['field'])) {
            return createReturn(false, '', '该字段已经存在！');
        }

        //字段附加配置
        $setting = $data['setting'];
        //附加属性值
        $data['setting'] = serialize($setting);

        /**
         * 对应字段配置
         * $field_type = 'varchar'; //字段数据库类型
         * $field_basic_table = 1; //是否允许作为主表字段
         * $field_allow_index = 1; //是否允许建立索引
         * $field_minlength = 0; //字符长度默认最小值
         * $field_maxlength = ''; //字符长度默认最大值
         * $field_allow_search = 1; //作为搜索条件
         * $field_allow_fulltext = 0; //作为全站搜索信息
         * $field_allow_isunique = 1; //是否允许值唯一
         */
        require $this->fieldPath . "{$data['formtype']}/config.inc.php";
        //根据字段设置临时更改字段类型，否则使用字段配置文件配置的类型

        if (isset($oldData['setting']['fieldtype']) && $oldData['setting']['fieldtype']) {
            $field_type = $oldData['setting']['fieldtype'];
        }

        //特定字段类型强制使用特定字段名，也就是字段类型等于字段名
        if (in_array($field_type, $this->forbid_delete)) {
            $data['field'] = $field_type;
        }

        //将多余的字段去除,兼容tp6模块
        unset($data['field_minlength']);
        unset($data['field_maxlength']);
        unset($data['pattern_select']);

        //检查该字段是否允许添加
        if (false === $this->isAddField($data['field'], $data['formtype'], $modelid)) {
            return createReturn(false, '', '该字段名称/类型不允许添加！');
        }

        //增加字段
        $field = array(
            'tablename' => $this->dbConfig['prefix'] . $tablename,
            'fieldname' => $data['field'],
            'maxlength' => isset($setting['maxlength']) ? $setting['minnumber'] : 0,
            'minlength' => isset($setting['minlength']) ? $setting['minlength'] : 99,
            'defaultvalue' => isset($setting['defaultvalue']) ? $setting['defaultvalue'] : '',
            'minnumber' => isset($setting['minnumber']) ? $setting['minnumber'] : '',
            'decimaldigits' => isset($setting['decimaldigits']) ? $setting['minnumber'] : '',
            'comment' => $data['name'] //字段别名 即为字段注释
        );
        if ($this->addFieldSql($field_type, $field)) {
            $fieldid = $ModelField->insertGetId($data);
            //清理缓存
            cache('ModelField', NULL);
            if ($fieldid) {
                return createReturn(true, [
                    'fieldid' => $fieldid
                ], '字段信息入库成功！');
            } else {
                //回滚
                $this->execute("ALTER TABLE  `{$field['tablename']}` DROP  `{$field['fieldname']}`");
                return createReturn(false, '', '字段信息入库失败！');
            }
        } else {
            return createReturn(false, '', '数据库字段添加失败！');
        }
    }

    /**
     * 编辑字段
     * @param int $modelid
     * @param array $info
     * @param array $data
     * @param array $oldData
     * @return array
     */
    function editField($modelid = 0,$info = [],$data = [],$oldData = []){

        //字段附加配置
        $setting = $data['setting'];

        $Operation = new Operation();
        //完整表名获取 判断主表 还是副表
        $tablename = $this->getModelTableName($modelid, $info['issystem']);
        if (!$Operation->table_exists($tablename)) {
            return self::createReturn(false,'','数据表不存在');
        }

        /**
         * 对应字段配置
         * $field_type = 'varchar'; //字段数据库类型
         * $field_basic_table = 1; //是否允许作为主表字段
         * $field_allow_index = 1; //是否允许建立索引
         * $field_minlength = 0; //字符长度默认最小值
         * $field_maxlength = ''; //字符长度默认最大值
         * $field_allow_search = 1; //作为搜索条件
         * $field_allow_fulltext = 0; //作为全站搜索信息
         * $field_allow_isunique = 1; //是否允许值唯一
         */
        require $this->fieldPath . "{$data['formtype']}/config.inc.php";

        //根据字段设置临时更改字段类型，否则使用字段配置文件配置的类型
        if (isset($oldData['setting']['fieldtype'])) {
            $field_type = $oldData['setting']['fieldtype'];
        } else {
            return self::createReturn(false,'','字段类型不能为空');
        }

        //附加属性值
        $data['setting'] = serialize($setting);

        //字段id
        $fieldid = $info['fieldid'];

        Db::startTrans();
        if (!empty($data)) {
            // 更新
            unset($data['pattern_select']);
            unset($data['field_minlength']);
            unset($data['field_maxlength']);
            $ModelField = new ModelField();
            if (false !== $ModelField->where("fieldid", $fieldid)->update($data)) {
                //清理缓存
                cache('ModelField', NULL);
                //如果字段名变更
                if ($data['field'] && $info['field']) {

                    //检查段是否存在，只有当字段名改变才检测
                    if ($data['field'] != $info['field'] && $this->field_exists($tablename, $data['field'])) {
                        //回滚
                        $ModelField->where("fieldid", $fieldid)->update($info);
                        return self::createReturn(false,'','该字段已经存在！');
                    }

                    //合并字段更改后的
                    $newInfo = array_merge($info, $data);
                    $newInfo['setting'] = unserialize($newInfo['setting']);
                    $field = array(
                        'tablename' => $this->dbConfig['prefix'] . $tablename,
                        'newfilename' => $data['field'],
                        'oldfilename' => $info['field'],
                        'maxlength' => isset($newInfo['maxlength']) ? $newInfo['maxlength'] : 0,
                        'minlength' => isset($newInfo['minlength']) ? $newInfo['minlength'] : 0,
                        'defaultvalue' => isset($newInfo['setting']['defaultvalue']) ? $newInfo['setting']['defaultvalue'] : '',
                        'minnumber' => isset($newInfo['setting']['minnumber']) ? $newInfo['setting']['minnumber'] : '',
                        'decimaldigits' => isset($newInfo['setting']['decimaldigits']) ? $newInfo['setting']['decimaldigits'] : '',
                        'comment' => $data['name'] //字段别名 即为字段注释
                    );

                    if (false === $this->editFieldSql($field_type, $field)) {
                        //回滚
                        $ModelField->where(array("fieldid" => $fieldid))->update($info);
                        return self::createReturn(false,'',$this->error);
                    }
                }

                Db::commit();
                return self::createReturn(true,'','操作成功！');
            } else {
                Db::rollback();
                return self::createReturn(false,'','数据库更新失败！');
            }
        }
    }

    /**
     * 检查该字段是否允许添加
     * @param string $field 字段名称
     * @param string $field_type 字段类型
     * @param string $modelid 模型
     * @return boolean
     */
    function isAddField($field, $field_type, $modelid)
    {
        //判断是否唯一字段
        if (in_array($field, $this->unique_fields)) {
            $ModelField = new ModelField();
            $f_datas = $ModelField
                ->where([
                    ['modelid','=',$modelid],
                    ['field','=',$field]
                ])->field("field,formtype,name")->count();
            if($f_datas) {
                return false;
            } else {
                return true;
            }
        }
        //不显示的字段类型（字段类型）
        if (in_array($field_type, $this->not_allow_fields)) {
            return false;
        }
        //禁止被禁用的字段列表（字段名）
        if (in_array($field, $this->forbid_fields)) {
            return false;
        }
        //禁止被删除的字段列表（字段名）
        if (in_array($field, $this->forbid_delete)) {
            return false;
        }
        return true;
    }

    /**
     * 根据字段类型，增加对应的字段到相应表里面
     * @param string $field_type 字段类型
     * @param array $field 相关配置
     * $field = array(
     *      'tablename' 表名(完整表名)
     *      'fieldname' 字段名
     *      'maxlength' 最大长度
     *      'minlength' 最小值
     *      'defaultvalue' 默认值
     *      'minnumber' 是否正整数 和整数 1为正整数，-1是为整数
     *      'decimaldigits' 小数位数
     *      'comment' 字段注释
     * )
     * @return boolean
     */
    function addFieldSql($field_type, $field)
    {
        //表名
        $tablename = $field['tablename'];
        //字段名
        $fieldname = $field['fieldname'];
        //最大长度
        $maxlength = $field['maxlength'];
        //最小值
        $minlength = $field['minlength'];
        //默认值
        $defaultvalue = isset($field['defaultvalue']) ? $field['defaultvalue'] : '';
        //是否正整数 和整数 1为正整数，-1是为整数
        $minnumber = isset($field['minnumber']) ? $field['minnumber'] : 1;
        //小数位数
        $decimaldigits = isset($field['decimaldigits']) ? $field['decimaldigits'] : '';
        //字段注释
        $comment = isset($field['comment']) ? $field['comment'] : '';

        switch ($field_type) {
            case "varchar":
                if (!$maxlength) {
                    $maxlength = 255;
                }
                $maxlength = min($maxlength, 255);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` VARCHAR( {$maxlength} ) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "tinyint":
                if (!$maxlength) {
                    $maxlength = 3;
                }
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` TINYINT( {$maxlength} ) " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "number": //特殊字段类型，数字类型，如果小数位是0字段类型为 INT,否则是FLOAT
                $minnumber = intval($minnumber);
                $defaultvalue = $decimaldigits == 0 ? intval($defaultvalue) : floatval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` " . ($decimaldigits == 0 ? 'INT' : 'FLOAT') . " " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "smallint":
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` SMALLINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "mediumint":
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` INT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "int":
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` INT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "mediumtext":
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` MEDIUMTEXT" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "text":
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` TEXT" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "date":
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` DATE" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "datetime":
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "timestamp":
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case 'readpoint': //特殊字段类型
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD  `readpoint` SMALLINT(5) unsigned NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "double":
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` DOUBLE NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "float":
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` FLOAT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "bigint":
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` BIGINT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "longtext":
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}`  LONGTEXT " . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '数据库字段添加失败！';
                    return false;
                }
                break;
            case "char":
                $sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}`  CHAR(255) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "pages": //特殊字段类型
                $this->execute("ALTER TABLE `{$tablename}` ADD `paginationtype` TINYINT( 1 ) NOT NULL DEFAULT '0'" . " COMMENT '{$comment}'");
                $this->execute("ALTER TABLE `{$tablename}` ADD `maxcharperpage` MEDIUMINT( 6 ) NOT NULL DEFAULT '0'" . " COMMENT '{$comment}'");
                return true;
                break;
            default:
                return false;
                break;
        }
        return true;
    }

    /**
     * 判断字段是否允许被编辑
     * @param string $field 字段名称
     * @return boolean
     */
    function isEditField($field)
    {
        //判断是否唯一字段
        if (in_array($field, $this->unique_fields)) {
            return false;
        }
        //禁止被禁用的字段列表（字段名）
        if (in_array($field, $this->forbid_fields)) {
            return false;
        }
        //禁止被删除的字段列表（字段名）
        if (in_array($field, $this->forbid_delete)) {
            return false;
        }
        return true;
    }

    /**
     * 执行数据库表结构更改
     * @param string $field_type 字段类型
     * @param array $field 相关配置
     * $field = array(
     *      'tablename' 表名(完整表名)
     *      'newfilename' 新字段名
     *      'oldfilename' 原字段名
     *      'maxlength' 最大长度
     *      'minlength' 最小值
     *      'defaultvalue' 默认值
     *      'minnumber' 是否正整数 和整数 1为正整数，-1是为整数
     *      'decimaldigits' 小数位数
     *      'comment' 字段注释
     * )
     * @return boolean
     */
    function editFieldSql($field_type, $field)
    {
        //表名
        $tablename = $field['tablename'];
        //原字段名
        $oldfilename = $field['oldfilename'];
        //新字段名
        $newfilename = $field['newfilename'] ? $field['newfilename'] : $oldfilename;
        //最大长度
        $maxlength = $field['maxlength'];
        //最小值
        $minlength = $field['minlength'];
        //默认值
        $defaultvalue = isset($field['defaultvalue']) ? $field['defaultvalue'] : '';
        //是否正整数 和整数 1为正整数，-1是为整数
        $minnumber = isset($field['minnumber']) ? $field['minnumber'] : 1;
        //小数位数
        $decimaldigits = isset($field['decimaldigits']) ? $field['decimaldigits'] : '';
        //字段注释
        $comment = isset($field['comment']) ? $field['comment'] : '';

        if (empty($tablename) || empty($newfilename)) {
            $this->error = '表名或者字段名不能为空！';
            return false;
        }

        switch ($field_type) {
            case 'varchar':
                //最大值
                if (!$maxlength) {
                    $maxlength = 255;
                }
                $maxlength = min($maxlength, 255);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` VARCHAR( {$maxlength} ) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'tinyint':
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` TINYINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'number': //特殊字段类型，数字类型，如果小数位是0字段类型为 INT,否则是FLOAT
                $minnumber = intval($minnumber);
                $defaultvalue = $decimaldigits == 0 ? intval($defaultvalue) : floatval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` " . ($decimaldigits == 0 ? 'INT' : 'FLOAT') . " " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'smallint':
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` SMALLINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'mediumint':
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` MEDIUMINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'int':
                $minnumber = intval($minnumber);
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` INT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'mediumtext':
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` MEDIUMTEXT" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'text':
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` TEXT" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'date':
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` DATE" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'datetime':
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'timestamp':
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case 'readpoint': //特殊字段类型
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `readpoint` SMALLINT(5) unsigned NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "double":
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` DOUBLE NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "float":
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` FLOAT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "bigint":
                $defaultvalue = intval($defaultvalue);
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}`  BIGINT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "longtext":
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}`  LONGTEXT" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            case "char":
                $sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}`  CHAR(255) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
                if (false === $this->execute($sql)) {
                    $this->error = '字段结构更改失败！';
                    return false;
                }
                break;
            //特殊自定义字段
            case 'pages':
                break;
            default:
                $this->error = "字段类型" . $field_type . "不存在相应信息！";
                return false;
                break;
        }
        return true;
    }




    /**
     * 根据字段类型，删除对应的字段到相应表里面
     * @param string $filename 字段名称
     * @param string $tablename 完整表名
     * @return boolean
     */
    function deleteFieldSql($filename, $tablename)
    {
        //不带表前缀的表名
        $noprefixTablename = str_replace($this->dbConfig['prefix'], '', $tablename);
        if (empty($tablename) || empty($filename)) {
            $this->error = '表名或者字段名不能为空！';
            return false;
        }

        $Operation = new Operation();
        if (false === $Operation->table_exists($noprefixTablename)) {
            $this->error = '该表不存在！';
            return false;
        }


        switch ($filename) {
            case 'readpoint': //特殊字段类型
                $sql = "ALTER TABLE `{$tablename}` DROP `readpoint`;";
                if (false === Db::execute($sql)) {
                    $this->error = '字段删除失败！';
                    return false;
                }
                break;
            //特殊自定义字段
            case 'pages':
                if ($this->field_exists($noprefixTablename, "paginationtype")) {
                    Db::execute("ALTER TABLE `{$tablename}` DROP `paginationtype`;");
                }
                if ($this->field_exists($noprefixTablename, "maxcharperpage")) {
                    Db::execute("ALTER TABLE `{$tablename}` DROP `maxcharperpage`;");
                }
                break;
            default:
                $sql = "ALTER TABLE `{$tablename}` DROP `{$filename}`;";
                if (false === Db::execute($sql)) {
                    $this->error = '字段删除失败！';
                    return false;
                }
                break;
        }
        return true;
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