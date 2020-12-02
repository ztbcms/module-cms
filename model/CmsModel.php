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

}