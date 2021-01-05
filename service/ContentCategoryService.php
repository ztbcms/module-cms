<?php
/**
 * Author: jayinton
 */

namespace app\cms\service;


use app\cms\model\ContentCategoryModel;
use app\common\libs\helper\TreeHelper;
use app\common\service\BaseService;
use think\facade\Db;

/**
 * 内容分类服务
 *
 * @package app\cms\service
 */
class ContentCategoryService extends BaseService
{
    static function addContentCategory($category_data)
    {
        $data = [
            'catname'             => $category_data['catname'] ?? '',
            'type'                => $category_data['type'] ?? '',
            'modelid'             => $category_data['modelid'] ?? '',
            'parentid'            => $category_data['parentid'] ?? '',
            'arrchildid'          => $category_data['arrchildid'] ?? '',
            'catdir'              => $category_data['catdir'] ?? '',
            'parentdir'           => $category_data['parentdir'] ?? '',
            'url'                 => $category_data['url'] ?? '',
            'setting'             => serialize($category_data['setting']) ?? serialize([]),
            'list_template'       => $category_data['list_template'] ?? '',
            'show_template'       => $category_data['show_template'] ?? '',
            'list_customtemplate' => $category_data['list_customtemplate'] ?? '',
            'add_customtemplate'  => $category_data['add_customtemplate'] ?? '',
            'edit_customtemplate' => $category_data['edit_customtemplate'] ?? '',
            'create_time'         => time()
        ];

        //数据验证
        $validate = new \app\cms\validate\ContentCategory();
        if (!$validate->check($data)) {
            return self::createReturn(false, '', $validate->getError());
        }

        $contentCategoryModel = new ContentCategoryModel();
        // 是否重复模型名称
        $checker = $contentCategoryModel->where([
            ['catdir', '=', $data['catdir']]
        ])->findOrEmpty();
        if (!$checker->isEmpty()) {
            return self::createReturn(false, null, '该模型名称已经存在!');
        }
        $res = $contentCategoryModel->insert($data);
        if ($res) {
            return self::createReturn(true, null, '操作成功');
        }

        return self::createReturn(false, null, '操作失败');
    }

    static function updateContentCategory($category_data)
    {
        $catid = $category_data['catid'];
        if (empty($catid)) {
            return self::createReturn(false, '', '缺少参数 catid');
        }
        $data = [
            'catid'               => $category_data['catid'],
            'catname'             => $category_data['catname'] ?? '',
            'type'                => $category_data['type'] ?? '',
            'modelid'             => $category_data['modelid'] ?? '',
            'parentid'            => $category_data['parentid'] ?? '',
            'arrchildid'          => $category_data['arrchildid'] ?? '',
            'catdir'              => $category_data['catdir'] ?? '',
            'parentdir'           => $category_data['parentdir'] ?? '',
            'url'                 => $category_data['url'] ?? '',
            'setting'             => serialize($category_data['setting']) ?? serialize([]),
            'list_template'       => $category_data['list_template'] ?? '',
            'show_template'       => $category_data['show_template'] ?? '',
            'list_customtemplate' => $category_data['list_customtemplate'] ?? '',
            'add_customtemplate'  => $category_data['add_customtemplate'] ?? '',
            'edit_customtemplate' => $category_data['edit_customtemplate'] ?? '',
            'create_time'         => time()
        ];

        //数据验证
        $validate = new \app\cms\validate\ContentCategory();
        if (!$validate->check($data)) {
            return self::createReturn(false, '', $validate->getError());
        }

        $contentCategoryModel = new ContentCategoryModel();
        // 是否重复模型名称
        $checker = $contentCategoryModel->where([
            ['catdir', '=', $data['catdir']],
            ['id', '<>', $category_data['catid']],
        ])->findOrEmpty();
        if (!$checker->isEmpty()) {
            return self::createReturn(false, null, '该模型名称已经存在!');
        }
        $res = $contentCategoryModel->insert($data);
        if ($res) {
            return self::createReturn(true, null, '操作成功');
        }

        return self::createReturn(false, null, '操作失败');
    }

    /**
     * 获取栏目分类
     *
     * @param $catid
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getContentCategory($catid)
    {
        $contentCategoryModel = new ContentCategoryModel();
        $res = $contentCategoryModel->where('catid', $catid)->find();
        if ($res->isEmpty()) {
            return self::createReturn(false, null, '找不到信息');
        }
        return self::createReturn(true, null, $res->toArray());
    }

    /**
     * 删除栏目
     *
     * @param $catid
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function deleteContentCategory($catid)
    {
        if (empty($catid)) {
            return self::createReturn(false, null, '栏目ID不能为空');
        }
        $contentCategoryModel = new ContentCategoryModel();
        // 检测是否有子栏目
        $list = $contentCategoryModel->field('catid,parentid')->select()->toArray();

        $son_list = TreeHelper::getSonNodeFromArray($list, [
            'idKey'     => 'catid',// 节点的ID字段名
            'parentKey' => 'parentid', // 父节点的ID字段名
        ]);
        if (!empty($son_list)) {
            return self::createReturn(false, null, '无法删除，原因：栏目下仍有子栏目');
        }

        // 检测该栏目下是否有内容
        $category = $contentCategoryModel->where('catid', $catid)->find();
        $model = ContentModelService::getModel($category['modelid'])['data'];
        $res = Db::table($model['table'])->where('catid', $catid)->find();
        if (!$res) {
            return self::createReturn(false, null, '无法删除，原因：栏目下仍有内容');
        }

        $contentCategoryModel->where('catid', $catid)->delete();
        return self::createReturn(true, null, '操作成功');
    }

    /**
     * 获取所有栏目
     *
     * @return array
     */
    static function getCategoryTree()
    {
        $contentCategoryModel = new ContentCategoryModel();
        $list = $contentCategoryModel->select();
        $result = [];
        if (!$list->isEmpty()) {
            $list = $list->toArray();
            $config['idKey'] = 'catid';
            $config['parentKey'] = 'parentid';
            $result = TreeHelper::arrayToTreeList($list, 0, $config);
        }

        return self::createReturn(true, $result);
    }
}