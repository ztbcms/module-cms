<?php

namespace app\cms\controller;

use app\cms\model\model\ModelField;
use app\cms\service\ContentCategoryService;
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
        $contentCategory = ContentCategoryService::getContentCategory($catid)['data'];
        $list_customtemplate = $contentCategory['list_customtemplate'];
        if(empty($list_customtemplate)){
            $contentModel = ContentModelService::getModel($contentCategory['modelid'])['data'];
            $list_customtemplate = $contentModel['list_customtemplate'];
            if(empty($list_customtemplate)){
                $list_customtemplate = 'content_list';
            }
        }

        $list_customtemplate = 'content/list/'.$list_customtemplate;
        return view($list_customtemplate);

    }

    function content_list_operate(){
        $action = input('_action','','trim');
        $catid = input('catid','','trim');
        if($action === 'getContentList'){
            //获取列表信息
            $page = input('page', 1, 'intval');
            $limit = input('limit', 15, 'intval');
            $where = [];
            $keywords = input('keywords');
            if (isset($keywords) && !empty($keywords)) {
                foreach ($keywords as $k => $v) {
                    $where[] = [$k, 'like', '%'.$v.'%'];
                }
            }
            $res = ContentService::getContentList($catid, $where, $page, $limit);
            return json($res);
        }
        if($action === 'addContent'){
            // 获取列表
        }
        if($action === 'editContent'){
            // 获取列表
        }
        if($action === 'deleteContent'){
            // 获取列表
        }

    }

    function content_add(){}



    function content_edit(){}



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
