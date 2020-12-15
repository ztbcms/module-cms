<?php
/**
 * User: cycle_3
 * Date: 2020/12/4
 * Time: 16:57
 */

namespace app\cms\model\model;

use app\cms\libs\module\Operation;
use app\cms\libs\module\OperationField;
use think\Model as PublicModel;

class ModelField extends PublicModel
{

    protected $name = 'content_model_field';

    /**
     * 添加字段
     * @param $data
     * @return array
     */
    public function addField($data)
    {
        //保存一份原始数据
        $oldData = $data;
        if(!isset($data) || empty($data)) {
            return createReturn(false, '', '数据不能为空！');
        }

        //模型id
        $modelid = $data['modelid'];

        $OperationField = new OperationField();

        //进行数据验证
        $validate = new \app\cms\validate\Field();
        if (!$validate->check($data)) {
            return createReturn(false, '', $validate->getError());
        }

        // 该字段名称已经存在？
        $checkName = $this->isFieldUnique($data['field']);
        if (!$checkName) {
            return createReturn(false, '', '该字段名称已经存在！');
        }

        // 是否作为基本信息设置错误？
        if (!in_array($data['isbase'], [0, 1])) {
            return createReturn(false, '', '作为基本信息设置错误！');
        }

        // 是否前台投稿中显示设置错误？
        if (!in_array($data['isadd'], [0, 1])) {
            return createReturn(false, '', '前台投稿中显示设置错误！');
        }

        $createFieldRes = $OperationField->createField($modelid,$data,$oldData);
        return $createFieldRes;
    }

    /**
     * 编辑字段
     * @param array $data
     * @param int $fieldid
     * @return array
     */
    public function editField($data = [],$fieldid = 0){

        //获取字段信息
        if (!$fieldid && !isset($data['fieldid'])) {
            return createReturn(false, '', '缺少字段id！');
        } else {
            $fieldid = $fieldid ? $fieldid : (int)$data['fieldid'];
        }

        //原字段信息
        $info = $this->where("fieldid", $fieldid)->findOrEmpty();
        if ($info->isEmpty()) {
            return createReturn(false, '', '该字段不存在！');
        }

        $info = $info->toArray();

        //字段主表副表不能修改
        unset($data['issystem']);
        //字段类型
        if (empty($data['formtype'])) $data['formtype'] = $info['formtype'];

        //模型id
        $modelid = $info['modelid'];

        //保存一份原始数据
        $oldData = $data;

        //进行数据验证
        $validate = new \app\cms\validate\Field();
        if (!$validate->check($data)) {
            return createReturn(false, '',$validate->getError());
        }

        // 该字段名称已经存在？
        $checkName = $this->isFieldUnique($data['field']);
        if (!$checkName) {
            return createReturn(false, '', '该字段名称已经存在！');
        }

        // 是否作为基本信息设置错误？
        if (!in_array($data['isbase'], [0, 1])) {
            return createReturn(false, '', '作为基本信息设置错误！');
        }

        // 是否前台投稿中显示设置错误？
        if (!in_array($data['isadd'], [0, 1])) {
            return createReturn(false, '', '前台投稿中显示设置错误！');
        }

        $OperationField = new OperationField();
        $createFieldRes = $OperationField->editField($modelid,$info,$data,$oldData);
        return $createFieldRes;
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
        if (
        $this->where(
            [
                ['modelid', '=', $this->modelid],
                ['field', '=', $fieldName]
            ])->count()
        ) {
            return false;
        }
        return true;
    }

    /**
     * 是否禁用字段
     * @param int $fieldid
     * @param int $disabled
     * @return array
     */
    public function doDisable($fieldid = 0, $disabled = 0){
        $OperationField = new OperationField();
        //载入字段配置文件
        include $OperationField->fieldPath. 'fields.inc.php';

        $field = $this->where('fieldid', $fieldid)->findOrEmpty();
        if ($field->isEmpty()) {
            return createReturn(false,'','该字段不存在');
        }
        //检查是否允许被删除
        if (in_array($field['field'], $OperationField->forbid_fields)) {
            return createReturn(false,'','该字段不允许被禁用');
        }
        $status = $this->where('fieldid', $fieldid)->update(array('disabled' => $disabled));
        if ($status) {
            return createReturn(true,'','操作成功');
        } else {
            return createReturn(false,'','操作失败');
        }
    }

    /**
     * 删除指定字段
     * @param $fieldid
     * @return array
     */
    public function doDelete($fieldid){
        //原字段信息
        $info = $this->where("fieldid", $fieldid)->findOrEmpty();
        if ($info->isEmpty()) {
            return createReturn(false,'','该字段不存在');
        }

        $Operation = new Operation();
        $OperationField = new OperationField();

        //模型id
        $modelid = $info['modelid'];
        //完整表名获取 判断主表 还是副表
        $tablename = $OperationField->getModelTableName($modelid, $info['issystem']);
        if (!$Operation->table_exists($tablename)) {
            return createReturn(false,'','数据表不存在');
        }

        //判断是否允许删除
        if (false !== in_array($info['field'], $OperationField->forbid_delete)) {
            return createReturn(false,'','该字段不允许被删除！');
        }

        if ($OperationField->deleteFieldSql($info['field'], $OperationField->dbConfig['prefix'].$tablename)) {

            $this->where('fieldid', $fieldid)
                ->where('modelid', $modelid)
                ->delete();
            return createReturn(true,'','删除成功！');
        } else {
            return createReturn(false,'','数据库表字段删除失败！');
        }
    }

    /**
     * 获取可用字段类型列表
     * @return array
     */
    public function getFieldTypeList()
    {
        $OperationField = new OperationField();
        $fields = include $OperationField->fieldPath . 'fields.inc.php';
        $fields = $fields ?: array();
        return $fields;
    }

    /**
     * 添加成功后执行回调 TODO：暂未处理回调结果
     * @param array $params
     */
    public function contentModelEditFieldBehavior($params = []){
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
    function getDefaultSettingData($setting = [])
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