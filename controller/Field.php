<?php
/**
 * Created by FHYI.
 * Date 2020/10/30
 * Time 9:46
 */

namespace app\cms\controller;

use app\cms\model\model\Model;

use app\cms\model\ModelFieldModel;
use app\cms\model\ModelModel;
use app\cms\service\FieldService;
use app\common\controller\AdminController;
use think\App;
use think\facade\View;

/**
 * 模型字段管理
 * Class Field
 * @package app\cms\controller
 */
class Field extends AdminController
{

    private $modelfield, $fields;

    //初始化
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->modelfield = new ModelFieldModel();
        //字段类型存放目录
        $this->fields = app_path() . 'fields/';
    }


    /**
     * 字段列表
     * @return array|string
     */
    public function index()
    {
        $modelid = input('modelid',0,'intval');
        $action = input('action', '', 'trim');

        if($action == 'getFieldData') {
            //获取字段
            return FieldService::getFieldData($modelid);
        } else if($action == 'disabledField') {
            //字段的启用和禁用
            $fieldIds = input('fieldid', [], 'intval');
            $disabled = input('disabled');
            $disabled = (int)$disabled ? 0 : 1;
            return FieldService::disabledField($fieldIds,$disabled);
        } else if($action == 'delFields') {
            //删除字段
            $fieldIds = input('fieldid',[],'intval');
            return FieldService::delFields($fieldIds);
        } else if($action == 'listOrderFields') {
            //更新字段排序
            $postData = input('post.');
            return FieldService::listOrderFields($postData);
        }



        //不允许删除的字段，这些字段讲不会在字段添加处显示
        View::assign("not_allow_fields", $this->modelfield->not_allow_fields);
        //允许添加但必须唯一的字段
        View::assign("unique_fields", $this->modelfield->unique_fields);
        //禁止被禁用的字段列表
        View::assign("forbid_fields", $this->modelfield->forbid_fields);
        //禁止被删除的字段列表
        View::assign("forbid_delete", $this->modelfield->forbid_delete);
        //可以追加 JS和CSS 的字段
        View::assign("att_css_js", $this->modelfield->att_css_js);

        $modelId = $this->request->get('modelid', 0, 'intval');
        $model = ModelModel::where("modelid", $modelId)->findOrEmpty();
        View::assign("modelinfo", $model);
        return View::fetch();
    }

    /**
     * 添加字段
     * @return string|\think\response\Json
     */
    public function add()
    {
        //模型ID
        $modelId = input('modelid',0,'intval');
        if ($this->request->isPost()) {
            $post = input('post.');
            return FieldService::addField($post,$modelId);
        } else {
            //可使用字段类型
            View::assign("all_field", FieldService::getAllField($modelId));
            //模型数据
            View::assign("modelinfo",(new Model())->where("modelid", $modelId)->findOrEmpty());
            return View::fetch();
        }
    }

    /**
     * 字段属性配置
     * @return array
     */
    public function publicFieldSetting()
    {
        //字段类型
        $fieldtype = input('fieldtype','','trim');
        return FieldService::getPublicFieldSetting($fieldtype);
    }


    public function getFieldDetails(){
        //模型ID
        $modelId = input('modelid',0,'intval');
        //字段ID
        $fieldId = input('fieldid',0,'intval');

        //模型信息
        $modeData = ModelModel::where("modelid", $modelId)->findOrEmpty();

        //字段信息
        $fieldWhere[] = ["fieldid", "=", $fieldId];
        $fieldWhere[] = ["modelid", "=", $modelId];
        $fieldData = $this->modelfield->where($fieldWhere)->findOrEmpty();

        //字段路径
        $fiepath = $this->fields . "{$fieldData['formtype']}/";
        //======获取字段类型的表单编辑界面===========
        //字段扩展配置

        $setting = unserialize($fieldData['setting']);
        $setting = $this->modelfield->getDefaultSettingData($setting);

        //字段类型过滤
        $all_field = array();
        $no_all_field = [];
        foreach ($this->modelfield->getFieldTypeList() as $formtype => $name) {
            if (!$this->modelfield->isEditField($formtype)) {
                $no_all_field[] = $formtype;
            }
            $all_field[$formtype] = $name;
        }

        return self::createReturn(true,[
            'modeData' => $modeData,
            'setting' => $setting,
            'all_field' => $all_field,
            'data' => $fieldData
        ],'获取详情信息');
    }

    /**
     * 编辑字段信息
     * @return string|\think\response\Json
     */
    public function edit()
    {
        //模型ID
        $modelId = input('modelid',0,'intval');
        //字段ID
        $fieldId = input('fieldid',0,'intval');
        if (empty($modelId))  return self::makeJsonReturn(false, '', '模型ID不能为空');
        if (empty($fieldId)) return self::makeJsonReturn(false, '', '字段ID不能为空！');

        // 提交更新
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                return self::makeJsonReturn(false, '', '数据不能为空！');
            }
            if ($this->modelfield->editField($post, $fieldId)) {
                //成功后执行回调
                $field = $post['field'];
                $params = array("modelid" => $modelId, 'field' => $field);
                $this->modelfield->contentModelEditFieldBehavior($params);

                return self::makeJsonReturn(true, [
                    'modelid' => $modelId
                ], '更新成功！',api_url("/cms/field/index", array("modelid" => $modelId)));
            } else {
                $error = $this->modelfield->error;
                return self::makeJsonReturn(false, '', $error ? $error : '更新失败！');
            }
        } else {
            //模型信息
            $modeData = ModelModel::where("modelid", $modelId)->findOrEmpty();

            //字段信息
            $fieldWhere[] = ["fieldid", "=", $fieldId];
            $fieldWhere[] = ["modelid", "=", $modelId];
            $fieldData = $this->modelfield->where($fieldWhere)->findOrEmpty();

            //字段路径
            $fiepath = $this->fields . "{$fieldData['formtype']}/";
            //======获取字段类型的表单编辑界面===========
            //字段扩展配置

            $setting = unserialize($fieldData['setting']);
            $setting = $this->modelfield->getDefaultSettingData($setting);

            //字段类型过滤
            $all_field = array();
            $no_all_field = [];
            foreach ($this->modelfield->getFieldTypeList() as $formtype => $name) {
                if (!$this->modelfield->isEditField($formtype)) {
                    $no_all_field[] = $formtype;
                }
                $all_field[$formtype] = $name;
            }

            //允许使用的字段类型
            View::assign("all_field", $all_field);

            View::assign("modelid", $modelId);
            View::assign("fieldid", $fieldId);
            View::assign("setting", $setting);
            //字段信息分配到模板
            View::assign("data", $fieldData);
            View::assign("modelinfo", $modeData);

            //是否可以编辑数据类型
            if(in_array($fieldData['formtype'],$no_all_field)){
                View::assign("is_disabled_formtype", 1);
            } else {
                //不存在可编辑数组中
                View::assign("is_disabled_formtype", 0);
            }

            //当前字段是否允许编辑
            View::assign('isEditField', $this->modelfield->isEditField($fieldData['field']));
            return View::fetch();
        }
    }

}
