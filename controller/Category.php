<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 9:52
 */

namespace app\cms\controller;

use app\cms\service\CategoryService;
use app\common\controller\AdminController;
use think\App;
use think\facade\Request;
use think\facade\View;
use app\cms\model\model\Model;
use app\cms\model\category\Category as CategoryModel;
use think\response\Json;


/**
 * 栏目管理
 * Class Category
 * @package app\cms\controller
 */
class Category extends AdminController
{
    //栏目列表
    public function index()
    {
        $action = input('action', '', 'trim');
        if($action == 'doDelete') {
            //删除栏目列表
            $catid = input("catid", "", "intval");
            return CategoryService::deleteCatid($catid);
        } else if($action == 'getCategoyList') {
            //获取列表数据
            $res =  CategoryService::getCategoyList();
            return json($res);
        } else if($action == 'listOrderCategoy'){
            //编辑列表排序
            $post = input('post.');
            return CategoryService::listOrderCategoy($post);
        }
        return View::fetch();
    }


    //更新栏目缓存并修复
    public function public_cache()
    {
        $CategoryModel = new CategoryModel;
        return $CategoryModel->clearCache();
    }

    /**
     * 栏目详情
     *
     * @return string|Json
     */
    function details()
    {
        $action = input('_action', '', 'trim');
        $catid = input('catid', '0', 'trim');
        if (Request::isGet() && $action == 'getFormParam') {
            $categoryList = CategoryService::getCategoyList()['data'];
            $modelList = (new Model())->getAvailableList();
            return self::makeJsonReturn(true, [
                'categoryList' => $categoryList,
                'modelList' => $modelList
            ]);
        }
        if ($action == 'add_submit') {
            $post = input('post.');
            return CategoryService::addSubmit($post);
        } else if ($action == 'details') {
            //获取栏目详情
            $catid = input('catid',0,'intval');
            $data = getCategory($catid);
            return self::makeJsonReturn(true, $data, '获取成功');
        } else if ($action == 'edit_submit') {
            $post = input('post.');
            return CategoryService::editSubmit($post);
        }
        //输出可用模型
        View::assign("models", (new Model())->getAvailableList());
        //栏目列表 可以用缓存的方式
        View::assign("category", (new CategoryModel())->getAvailableList());
        //详情id
        View::assign("catid", $catid);
        return View::fetch();
    }

}
