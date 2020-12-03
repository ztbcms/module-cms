<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 9:52
 */

namespace app\cms\controller;

use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use app\cms\model\CategoryModel;
use app\cms\model\CategoryPrivModel;
use app\cms\model\CmsCategory;
use app\cms\model\ModelModel;
use app\common\controller\AdminController;
use app\cms\model\CmsModel;
use think\App;
use think\facade\Config;
use think\facade\View;


/**
 * 栏目管理
 * Class CategoryModel
 * @package app\cms\controller
 */
class Category extends AdminController
{
    //模板文件夹
    private $filepath;
    //频道模板路径
    protected $tp_category;
    //列表页模板路径
    protected $tp_list;
    //内容页模板路径
    protected $tp_show;
    //单页模板路径
    protected $tp_page;
    //评论模板路径
    protected $tp_comment;
    // 系统配置
    protected $config;

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    //栏目列表
    public function index()
    {
        $action = input('action', '', 'trim');
        if($action == 'doDelete') {
            //删除栏目列表
            $catid = input("catid", "", "intval");
            if(empty($catid))   return self::makeJsonReturn(false, [], '栏目ID不能为空!');

            $CmsCategory = new CmsCategory();
            return $CmsCategory->deleteCatid($catid);
        }
        return View::fetch();
    }

    //更新栏目缓存并修复
    public function public_cache()
    {
        $CmsCategory = new CmsCategory();
        return $CmsCategory->clearCache();
    }

    /**
     * 栏目列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCategoyList()
    {
        $CmsCategory = new CmsCategory();
        $list = $CmsCategory::getCategoryTree();
        // 获取全部模型
        $models = ModelModel::model_cache();
        // 类型
        $type = [
            0 => '内部栏目',
            1 => '单网页',
            2 => '外部链接',
        ];

        foreach ($list as $k => &$v) {
            $v['type_name'] = $type[$v['type']];
            $v['model_name'] = (!empty($models[$v['modelid']])) ? $models[$v['modelid']]['name'] : '';

            if ($v['url']) {
                $v['url_text'] = '访问';
                $v['url_jump'] = 'open';
            } else {
                $v['url'] = api_url("/cms/category/public_cache");
                $v['url_text'] = '更新缓存';
                $v['url_jump'] = 'update';
            }
        }
        return self::makeJsonReturn(true, $list, '获取成功');
    }

    /**
     * 栏目详情
     * @return string|\think\response\Json
     */
    public function details()
    {
        $action = input('action', '', 'trim');
        $catid = input('catid', '0', 'trim');
        if ($action == 'add_submit') {
            //添加新栏目
            $AdminUserDetails = AdminUserService::getInstance()->getInfo();
            $_POST = input('post.');
            if (!AdminUserService::getInstance()->isAdministrator()) {
                //不是超管的情况
                $priv_roleid = [
                    'init,' . $AdminUserDetails['role_id'],
                    'add,' . $AdminUserDetails['role_id'],
                    'edit,' . $AdminUserDetails['role_id'],
                    'delete,' . $AdminUserDetails['role_id'],
                    'listorder,' . $AdminUserDetails['role_id'],
                    'push,' . $AdminUserDetails['role_id'],
                    'remove,' . $AdminUserDetails['role_id'],
                ];
                $_POST['priv_roleid'] = $priv_roleid;
            }
            $CmsCategory = new CmsCategory();
            //是否批量添加
            $isbatch = input('isbatch', '0', 'intval');
            if ($isbatch) {

                //todo 未处理批量添加栏目

            } else {
                $addCategoryRes = $CmsCategory->addCategory($_POST);
                if (!$addCategoryRes['status']) return $addCategoryRes;

                $catid = $addCategoryRes['data']['catid'];
                if(isset($_POST['priv_roleid'])){
                    $CategoryPrivModel = new CategoryPrivModel();
                    //更新权限
                    $CategoryPrivModel->update_priv($catid, $_POST['priv_roleid'], 1);
                }
            }

            return self::makeJsonReturn(true, [], '操作成功！');
        } else if ($action == 'details') {
            //获取栏目详情
            $catid = input('catid',0,'intval');
            $data = getCategory($catid);
            return self::makeJsonReturn(true, $data, '获取成功');
        } else if ($action == 'edit_submit') {
            //编辑栏目信息
            $catid = input("post.catid", "", "intval");
            if (empty($catid)) {
                return self::makeJsonReturn(false, [], '请选择需要修改的栏目！');
            }

            $_POST = input('post.');

            $CmsCategory = new CmsCategory();
            $editCategoryRes = $CmsCategory->editCategory($_POST);
            if (!$editCategoryRes['status']) return $editCategoryRes;

            $catid = $editCategoryRes['data']['catid'];

            //更新权限
            if(isset($_POST['priv_roleid'])) {
                $CategoryPrivModel = new CategoryPrivModel();
                $CategoryPrivModel->update_priv($catid, $_POST['priv_roleid'], 1);
            }

            return self::makeJsonReturn(true, [], '操作成功！');
        }

        //输出可用模型
        $CmsModel = new CmsModel();
        $models = $CmsModel->getAvailableList();
        View::assign("models", $models);

        //栏目列表 可以用缓存的方式
        $CmsCategory = new CmsCategory();
        $categorydata = $CmsCategory->getAvailableList();
        View::assign("category", $categorydata);

        //详情id
        View::assign("catid", $catid);

        return View::fetch();
    }

    /**
     * 更新排序
     * @return \think\response\Json
     */
    public function listOrder()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $model = new CmsCategory();

            $model->transaction(function () use ($data) {
                foreach ($data['data'] as $item) {
                    CategoryModel::where('catid', $item['catid'])
                        ->save(['listorder' => $item['listorder']]);
                    //删除缓存
                    getCategory($item['catid'], '', true);
                }
                return true;
            });

            $model->clearCache();
            return self::makeJsonReturn(true, '', '更新排序成功');
        }
    }

}
