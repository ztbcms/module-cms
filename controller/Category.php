<?php

namespace app\cms\controller;

use app\cms\model\ContentCategoryModel;
use app\cms\model\ContentModelModel;
use app\cms\service\CategoryService;
use app\cms\service\ContentCategoryService;
use app\common\controller\AdminController;
use think\App;
use think\facade\View;
use app\cms\model\model\Model;
use think\response\Json;


/**
 * 栏目管理
 * Class Category
 *
 * @package app\cms\controller
 */
class Category extends AdminController
{
    //栏目列表
    public function index()
    {
        $action = input('action', '', 'trim');
        if ($action == 'doDelete') {
            //删除栏目列表
            $catid = input("catid", "", "intval");
            return json(ContentCategoryService::deleteContentCategory($catid));
        }
        if ($action == 'getCategoyList') {
            //获取列表数据
            return $this->_getCategoryList();
        }
        return View::fetch();
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
        return self::makeJsonReturn(true, $list);
    }

    private function _getFormParam()
    {
        $categoryList = ContentCategoryService::getCategoryTree()['data'];
        $modelList = (new Model())->getAvailableList();
        return self::makeJsonReturn(true, [
            'categoryList' => $categoryList,
            'modelList'    => $modelList
        ]);
    }

    /**
     * 添加分类
     *
     * @return Json|\think\response\View
     */
    function addCategory()
    {
        $action = input('_action', '', 'trim');
        if ($this->request->isGet() && $action == 'getFormParam') {
            return $this->_getFormParam();
        }
        if ($this->request->isPost()) {
            // 添加
            $post = input('post.');
            $res = ContentCategoryService::addContentCategory($post);
            return json($res);
        }

        return view('addOrEditCategory');
    }

    /**
     * 编辑分类
     *
     * @return Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function editCategory()
    {
        $action = input('_action', '', 'trim');
        if ($this->request->isGet() && $action == 'getFormParam') {
            return $this->_getFormParam();
        }
        if ($this->request->isGet() && $action == 'getDetail') {
            //获取栏目详情
            $catid = input('catid', 0, 'intval');
            $res = ContentCategoryService::getContentCategory($catid);
            return json($res);
        }
        if ($this->request->isPost()) {
            // 编辑
            $post = input('post.');
            $res = ContentCategoryService::updateContentCategory($post);
            return json($res);
        }

        return view('addOrEditCategory');
    }

}
