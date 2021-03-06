<?php

namespace app\cms\controller;

use app\cms\model\ContentCategoryModel;
use app\cms\model\ContentModelFieldModel;
use app\cms\model\ContentModelModel;
use app\cms\model\model\ModelField;
use app\cms\service\ContentCategoryService;
use app\cms\service\ContentModelFieldService;
use app\cms\service\ContentModelService;
use app\cms\service\ContentService;
use app\common\controller\AdminController;
use think\facade\View;
use app\cms\model\category\Category;

/**
 * 内容管理
 */
class Content extends AdminController
{

    public $noNeedPermission = ['content_list_operate'];
    /**
     * 内容管理列表
     * @return array|string
     */
    function index(){

        $action = input('action','','trim');
        if($action == 'category_list') {
            //获取分类列表
            $Category = new Category();
            $res['list'] = $Category::getCategoryToArray();
            return self::createReturn(true,$res);
        }
        return View::fetch('index');
    }

    /**
     * 栏目列表页
     * @return array|string
     */
    function list(){
        $action = input('action','','trim');
        $catid = input('catid','','trim');
        if($action == 'getDisplaySettin'){
            //获取显示设置
            return ContentService::getDisplaySettin($catid);
        } else if($action == 'getTemplateList') {
            //获取列表信息
            $where = [];
            $keywords = input('keywords');
            if(isset($keywords) && !empty($keywords)) {
                foreach ($keywords as $k => $v) {
                    $where[] = [$k,'like','%'.$v.'%'];
                }
            }
            $res = ContentService::getTemplateList($catid,$where);
            return $res;
        } else if($action == 'delTemplate'){
            //删除模板内容
            $id = input('id','','trim');
            $res = ContentService::delTemplate($catid,$id);
            return $res;
        }

        return View::fetch('list',[
            'catid' => $catid
        ]);
    }

    /**
     * 栏目内容列表页
     */
    function content_list(){
        $catid = input('catid','','trim');
        $action = input('_action','','trim');

        $contentCategory = ContentCategoryService::getContentCategory($catid)['data'];
        $contentModel = ContentModelService::getModel($contentCategory['modelid'])['data'];

        // 获取列表页参数
        if($this->request->isGet() && $action == 'getListParam'){
            $fieldModel = new ContentModelFieldModel();
            $field_list = $fieldModel->where([
                ['modelid', '=', $contentCategory['modelid']],
                ['enable_list_show', '=', 1]
            ])->field('field,name,form_type')->select()->toArray();
            return json(self::createReturn(true, [
                'field_list' => $field_list
            ]));
        }

        //获取内容列表
        if($action === 'getContentList'){
            $page = input('page', 1, 'intval');
            $limit = input('limit', 15, 'intval');
            $_where = input('where', []);;
            $where = [];
            foreach ($_where as $item) {
                $value = $item['value'];
                if (strtolower($item['operator']) == 'like') {
                    $value = '%'.$value.'%';
                }
                $where [] = [$item['field'], $item['operator'], $value];
            }
            $res = ContentService::getContentList($catid, $where, $page, $limit);
            return json($res);
        }

        // 选择模板
        $list_customtemplate = $contentCategory['list_customtemplate'];
        if(empty($list_customtemplate)){
            $list_customtemplate = $contentModel['list_customtemplate'];
            if(empty($list_customtemplate)){
                $list_customtemplate = 'content_list';
            }
        }

        $list_customtemplate = 'content/list/'.$list_customtemplate;
        return view($list_customtemplate);

    }

    // 内容列表页操作
    function content_list_operate(){
        $action = input('_action','','trim');
        $catid = input('catid','','trim');
        if ($action === 'deleteContent') {
            // 删除内容
            $id = input('id', '', 'trim');
            $res = ContentService::deleteContent($catid, $id);
            return json($res);
        }
    }

    /**
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function content_add(){
        return $this->content_edit();
    }

    /**
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function content_edit(){
        $catid = input('catid','','trim');
        $action = input('_action','','trim');
        // 表单设置
        if($this->request->isGet() && $action == 'getFormSetting'){
            $model = ContentModelService::getModelByCatid($catid)['data'];
            $field_list = ContentModelFieldService::getEditableModelFieldList($model['modelid'])['data'];

            $category_list = $this->_getCategoryList();
            return json(self::createReturn(true, [
                'field_list' => $field_list,
                'category_list' => $category_list
            ]));
        }

        // 获取内容信息
        if($this->request->isGet() && $action == 'getDetail'){
            $id = input('id','','trim');
            $res = ContentService::getDetail($catid, $id);
            return json($res);
        }

        // 添加、编辑
        if($this->request->isPost() && $action == 'submitForm'){
            $content = input('content');
            $res = ContentService::addOrEditContent($content);
            return json($res);
        }


        // 展示页面
        $contentCategory = ContentCategoryService::getContentCategory($catid)['data'];
        $list_customtemplate = $contentCategory['edit_customtemplate'];
        if(empty($list_customtemplate)){
            $contentModel = ContentModelService::getModel($contentCategory['modelid'])['data'];
            $list_customtemplate = $contentModel['edit_customtemplate'];
            if(empty($list_customtemplate)){
                $list_customtemplate = 'content_edit';
            }
        }

        $list_customtemplate = 'content/edit/'.$list_customtemplate;
        return view($list_customtemplate);
    }

    // 栏目列表
    private function _getCategoryList()
    {
        $list = ContentCategoryService::getCategoryTree()['data'];
        // 获取全部模型
        $contentModelModel = new ContentModelModel();
        $model_list = $contentModelModel->select()->toArray();
        $models = [];
        foreach ($model_list as $i => $item) {
            $models[$item['modelid']] = $item;
        }

        foreach ($list as $k => &$v) {
            $v['type_name'] = ContentCategoryModel::getCategoryTypeName($v['type']);
            $v['model_name'] = (!empty($models[$v['modelid']])) ? $models[$v['modelid']]['name'] : '';

        }
        return $list;
    }



    /**
     * 获取详情信息
     */
    function details(){
        $action = input('_action','','trim');
        $catid = input('catid','','trim');
        $id = input('id','','trim');

        if($action == 'getCategoryList'){
            //获取分类列表 TODO 精细化返回
            $Category = new Category();
            $list = $Category::getCategoryTree();
            return self::createReturn(true, $list);
        }
        if($action == 'getDisplaySetting'){
            //获取显示设置
            return ContentService::getDetailsDisplaySettin($catid,$id);
        } else if($action == 'submitForm') {
            //提交内容
            $post = input('post.');
            return ContentService::submitForm($post);
        }

        $ModelField = new ModelField();
        $where[] = ['formtype','=','editor'];
        $editor = $ModelField->where($where)->group('field')->column('field') ?: [];
        $editor = implode(',',$editor);
        return View::fetch('content/details/admin_default/admin_default',[
            'catid' => $catid,
            'id' => $id,
            'editor' => $editor
        ]);
    }


}
