<?php
/**
 * User: cycle_3
 * Date: 2020/12/3
 * Time: 15:46
 */

namespace app\cms\service;

use app\cms\model\category\Category;
use app\cms\model\model\Model;
use app\cms\model\model\ModelField;
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

        $Category = new Category();
        $ModelField = new ModelField();
        $CategoryDetails = $Category->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        //列表中显示的字段
        $where[] = ['issystem','=',1];
        $where[] = ['modelid','=',$modelid];

        $cmsModelIsaddWhere[] = ['isadd','=',1];
        $fieldList = $ModelField->where($cmsModelIsaddWhere)->where($where)->order('listorder asc')->select()->toArray() ?: [];

        //筛选条件中显示的字段
        $cmsModelIsbaseWhere[] = ['issearch','=',1];
        $baseList = $ModelField->where($cmsModelIsbaseWhere)->where($where)->order('listorder asc')->select()->toArray() ?: [];

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
        $Category = new Category();
        $Model = new Model();
        $CategoryDetails = $Category->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        $modelWhere[] = ['modelid','=',$modelid];
        $modelDetails = $Model->where($modelWhere)->find()->toArray() ?: [];
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
        $Category = new Category();
        $Model = new Model();

        $CategoryDetails = $Category->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        $modelWhere[] = ['modelid','=',$modelid];
        $modelDetails = $Model->where($modelWhere)->find()->toArray() ?: [];
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
    static function getDetailsDisplaySettin($catid = 0,$id = 0){

        $Category = new Category();
        $Model = new Model();
        $ModelField = new ModelField();
        $CategoryDetails = $Category->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        $contentDetails = [];
        if($id > 0){
            $modelWhere[] = ['modelid','=',$modelid];
            $modelDetails = $Model->where($modelWhere)->find()->toArray() ?: [];
            $tablename = $modelDetails['tablename'];
            $contentWhere[] = ['catid','=',$catid];
            $contentWhere[] = ['id','=',$id];
            $contentDetails = Db::name($tablename)->where($contentWhere)
                ->find() ?: [];
        }

        //列表中显示的字段
        $where[] = ['modelid','=',$modelid];
        $fieldList = $ModelField->where($where)->order('listorder asc')->select()->toArray() ?: [];

        //处理解密后的数据
        foreach ($fieldList as $k => $v){
            if(isset($v['setting'])) {
                $setting = unserialize($v['setting']);
                if(isset($setting['options'])) {
                    $options = explode("\n",$setting['options']);
                    $option = [];
                    foreach ($options as $k2 => $v2) {
                        $option_val = explode("|", $v2);
                        if(isset($option_val[1])) {
                            $option[trim($option_val[1])] = $option_val[0];
                        }
                    }
                    $setting['option_list'] = $option;
                }
                $fieldList[$k]['setting']  = $setting;
            }
        }

        $res['field_list'] = $fieldList;

        $formData = [];
        $formData['id'] = $id;
        foreach ($fieldList as $k => $v){
            if($v['formtype'] == 'catid') {
                $formData[$v['field']] = $catid;
            } else {
                if(isset($contentDetails[$v['field']])) {
                    //是否存在值
                    $content = $contentDetails[$v['field']];
                    if(is_numeric($content)) {
                        $content = (string)$content;
                    }
                    if($v['formtype'] == 'datetime') {
                        $content = date("Y-m-d H:i",$content);
                    }
                    $formData[$v['field']] = $content;
                } else {
                    $formData[$v['field']] = '';
                }
            }
        }
        $res['form_data'] = $formData;
        return self::createReturn(true,$res);
    }

    /**
     * 提交信息
     * @param array $post
     * @return array
     */
    static function submitForm($post = []){

        if(!$post['catid']) return self::createReturn(false,'','栏目id不能为空');

        $catid = $post['catid'];

        $Model = new Model();
        $Category = new Category();
        $ModelField = new ModelField();


        $CategoryDetails = $Category->where(['catid'=>$catid])->find()->toArray() ?: [];
        $modelid = $CategoryDetails['modelid'];

        $modelWhere[] = ['modelid','=',$modelid];
        $modelDetails = $Model->where($modelWhere)->find()->toArray() ?: [];
        $tablename = $modelDetails['tablename'];

        $where[] = ['modelid','=',$modelid];
        $fieldList = $ModelField->where($where)->order('listorder asc')->select()->toArray() ?: [];

        $issystem_content = [];
        $no_issystem_content = [];
        foreach ($fieldList as $k => $v) {
            if(isset($post[$v['field']])) {
                if($v['formtype'] == 'datetime') {
                    //时间类型的参数进行转换
                    $post[$v['field']] = strtotime($post[$v['field']]);
                }

                //存在该参数
                if($v['issystem']) {
                    //判断是否为主表
                    $issystem_content[$v['field']] = $post[$v['field']];
                } else {
                    $no_issystem_content[$v['field']] = $post[$v['field']];
                }
            }
        }
        unset($no_issystem_content['pages']);
        $tableWhere[] = ['catid','=',$catid];
        if($post['id'] <= 0) {
            //新增信息
            $id = Db::name($tablename)->insertGetId($issystem_content);
            $no_issystem_content['id'] = $id;
            Db::name($tablename.'_data')->insertGetId($no_issystem_content);
        } else {
            //编辑信息
            $tableWhere[] = ['id','=',$post['id']];
            Db::name($tablename)->where($tableWhere)->update($issystem_content);
            Db::name($tablename.'_data')->where([
                ['id','=',$post['id']]
            ])->update($no_issystem_content);
        }
        return self::createReturn(true,'','操作成功');
    }


}