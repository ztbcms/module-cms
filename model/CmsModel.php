<?php
/**
 * User: cycle_3
 * Date: 2020/12/2
 * Time: 11:23
 */

namespace app\cms\model;


use think\Model;

class CmsModel extends Model
{

    protected $name = 'model';

    /**
     * 获取可用模块列表
     * @return array
     */
    public function getAvailableList(){
        $where[] = ['disabled','=',0];
        $where[] = ['type','=',0];
        $availableList = $this->where($where)->select()->toArray() ?: [];
        return $availableList;
    }

    /**
     * 生成模型缓存，以模型ID为下标的数组
     * @param bool $isForce
     * @return array|mixed
     */
    public static function model_cache($isForce = false)
    {
        // 不强制则检查
        if(!$isForce){
            $check = cache('Model');
            if(empty($check)){
                $data = self::getModelAll();
                cache('Model', $data);
                return $data;
            }
            return $check;
        }
        $data = self::getModelAll();
        cache('Model', $data);
        return $data;
    }

    /**
     * 根据模型类型取得数据用于缓存
     * @param null $type
     * @return array
     */
    public static function getModelAll($type = null)
    {
        $where = array('disabled' => 0);
        if (!is_null($type)) {
            $where['type'] = $type;
        }
        $data = self::where($where)->select();
        $Cache = array();
        foreach ($data as $v) {
            $Cache[$v['modelid']] = $v;
        }
        return $Cache;
    }

}