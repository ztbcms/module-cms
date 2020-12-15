<?php

namespace app\cms\controller;

use app\cms\model\model\Model;
use app\cms\service\FieldService;
use app\common\controller\AdminController;
use think\App;
use think\facade\View;
use think\Request;

/**
 * 模型字段管理
 * Class Field
 * @package app\cms\controller
 */
class Field extends AdminController
{

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

        $modelId = input('modelid', 0, 'intval');
        $Model = new Model();
        $model = $Model->where("modelid", $modelId)->findOrEmpty();
        View::assign("modelinfo", $model);
        return View::fetch();
    }

    /**
     * 添加字段
     * @return string|\think\response\Json
     */
    function add()
    {
        //模型ID
        $modelId = input('modelid',0,'intval');
        if ($this->request->isPost()) {
            $post = input('post.');
            return FieldService::addField($post,$modelId);
        } else {
            //可使用字段类型
            View::assign("all_field", FieldService::getAllField($modelId)['all_field']);
            //模型数据
            View::assign("modelinfo",(new Model())->where("modelid", $modelId)->findOrEmpty());
            return View::fetch();
        }
    }

    /**
     * 字段属性配置
     * @return array
     */
    function publicFieldSetting()
    {
        //字段类型
        $fieldtype = input('fieldtype','','trim');
        return FieldService::getPublicFieldSetting($fieldtype);
    }

    /**
     * 获取字段详情
     * @return array
     */
    function getFieldDetails(){
        //模型ID
        $modelId = input('modelid',0,'intval');
        //字段ID
        $fieldId = input('fieldid',0,'intval');

        return FieldService::getFieldDetails($modelId,$fieldId);
    }

    /**
     * 编辑字段信息
     * @return string|\think\response\Json
     */
    function edit1()
    {
        //模型ID
        $modelId = input('modelid',0,'intval');
        //字段ID
        $fieldId = input('fieldid',0,'intval');
        // 提交更新
        if ($this->request->isPost()) {
            $post = input('post.');
            return FieldService::editField($post, $modelId,$fieldId);
        } else {
            //获取字段信息
            $FieldDetails = FieldService::getFieldDetails($modelId,$fieldId)['data'];
            //获取可编辑的字段
            $getAllFieldRes = FieldService::getAllField($modelId,$FieldDetails['data']['formtype']);

            //允许使用的字段类型
            View::assign("all_field", $getAllFieldRes['all_field']);
            View::assign("is_disabled_formtype", $getAllFieldRes['is_disabled_formtype']);
            //模型信息
            View::assign("modelinfo", $FieldDetails['modeData']);
            //字段信息分配到模板
            View::assign("data", $FieldDetails['data']);
            //模型id
            View::assign("modelid", $modelId);
            //字段id
            View::assign("fieldid", $fieldId);
            //字段设置
            View::assign("setting", $FieldDetails['setting']);
            //当前字段是否允许编辑
            View::assign('isEditField', $FieldDetails['isEditField']);
            return View::fetch();
        }
    }

    /**
     * 编辑字段信息
     * @return string|\think\response\Json
     */
    function edit(Request $request)
    {
        $action = input('_action', '', 'trim');
        //模型ID
        $modelId = input('modelid', 0, 'intval');
        //字段ID
        $fieldId = input('fieldid', 0, 'intval');
        // 提交更新
        if ($request->isPost()) {
            $post = input('post.');
            return FieldService::editField($post, $modelId, $fieldId);
        }

        if ($request->isGet() && $action == 'getDetail') {
            //获取字段信息
            $FieldDetails = FieldService::getFieldDetail($modelId, $fieldId)['data'];

            return self::makeJsonReturn(true, $FieldDetails);
        }

        if ($request->isGet() && $action == 'getFormParam') {
            //获取可编辑的字段
            $getAllFieldRes = FieldService::getAvailableFiled()['data'];

            $data = [
                'field_type' => $getAllFieldRes
            ];

            return self::makeJsonReturn(true, $data);
        }


        return view('edit');
    }

}
