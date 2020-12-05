<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 16:11
 */

namespace app\cms\model;

use think\facade\Db;

/**
 * 模型字段
 * Class ModelFieldModel
 * @package app\cms\model
 */
class ModelFieldModel extends BaseModel
{
    protected $name = 'model_field';
    protected $strict = false;

    //字段类型存放路径
    private $fieldPath = '';
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

    //array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
    protected $_validate = array(
        array('modelid', 'require', '请选择模型！'),
        array('formtype', 'require', '字段类型不能为空！'),
        array('field', 'require', '字段名称必须填写！'),
        array('field', 'isFieldUnique', '该字段名称已经存在！', 0, 'callback', 1),
        array('name', 'require', '字段别名必须填写！'),
        array('field', '/^[a-z_0-9]+$/i', '字段名只支持英文！', 0, 'regex', 3),
        array('isbase', array(0, 1), '是否作为基本信息设置错误！', 0, 'in', 3),
        array('isadd', array(0, 1), '是否前台投稿中显示设置错误！', 0, 'in', 3),
    );

    // 数据库配置
    protected $dbConfig;

    //初始化
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->fieldPath = app_path() . '/fields/';
        $this->dbConfig = getDbConfig();
    }

    //返回字段存放路径
    public function getFieldPath()
    {
        return $this->fieldPath;
    }

    /**
     * 验证字段名是否已经存在
     * @param string $fieldName
     * @return boolean false已经存在，true不存在
     */
    public function isFieldUnique($fieldName)
    {
        if (empty($fieldName)) {
            return true;
        }
        if ($this->where([['modelid', '=', $this->modelid], ['field', '=', $fieldName]])->count()) {
            return false;
        }
        return true;
    }

    /**
     * 获取可用字段类型列表
     * @return array
     */
    public function getFieldTypeList()
    {
        $fields = include $this->fieldPath . 'fields.inc.php';
        $fields = $fields ?: array();
        return $fields;
    }

    /**
     * 检查该字段是否允许添加
     * @param string $field 字段名称
     * @param string $field_type 字段类型
     * @param string $modelid 模型
     * @return boolean
     */
    public function isAddField($field, $field_type, $modelid)
    {
        //判断是否唯一字段
        if (in_array($field, $this->unique_fields)) {
            $f_datas = $this->where("modelid", $modelid)->field("field,field,formtype,name")->find();
            return empty($f_datas[$field]) ? true : false;
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
     * 判断字段是否允许被编辑
     * @param string $field 字段名称
     * @return boolean
     */
    public function isEditField($field)
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
     * 判断字段是否允许删除
     * @param string $field 字段名称
     * @return boolean
     */
    public function isDelField($field)
    {
        //禁止被删除的字段列表（字段名）
        if (in_array($field, $this->forbid_delete)) {
            return false;
        }
        return true;
    }

    /**
     * 根据模型ID，返回表名
     * @param $modelid
     * @param int $issystem
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getModelTableName($modelid, $issystem = 1)
    {
        //读取模型配置
        $model_cache = ModelModel::model_cache(true);
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
     *  编辑字段
     * @param array $data 编辑字段数据
     * @param int $fieldid 字段id
     * @return boolean
     */
    public function editField($data, $fieldid = 0)
    {
        if (!$fieldid && !isset($data['fieldid'])) {
            $this->error = '缺少字段id！';
            return false;
        } else {
            $fieldid = $fieldid ? $fieldid : (int)$data['fieldid'];
        }
        //原字段信息
        $info = $this->where("fieldid", $fieldid)->findOrEmpty();
        if ($info->isEmpty()) {
            $this->error = '该字段不存在！';
            return false;
        }
        $info = $info->toArray();
        //字段主表副表不能修改
        unset($data['issystem']);
        //字段类型
        if (empty($data['formtype'])) {
            $data['formtype'] = $info['formtype'];
        }
        //模型id
        $modelid = $info['modelid'];
        //完整表名获取 判断主表 还是副表
        $tablename = $this->getModelTableName($modelid, $info['issystem']);
        if (!$this->table_exists($tablename)) {
            $this->error = '数据表不存在！';
            return false;
        }
        //保存一份原始数据
        $oldData = $data;
        //字段附加配置
        $setting = $data['setting'];
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
        }
        //附加属性值
        $data['setting'] = serialize($setting);
        //进行数据验证
        $validate = new \app\cms\validate\Field();
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        // 该字段名称已经存在？
        $checkName = $this->isFieldUnique($data['field']);
        if (!$checkName) {
            $this->error = '该字段名称已经存在!';
            return false;
        }
        // 是否作为基本信息设置错误？
        if (!in_array($data['isbase'], [0, 1])) {
            $this->error = '作为基本信息设置错误!';
            return false;
        }
        // 是否前台投稿中显示设置错误？
        if (!in_array($data['isadd'], [0, 1])) {
            $this->error = '前台投稿中显示设置错误!';
            return false;
        }
        Db::startTrans();
        if (!empty($data)) {
            // 更新

            unset($data['pattern_select']);
            unset($data['field_minlength']);
            unset($data['field_maxlength']);

            if (false !== $this->where("fieldid", $fieldid)->save($data)) {
                //清理缓存
                cache('ModelField', NULL);
                //如果字段名变更
                if ($data['field'] && $info['field']) {
                    //检查段是否存在，只有当字段名改变才检测
                    if ($data['field'] != $info['field'] && $this->field_exists($tablename, $data['field'])) {
                        $this->error = '该字段已经存在！';
                        //回滚
                        $this->where("fieldid", $fieldid)->save($info);
                        return false;
                    }
                    //合并字段更改后的
                    $newInfo = array_merge($info, $data);
                    $newInfo['setting'] = unserialize($newInfo['setting']);
                    $field = array(
                        'tablename' => $this->dbConfig['prefix'] . $tablename,
                        'newfilename' => $data['field'],
                        'oldfilename' => $info['field'],
                        'maxlength' => $newInfo['maxlength'],
                        'minlength' => $newInfo['minlength'],
                        'defaultvalue' => $newInfo['setting']['defaultvalue'] ?? '',
                        'minnumber' => $newInfo['setting']['minnumber'] ?? '',
                        'decimaldigits' => $newInfo['setting']['decimaldigits'] ?? '',
                        'comment' => $data['name'] //字段别名 即为字段注释
                    );
                    if (false === $this->editFieldSql($field_type, $field)) {
                        $this->error = '数据库字段结构更改失败！';
                        //回滚
                        $this->where(array("fieldid" => $fieldid))->save($info);
                        return false;
                    }
                }
                Db::commit();
                return true;
            } else {
                Db::rollback();
                $this->error = '数据库更新失败！';
                return false;
            }
        } else {
            Db::rollback();
            return false;
        }
    }


    /**
     * 添加字段
     * @param array $data 字段相关数据
     * @return boolean
     */
    public function addField($data)
    {
        //保存一份原始数据
        $oldData = $data;
        //字段附加配置
        $setting = $data['setting'];
        //附加属性值
        $data['setting'] = serialize($setting);
        //模型id
        $modelid = $data['modelid'];
        //完整表名获取 判断主表 还是副表
        $tablename = $this->getModelTableName($modelid, $data['issystem']);
        if (!$this->table_exists($tablename)) {
            $this->error = '数据表不存在！';
            return false;
        }
        //进行数据验证
        $validate = new \app\cms\validate\Field();
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        // 该字段名称已经存在？
        $checkName = $this->isFieldUnique($data['field']);
        if (!$checkName) {
            $this->error = '该字段名称已经存在!';
            return false;
        }

        // 是否作为基本信息设置错误？
        if (!in_array($data['isbase'], [0, 1])) {
            $this->error = '作为基本信息设置错误!';
            return false;
        }
        // 是否前台投稿中显示设置错误？
        if (!in_array($data['isadd'], [0, 1])) {
            $this->error = '前台投稿中显示设置错误!';
            return false;
        }

        if ($data) {
            //检查字段是否存在
            if ($this->field_exists($tablename, $data['field'])) {
                $this->error = '该字段已经存在！';
                return false;
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
                $this->error = '该字段名称/类型不允许添加！';
                return false;
            }
            //增加字段
            $field = array(
                'tablename' => $this->dbConfig['prefix'] . $tablename,
                'fieldname' => $data['field'],
                'maxlength' => $data['maxlength'] ?? 0,
                'minlength' => $data['minlength'] ?? 9999,
                'defaultvalue' => $setting['defaultvalue'] ?? '',
                'minnumber' => $setting['minnumber'] ?? '',
                'decimaldigits' => $setting['decimaldigits'] ?? '',
                'comment' => $data['name'] //字段别名 即为字段注释
            );

            if ($this->addFieldSql($field_type, $field)) {
                $fieldid = $this->insertGetId($data);
                //清理缓存
                cache('ModelField', NULL);
                if ($fieldid) {
                    return $fieldid;
                } else {
                    $this->error = '字段信息入库失败！';
                    //回滚
                    $this->execute("ALTER TABLE  `{$field['tablename']}` DROP  `{$field['fieldname']}`");
                    return false;
                }
            } else {
                $this->error = '数据库字段添加失败！';
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 删除字段
     * @param $fieldId 字段id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteField($fieldId)
    {
        //原字段信息
        $info = $this->where("fieldid", $fieldId)->findOrEmpty();
        if ($info->isEmpty()) {
            $this->error = '该字段不存在！';
            return false;
        }
        //模型id
        $modelid = $info['modelid'];
        //完整表名获取 判断主表 还是副表
        $tablename = $this->getModelTableName($modelid, $info['issystem']);
        if (!$this->table_exists($tablename)) {
            $this->error = '数据表不存在！';
            return false;
        }
        //判断是否允许删除
        if (false === $this->isDelField($info['field'])) {
            $this->error = '该字段不允许被删除！';
            return false;
        }

        if ($this->deleteFieldSql($info['field'], $this->dbConfig['prefix'] . $tablename)) {
            $this->where('fieldid', $fieldId)
                ->where('modelid', $modelid)
                ->delete();
            return true;
        } else {
            $this->error = '数据库表字段删除失败！';
            return false;
        }
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
    protected function addFieldSql($field_type, $field)
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
    protected function editFieldSql($field_type, $field)
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
    protected function deleteFieldSql($filename, $tablename)
    {
        //不带表前缀的表名
        $noprefixTablename = str_replace($this->dbConfig['prefix'], '', $tablename);
        if (empty($tablename) || empty($filename)) {
            $this->error = '表名或者字段名不能为空！';
            return false;
        }

        if (false === $this->table_exists($noprefixTablename)) {
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
     * 生成模型字段缓存
     * @param bool $isForce
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function model_field_cache($isForce = false)
    {
        if (!$isForce) {
            $data = cache('ModelField');
            if (!empty($data)) {
                return $data;
            }
        }
        $cache = array();
        $modelList = ModelModel::select();
        foreach ($modelList as $info) {
            $data = self::where([
                ['modelid', '=', $info['modelid']],
                ['disabled', '=', 0],
            ])->order("listorder", "ASC")->select();
            $fieldList = array();
            if (!$data->isEmpty()) {
                foreach ($data as $rs) {
                    $fieldList[$rs['field']] = $rs;
                }
            }
            $cache[$info['modelid']] = $fieldList;
        }
        cache('ModelField', $cache);
        return $cache;
    }

    /**
     * 执行添加字段回调
     * todo : 暂未处理回调结果
     */
    public function contentModelEditFieldBehavior($params)
    {
        $field = $this->where($params)->find();
        if ($field['formtype'] == 'box') {
            //如果为选项组件，反序列化设置参数
            $setting = unserialize($field['setting']);
            if (!isset($setting['relation'])) return true;
            if ($setting['relation'] == 1) {
                return true;
            }
            return true;
        }
    }

    /**
     * 填补默认数据
     * @param array $setting
     * @return array
     */
    public function getDefaultSettingData($setting = [])
    {
        // 填充扩展配置
        //后台信息处理函数
        if (!isset($setting['backstagefun'])) $setting['backstagefun'] = '';
        //后台信息处理函数 (入库类型)
        if (!isset($setting['backstagefun_type'])) $setting['backstagefun_type'] = 1;
        //前台信息处理函数
        if (!isset($setting['frontfun'])) $setting['frontfun'] = '';
        //前台信息处理函数 (入库类型)
        if (!isset($setting['frontfun_type'])) $setting['frontfun_type'] = 1;

        if (!isset($setting['enablehtml'])) $setting['enablehtml'] = '';
        if (!isset($setting['toolbar'])) $setting['toolbar'] = '';
        if (!isset($setting['enablesaveimage'])) $setting['enablesaveimage'] = '';

        if(!isset($setting['show_type'])) $setting['show_type'] = '';
        if(!isset($setting['options'])) $setting['options'] = '';
        if(!isset($setting['boxtype'])) $setting['boxtype'] = '';
        if(!isset($setting['outputtype'])) $setting['outputtype'] = '';
        if(!isset($setting['upload_allowext'])) $setting['upload_allowext'] = 'gif|jpg|jpeg|png|bmp';
        if(!isset($setting['watermark'])) $setting['watermark'] = 0;
        if(!isset($setting['isselectimage'])) $setting['isselectimage'] = 0;
        if(!isset($setting['images_width'])) $setting['images_width'] = 20;
        if(!isset($setting['images_height'])) $setting['images_height'] = 50;
        if(!isset($setting['upload_number'])) $setting['upload_number'] = 1;
        if(!isset($setting['maxnumber'])) $setting['maxnumber'] = 99999;
        if(!isset($setting['decimaldigits'])) $setting['decimaldigits'] = '-1';


        if (!isset($setting['width'])) $setting['width'] = '';
        if (!isset($setting['height'])) $setting['height'] = '';
        if (!isset($setting['mbtoolbar'])) $setting['mbtoolbar'] = '';
        if (!isset($setting['defaultvalue'])) $setting['defaultvalue'] = '';
        if (!isset($setting['fieldtype'])) $setting['fieldtype'] = 'mediumtext';
        if (!isset($setting['minnumber'])) $setting['minnumber'] = '';
        if (!isset($setting['size'])) $setting['size'] = '';
        if (!isset($setting['ispassword'])) $setting['ispassword'] = '';
        if (!isset($setting['relation'])) $setting['relation'] = '';
        if (!isset($setting['decimaldigits'])) $setting['decimaldigits'] = '';
        if (!isset($setting['format'])) $setting['format'] = '';
        if (!isset($setting['defaulttype'])) $setting['defaulttype'] = 0;
        if (!isset($setting['statistics'])) $setting['statistics'] = '';
        if (!isset($setting['downloadlink'])) $setting['downloadlink'] = '';
        if (!isset($setting['formtext']))  $setting['formtext'] = '';

        return $setting;
    }

}
