<?php
/**
 * Created by FHYI.
 * Date 2020/10/30
 * Time 19:01
 */

namespace app\cms\model;

/**
 * 栏目权限与角色之间的授权
 * Class CategoryPrivModel
 * @package app\cms\model
 */
class CategoryPrivModel extends BaseModel
{
    protected $name = 'category_priv';


    /**
     * 更新权限
     * @param $catid
     * @param $priv_datas
     * @param int $is_admin
     */
    function update_priv($catid, $priv_datas = [], $is_admin = 1){

        //删除旧的
        $where[] = ['catid','=',$catid];
        $where[] = ['is_admin','=',$is_admin];
        $this->where($where)->delete();

        if (is_array($priv_datas) && !empty($priv_datas)) {
            foreach ($priv_datas as $r) {
                $r = explode(',', $r);
                //动作
                $action = $r[0];
                //角色或者会员用户组
                $roleid = $r[1];
                $this->insert([
                    'catid' => $catid,
                    'roleid' => $roleid,
                    'is_admin' => $is_admin,
                    'action' => $action
                ]);
            }
        }
    }

}
