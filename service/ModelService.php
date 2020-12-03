<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2020/12/3
 * Time: 21:09
 */

namespace app\cms\service;

use app\cms\model\CmsCategory;
use app\cms\model\CmsModel;
use app\cms\model\ModelModel;
use app\common\service\BaseService;

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
        $CmsModel = new CmsModel();
        $data = $CmsModel->where("type", 0)->select();
        return self::createReturn(true, $data);
    }

    /**
     * 删除模型
     * @param $modelId
     * @return array
     */
    static function delModel($modelId){
        $CmsCategory = new CmsCategory();
        $ModelModel = new ModelModel();
        //检查该模型是否已经被使用
        $count = $CmsCategory->where("modelid", $modelId)->count();
        if ($count) {
            return self::createReturn(false, '', '该模型已经在使用中，请删除栏目后再进行删除！');
        }
        //这里可以根据缓存获取表名
        $modeldata = $ModelModel->where("modelid", $modelId)->findOrEmpty();
        if ($modeldata->isEmpty()) {
            return self::createReturn(false, '', '要删除的模型不存在！');
        }
        if ($modeldata->deleteModel($modelId)) {
            return self::createReturn(true, '', '删除成功！');
        } else {
            return self::createReturn(false, '', '删除失败');
        }

    }

}