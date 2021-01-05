<?php

namespace app\cms\service;

use app\cms\model\category\Category;
use app\cms\model\category\CategoryPriv;
use app\cms\model\model\Model;
use app\common\service\BaseService;
use app\admin\service\AdminUserService;

/**
 * 栏目管理
 * @deprecated
 * Class FieldExportService
 * @package app\cms\service
 */
class CategoryService extends BaseService
{


    /**
     * 获取栏目列表
     * @return array
     */
    static function getCategoyList(){
        $Category = new Category();
        $list =  $Category::getCategoryTree();

        // 获取全部模型
        $Model = new Model();
        $models = $Model::model_cache();


        foreach ($list as $k => &$v) {
            $v['type_name'] = Category::getCategoryTypeName($v['type']);
            $v['model_name'] = (!empty($models[$v['modelid']])) ? $models[$v['modelid']]['name'] : '';

            if(empty($v['url'])){
                $v['url'] = api_url("/cms/Index/list").'?catid='.$v['catid'];
            }
        }
        return self::createReturn(true, $list, '获取成功');
    }

    /**
     * 添加栏目
     * @param array $post
     * @return array
     */
    static function addSubmit($post = []){

        //添加新栏目
        $AdminUserDetails = AdminUserService::getInstance()->getInfo();
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
            $post['priv_roleid'] = $priv_roleid;
        }

        $Category = new Category();
        //是否批量添加
        $isbatch = input('isbatch', '0', 'intval');
        if ($isbatch) {
            //todo 未处理批量添加栏目
        } else {
            $addCategoryRes = $Category->addCategory($post);
            if (!$addCategoryRes['status']) return $addCategoryRes;

            $catid = $addCategoryRes['data']['catid'];
            if(isset($post['priv_roleid'])){

                $CategoryPriv = new CategoryPriv();
                //更新权限
                $CategoryPriv->update_priv($catid, $_POST['priv_roleid'], 1);
            }
        }
        return self::createReturn(true, [], '操作成功！');
    }

    /**
     * 编辑栏目
     * @param array $post
     * @return array
     */
    static function editSubmit($post = []){
        $Category = new Category();
        $editCategoryRes = $Category->editCategory($post);
        if (!$editCategoryRes['status']) return $editCategoryRes;

        $catid = $editCategoryRes['data']['catid'];

        //更新权限
        if(isset($post['priv_roleid'])) {

            $CategoryPriv = new CategoryPriv();
            $CategoryPriv->update_priv($catid, $post['priv_roleid'], 1);
        }

        return self::createReturn(true, [], '操作成功！');
    }

    /**
     * 删除栏目
     * @param $catid
     * @return array
     */
    static function deleteCatid($catid){
        if(empty($catid))   return self::createReturn(false, [], '栏目ID不能为空!');
        //取得子栏目
        $Category = new Category();
        return $Category->deleteCatid($catid);
    }

    /**
     * 更新排序
     * @param array $post
     * @return array
     */
    static function listOrderCategoy($post = []){
        $model = new Category();
        $model->transaction(function () use ($post) {
            foreach ($post['data'] as $item) {
                Category::where('catid', $item['catid'])
                    ->save(['listorder' => $item['listorder']]);
                //删除缓存
                getCategory($item['catid'], '', true);
            }
            return true;
        });
        $model->clearCache();
        return self::createReturn(true, '', '更新排序成功');
    }
}