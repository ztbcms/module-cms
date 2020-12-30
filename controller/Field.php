<?php

namespace app\cms\controller;

use app\cms\model\model\Model;
use app\cms\service\ContentModelFieldService;
use app\cms\service\ContentModelService;
use app\cms\service\FieldService;
use app\common\controller\AdminController;
use think\App;
use think\facade\View;
use think\Request;

/**
 * 模型字段管理
 * Class Field
 *
 * @package app\cms\controller
 */
class Field extends AdminController
{

    /**
     * 字段列表
     *
     * @return array|string
     */
    public function index()
    {
        $modelid = input('modelid', 0, 'intval');
        $action = input('action', '', 'trim');

        if ($action == 'getFieldData') {
            //获取字段
            return FieldService::getFieldData($modelid);
        } else {
            if ($action == 'disabledField') {
                //字段的启用和禁用
                $fieldIds = input('fieldid', [], 'intval');
                $disabled = input('disabled');
                $disabled = (int) $disabled ? 0 : 1;
                return FieldService::disabledField($fieldIds, $disabled);
            } else {
                if ($action == 'delFields') {
                    //删除字段
                    $fieldIds = input('fieldid', [], 'intval');
                    return FieldService::delFields($fieldIds);
                } else {
                    if ($action == 'listOrderFields') {
                        //更新字段排序
                        $postData = input('post.');
                        return FieldService::listOrderFields($postData);
                    }
                }
            }
        }

        $modelId = input('modelid', 0, 'intval');
        $Model = new Model();
        $model = $Model->where("modelid", $modelId)->findOrEmpty();
        View::assign("modelinfo", $model);
        return View::fetch();
    }

    /**
     * 添加字段
     *
     * @return string|\think\response\Json
     */
    function addField(Request $request)
    {
        $action = input('_action', '', 'trim');
        //模型ID
        $modelId = input('modelid', 0, 'intval');

        // 提交更新
        if ($request->isPost()) {
            $post = input('post.');
            $post['modelid'] = $modelId;
            $res = ContentModelFieldService::addModelField($post, true);
            return json($res);
        }

        if ($request->isGet() && $action == 'getFormParam') {
            //获取可编辑的字段
            $getAllFieldRes = ContentModelFieldService::getAvailableFormTypeList()['data'];
            $res = ContentModelService::getModel($modelId);
            $data = [
                'form_type'  => $getAllFieldRes,
                'model_info' => $res['data']
            ];

            return self::makeJsonReturn(true, $data);
        }
        return view('addOrEditField');
    }

    /**
     * 编辑字段信息
     *
     * @return string|\think\response\Json
     */
    function editField(Request $request)
    {
        $action = input('_action', '', 'trim');
        //模型ID
        $modelId = input('modelid', 0, 'intval');
        //字段ID
        $fieldId = input('fieldid', 0, 'intval');
        // 提交更新
        if ($request->isPost()) {
            $post = input('post.');
            $res = ContentModelFieldService::updateModelField($post, true);
            return json($res);
        }

        if ($request->isGet() && $action == 'getDetail') {
            //获取字段信息
            $FieldDetails = FieldService::getFieldDetail($modelId, $fieldId)['data'];

            return self::makeJsonReturn(true, $FieldDetails);
        }

        if ($request->isGet() && $action == 'getFormParam') {
            //获取可编辑的字段
            $getAllFieldRes = ContentModelFieldService::getAvailableFormTypeList()['data'];
            $res = ContentModelService::getModel($modelId);
            $data = [
                'form_type'  => $getAllFieldRes,
                'model_info' => $res['data']
            ];

            return self::makeJsonReturn(true, $data);
        }
        return view('addOrEditField');
    }

}
