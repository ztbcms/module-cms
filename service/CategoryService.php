<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2020/12/6
 * Time: 12:54
 */

namespace app\cms\service;

use app\cms\model\category\Category;
use app\cms\model\category\CategoryPriv;
use app\cms\model\model\Model;
use app\common\service\BaseService;
use app\admin\service\AdminUserService;

/**
 * 栏目管理
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

        // 类型
        $type = [
            0 => '内部栏目',
            1 => '单网页',
            2 => '外部链接',
        ];

        foreach ($list as $k => &$v) {
            $v['type_name'] = $type[$v['type']];
            $v['model_name'] = (!empty($models[$v['modelid']])) ? $models[$v['modelid']]['name'] : '';

            $v['url'] = api_url("/cms/Content/list").'&catid='.$v['catid'];
            $v['url_text'] = '访问';
            $v['url_jump'] = 'open';
//            if ($v['url']) {
//                $v['url'] = api_url("/cms/category/public_cache");
//                $v['url_text'] = '访问';
//                $v['url_jump'] = 'open';
//            } else {
//                $v['url'] = api_url("/cms/category/public_cache");
//                $v['url_text'] = '更新缓存';
//                $v['url_jump'] = 'update';
//            }
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