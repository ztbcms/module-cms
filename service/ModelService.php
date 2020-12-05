<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2020/12/3
 * Time: 21:09
 */

namespace app\cms\service;

use app\cms\model\category\Category;
use app\cms\model\model\Model;
use app\common\service\BaseService;
use app\admin\service\AdminConfigService;
use think\facade\Config;

/**
 * 模型管理
 * Class ModelService
 * @package app\cms\service
 */
class ModelService extends BaseService
{

    /**
     * 获取所有模型
     * @return array
     */
    static function getModelsList(){
        $Model = new Model();
        $data = $Model->where("type", 0)->select();
        return self::createReturn(true, $data);
    }

    /**
     * 删除模型
     * @param $modelId
     * @return array
     */
    static function delModel($modelId){
        $Model = new Model();
        $Category = new Category();

        //检查该模型是否已经被使用
        $count = $Category->where([
            ['modelid','=',$modelId]
        ])->count();
        if ($count) {
            return self::createReturn(false, '', '该模型已经在使用中，请删除栏目后再进行删除！');
        }
        return $Model->deleteModel($modelId);
    }

    /**
     * 编辑模型的禁用状态
     * @param $modelid
     * @param $disabled
     * @return array
     */
    static function disabled($modelid,$disabled){
        $Model = new Model();
        $res = $Model->where([
            ['modelid','=',$modelid]
        ])->update(compact('disabled'));
        return self::createReturn(true, '', '操作成功！');
    }

    /**
     * 获取基础配置
     * @return mixed
     */
    static function getBasicsConfig(){
        $adminConfigService = new AdminConfigService();
        $config = $adminConfigService->getConfig()['data'];
        //获取文件路径
        $filepath = Config::get('cms.template_path') . (empty($config['theme']) ? "Default" : $config['theme']) . "/Content/";
        //取得栏目频道模板列表
        $tp_category = str_replace($filepath. "CategoryModel/", '', glob($filepath. 'CategoryModel/category*'));
        //取得栏目列表模板列表
        $tp_list = str_replace($filepath. "List/", '', glob($filepath .'List/list*'));
        //取得内容页模板列表
        $tp_show = str_replace($filepath. "Show/", '', glob($filepath .'Show/show*'));
        //取得单页模板
        $tp_page = str_replace($filepath. "Page/", '', glob($filepath. 'Page/page*'));
        //取得评论模板列表
        $tp_comment = str_replace($filepath. "Comment/", '', glob($filepath. 'Comment/comment*'));
        $res['tp_category'] = $tp_category;
        $res['tp_list'] = $tp_list;
        $res['tp_show'] = $tp_show;
        $res['tmpl_template_suffix'] = Config::get('template.tmpl_template_suffix');
        return $res;
    }

    /**
     * 添加模型
     * @param $data
     * @return array
     */
    static function addModel($data){
        if (empty($data) && !isset($data)) {
            return self::createReturn(false, '', '提交数据不能为空！');
        }
        $Model = new Model();
        return $Model->addModel($data);
    }

    /**
     * 编辑模板
     * @param $data
     * @return array
     */
    static function editModel($data){
        if (empty($data) && !isset($data)) {
            return self::createReturn(false, '', '提交数据不能为空！');
        }
        $Model = new Model();
        return $Model->editModel($data);
    }

    /**
     * 获取模型详情
     * @param $modelId
     * @return array
     */
    static function getModelDetail($modelId){
        $Model = new Model();
        $data = $Model->where("modelid", $modelId)->findOrEmpty();
        return self::createReturn(true, $data);
    }


    /**
     * 导入模型
     * @param $tablename
     * @param $name
     * @return array
     */
    static function importModel($tablename,$name){
        if (!isset($_FILES['file']) || empty($_FILES['file'])) {
            return self::createReturn(false, [], "请选择上传文件！");
        }
        $filename = $_FILES['file']['tmp_name'];
        if (strtolower(substr($_FILES['file']['name'], -3, 3)) != 'txt') {
            return self::createReturn(false, [], "上传的文件格式有误！");
        }
        //读取文件
        $data = file_get_contents($filename);
        //删除
        @unlink($filename);
        $Model = new Model();
        return $Model->importModel($data, $tablename, $name);
    }

    /**
     * 导出模型
     * @param $modelId
     * @return array|string
     */
    static function exportModel($modelId){
        if (!isset($modelId) || empty($modelId)) {
            return self::createReturn(false, '', '请指定需要导出的模型!');
        }
        $Model = new Model();
        $exportModelRSes = $Model->exportModel($modelId);
        if ($exportModelRSes['status']) {
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=ztb_model_" . $modelId . '.txt');
            echo $exportModelRSes['data']['data'];
            exit;
        } else {
            return $exportModelRSes;
        }
    }

}