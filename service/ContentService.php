<?php
/**
 * User: cycle_3
 * Date: 2020/12/3
 * Time: 15:46
 */

namespace app\cms\service;

use app\cms\model\CmsCategory;
use app\cms\model\CmsModel;
use app\cms\model\CmsModelField;
use app\common\service\BaseService;
use think\facade\Db;

/**
 * 内容管理
 * Class ContentService
 * @package app\cms\service
 */
class ContentService extends BaseService
{

    /**
     * 获取显示的字段
     * @param int $catid
     * @return array
     */
    static function getDisplaySettin($catid = 0){
        $CmsCategory = new CmsCategory();
        $CmsModelField = new CmsModelField();
        $CategoryDetails = $CmsCategory->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        //列表中显示的字段
        $where[] = ['issystem','=',1];
        $where[] = ['modelid','=',$modelid];

        $cmsModelIsaddWhere[] = ['isadd','=',1];
        $fieldList = $CmsModelField->where($cmsModelIsaddWhere)->where($where)->order('listorder asc')->select()->toArray() ?: [];

        //筛选条件中显示的字段
        $cmsModelIsbaseWhere[] = ['isbase','=',1];
        $baseList = $CmsModelField->where($cmsModelIsbaseWhere)->where($where)->order('listorder asc')->select()->toArray() ?: [];

        $res['field_list'] = $fieldList;
        $res['base_list'] = $baseList;
        return self::createReturn(true,$res);
    }

    /**
     * 获取模板列表
     * @param int $catid
     * @param $where
     * @param int $limit
     * @return array
     */
    static function getTemplateList($catid = 0,$where = []){
        $CmsCategory = new CmsCategory();
        $CmsModel = new CmsModel();
        $CategoryDetails = $CmsCategory->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        $modelWhere[] = ['modelid','=',$modelid];
        $modelDetails = $CmsModel->where($modelWhere)->find()->toArray() ?: [];
        $tablename = $modelDetails['tablename'];
        $where[] = ['catid','=',$catid];
        $lists = Db::name($tablename)->where($where)->order('id', 'DESC')->paginate(20);
        return self::createReturn(true, $lists);
    }

    /**
     * 删除数据信息
     * @param int $catid
     * @param int $id
     * @return array
     */
    static function delTemplate($catid = 0,$id = 0){
        $CmsCategory = new CmsCategory();
        $CmsModel = new CmsModel();
        $CategoryDetails = $CmsCategory->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        $modelWhere[] = ['modelid','=',$modelid];
        $modelDetails = $CmsModel->where($modelWhere)->find()->toArray() ?: [];
        $tablename = $modelDetails['tablename'];

        $where[] = ['catid','=',$catid];
        $where[] = ['id','=',$id];
        Db::name($tablename)->where($where)->delete();
        return self::createReturn(true, '','删除成功！');
    }

    /**
     * 获取详情页显示的字段
     * @param int $catid
     * @return array
     */
    static function getDetailsDisplaySettin($catid = 0){
        $CmsCategory = new CmsCategory();
        $CmsModelField = new CmsModelField();
        $CategoryDetails = $CmsCategory->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        //列表中显示的字段
        $where[] = ['modelid','=',$modelid];
        $fieldList = $CmsModelField->where($where)->order('listorder asc')->select()->toArray() ?: [];
        $res['field_list'] = $fieldList;

        $formData = [];
        foreach ($fieldList as $k => $v){
            $formData[$v['field']] = '';
        }
        $res['form_data'] = $formData;
        return self::createReturn(true,$res);
    }


}