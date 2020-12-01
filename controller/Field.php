<?php
/**
 * Created by FHYI.
 * Date 2020/10/30
 * Time 9:46
 */

namespace app\cms\controller;

use app\cms\fields\content_form;
use app\cms\model\ModelFieldModel;
use app\cms\model\ModelModel;
use app\common\controller\AdminController;
use app\common\model\ConfigModel;
use think\App;
use think\facade\Db;
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
     * 显示字段列表
     * @return string|\think\response\Json
     */
    public function index()
    {
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
     * 获取字段
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFieldData()
    {
        $modelId = $this->request->get('modelid', 0, 'intval');
        if (empty($modelId)) {
            return self::makeJsonReturn(false, '', '参数错误！');
        }
        $model = ModelModel::where("modelid", $modelId)->findOrEmpty();
        if ($model->isEmpty()) {
            return self::makeJsonReturn(false, '', '该模型不存在！');
        }
        //根据模型读取字段列表
        $data = $this->modelfield->getModelField($modelId);
        return self::makeJsonReturn(true, $data, '');
    }

    /**
     * 字段的启用与禁用【批量】
     * @return \think\response\Json
     */
    public function disabled()
    {
        $fieldIds = $this->request->post('fieldid', [], 'intval');
        $disabled = $this->request->post('disabled');
        $disabled = (int)$disabled ? 0 : 1;
        Db::startTrans();
        $count = 0;
        foreach ($fieldIds as $fieldId) {
            $result = $this->doDisable($fieldId, $disabled);
            if ($result['status']) {
                $count++;
            }
        }
        if ($count > 0) {
            Db::commit();
            return self::makeJsonReturn(true, '', $result['msg']);
        } else {
            Db::rollback();
            return self::makeJsonReturn(false, '', $result['msg']);
        }
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
            foreach ($this->modelfield->getFieldTypeList() as $formtype => $name) {
                if (!$this->modelfield->isEditField($formtype)) {
                    continue;
                }
                $all_field[$formtype] = $name;
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
            //允许使用的字段类型
            View::assign("all_field", $all_field);
            //当前字段是否允许编辑
            View::assign('isEditField', $this->modelfield->isEditField($fieldData['field']));
            View::assign("modelid", $modelId);
            View::assign("fieldid", $fieldId);
            View::assign("setting", $setting);
            //字段信息分配到模板
            View::assign("data", $fieldData);
            View::assign("modelinfo", $modeData);
            return View::fetch();
        }
    }


    /**
     * 添加字段
     * @return string|\think\response\Json
     */
    public function add()
    {
        //模型ID
        $modelId = input('modelid',0,'intval');
        if (empty($modelId)) {
            return self::makeJsonReturn(false, '', '模型ID不能为空');
        }

        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                return self::makeJsonReturn(false, '', '数据不能为空!');
            }
            if ($this->modelfield->addField($post)) {

                //成功后执行回调
                $field = $post['field'];
                $params = array("modelid" => $modelId, 'field' => $field);
                $this->modelfield->contentModelEditFieldBehavior($params);

                return self::makeJsonReturn(true, '', '添加成功',200,api_url("/cms/field/index", array("modelid" => $modelId)));
            } else {
                $error = $this->modelfield->error;
                return self::makeJsonReturn(false, '', $error ? $error : '添加失败！');
            }
        } else {

            //字段类型过滤
            $all_field = [];
            foreach ($this->modelfield->getFieldTypeList() as $formtype => $name) {
                if (!$this->modelfield->isAddField($formtype, $formtype, $modelId)) {
                    continue;
                }
                $all_field[$formtype] = $name;
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
            //可使用字段类型
            View::assign("all_field", $all_field);
            //模型数据
            View::assign("modelinfo", ModelModel::where("modelid", $modelId)->findOrEmpty());
            View::assign("modelid", $modelId);

            return View::fetch();
        }
    }

    /**
     * 删除字段 支持批量
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delFields()
    {
        //字段ID
        $fieldIds = input('fieldid',[],'intval');
        Db::startTrans();
        foreach ($fieldIds as $index => $fieldid) {
            $res = $this->doDelete($fieldid);
            if (!$res['status']) {
                Db::rollback();
                return self::makeJsonReturn(false, '', $res['msg']);
            }
        }
        Db::commit();
        return self::makeJsonReturn(true, '', '操作成功');
    }

    /**
     * 删除字段操作
     * @param $fieldId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function doDelete($fieldId)
    {
        if (empty($fieldId)) {
            return [
                'status' => false,
                'msg'    => '字段ID不能为空'
            ];
        }
        if ($this->modelfield->deleteField($fieldId)) {
            return [
                'status' => true,
                'msg'    => '字段删除成功'
            ];

        } else {
            $error = $this->modelfield->error;
            return [
                'status' => false,
                'msg'    => $error ? $error : "删除字段失败！"
            ];
        }
    }

    /**
     * 更新字段排序
     * @return \think\response\Json
     */
    public function listOrder()
    {
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            $res = $this->modelfield->transaction(function () use ($postData) {
                foreach ($postData['data'] as $item) {
                    $this->modelfield->where('fieldid', $item['fieldid'])
                        ->save(['listorder' => $item['listorder']]);
                }
                return true;
            });
            if ($res) {
                cache('Model', NULL);
                cache('ModelField', NULL);
                return self::makeJsonReturn(true, $res, '排序更新成功!');
            } else {
                return self::makeJsonReturn(false, $res, '排序失败!');
            }
        } else {
            return self::makeJsonReturn(false, '', '排序失败!');
        }
    }

    //验证字段是否重复 AJAX
    public function public_checkfield()
    {
        //新字段名称
        $field = input('get.field');
        //原来字段名
        $oldfield = input('get.oldfield');
        if ($field == $oldfield) {
            return self::makeJsonReturn(true, $field, '字段没有重复!');
        }
        //模型ID
        $modelid = input('get.modelid');
        $status = $this->modelfield->where(array("field" => $field, "modelid" => $modelid))->count();
        if ($status == 0) {
            return self::makeJsonReturn(true, $field, '字段没有重复!');
        } else {
            return self::makeJsonReturn(false, $field, '字段有重复!');
        }
    }

    //字段属性配置
    public function public_field_setting()
    {
        //字段类型
        $fieldtype = input('fieldtype','','trim');
        $fiepath = $this->fields . $fieldtype . '/';
        //载入对应字段配置文件 config.inc.php
        include $fiepath . 'config.inc.php';
        $settings = array(
            'field_basic_table' => $field_basic_table,
            'field_minlength' => $field_minlength,
            'field_maxlength' => $field_maxlength,
            'field_allow_search' => $field_allow_search,
            'field_allow_fulltext' => $field_allow_fulltext,
            'field_allow_isunique' => $field_allow_isunique,
            'setting' => ''
        );
        return self::makeJsonReturn(true, $settings, '获取成功!');
    }

    /**
     * 隐藏/启用字段
     * @param int $fieldid
     * @param int $disabled 1 禁用 0启用
     * @return array
     */
    private function doDisable($fieldid = 0, $disabled = 0)
    {
        //载入字段配置文件
        include $this->fields . 'fields.inc.php';
        $field = $this->modelfield->where('fieldid', $fieldid)->findOrEmpty();
        if ($field->isEmpty()) {
            return [
                'status' => false,
                'msg'    => '该字段不存在'
            ];
        }
        //检查是否允许被删除
        if (in_array($field['field'], $this->modelfield->forbid_fields)) {
            return [
                'status' => false,
                'msg'    => '该字段不允许被禁用'
            ];
        }

        $status = $this->modelfield->where('fieldid', $fieldid)->save(array('disabled' => $disabled));
        if ($status) {
            return [
                'status' => true,
                'msg'    => '操作成功'
            ];
        } else {
            return [
                'status' => false,
                'msg'    => '操作失败'
            ];
        }
    }

    //模型预览 TP6
    public function priview()
    {
        //模型ID
        $modelId = $this->request->get('modelid', 0);
        if (empty($modelId)) {
            $this->error("请指定模型！");
        }
        define('IN_ADMIN',true);
        define('ACTION_NAME',$this->request->action());
        define('MODULE_NAME','cms');
        define('CONFIG_SITEURL_MODEL','/');

        cache('Model', NULL);
        cache('ModelField', NULL);
        $content_form = new content_form($modelId);
        //生成对应字段的输入表单
        $forminfos = $content_form->get();
        //生成对应的JS验证规则
        $formValidateRules = $content_form->formValidateRules;
        //js验证不通过提示语
        $formValidateMessages = $content_form->formValidateMessages;
        //js
        $formJavascript = $content_form->formJavascript;
        //获取当前模型信息
        $r = ModelModel::where("modelid", $modelId)->findOrEmpty();
        View::assign("r", $r);
        View::assign("forminfos", $forminfos);
        View::assign("formValidateRules", $formValidateRules);
        View::assign("formValidateMessages", $formValidateMessages);
        View::assign("formJavascript", $formJavascript);
        View::assign("Config", ConfigModel::getConfigs());
        return View::fetch();
    }

}
