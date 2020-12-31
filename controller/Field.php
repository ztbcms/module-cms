<?php

namespace app\cms\controller;

use app\cms\model\model\ModelField;
use app\cms\service\ContentModelFieldService;
use app\cms\service\ContentModelService;
use app\common\controller\AdminController;
use think\App;
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
        $action = input('_action', '', 'trim');

        if ($this->request->isPost() && $action == 'delFields') {
            //删除字段
            $fieldIds = input('fieldid', [], 'intval');
            if (empty($fieldIds)) {
                return self::makeJsonReturn(false, null, '请选择要删除的字段');
            }
            if (is_array($fieldIds)) {
                foreach ($fieldIds as $fieldId) {
                    ContentModelFieldService::deleteModelField($fieldId, true);
                }
                return self::makeJsonReturn(true, null, '操作完成');
            }
            if (is_numeric($fieldIds)) {
                $res = ContentModelFieldService::deleteModelField($fieldIds, true);
                return json($res);
            }
            return self::makeJsonReturn(false, null, '参数异常');
        }

        if ($this->request->isPost() && $action == 'listOrderFields') {
            //更新字段排序
            $postData = input('post.');
            $list = $postData['data'];
            $ModelField = new ModelField();
            $res = $ModelField->transaction(function () use ($list)
            {
                foreach ($list as $item) {
                    ModelField::where('fieldid', $item['fieldid'])->save(['listorder' => $item['listorder']]);
                }
                return true;
            });
            if ($res) {
                return self::makeJsonReturn(true, null, '更新完成');
            }
            return self::makeJsonReturn(false, null, '操作失败');
        }

        if ($this->request->isGet() && $action == 'getFieldData') {
            //获取字段
            $field_list = ContentModelFieldService::getModelFieldList($modelid)['data'];
            $model_info = ContentModelService::getModel($modelid)['data'];
            return self::makeJsonReturn(true, [
                'field_list' => $field_list,
                'model_info' => $model_info,
            ]);
        }

        return view('index');
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
            $FieldDetails = ContentModelFieldService::getFieldDetail($fieldId);

            return json($FieldDetails);
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
