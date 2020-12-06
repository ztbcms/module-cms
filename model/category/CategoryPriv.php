<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2020/12/6
 * Time: 13:07
 */

namespace app\cms\model\category;

use think\Model;

/**
 * 栏目权限管理
 * Class CategoryPriv
 * @package app\cms\model\category
 */
class CategoryPriv extends Model
{

    protected $name = 'content_category_priv';

    /**
     * 更新权限
     * @param $catid
     * @param $priv_datas
     * @param int $is_admin
     */
    public function update_priv($catid, $priv_datas = [], $is_admin = 1){

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

        return true;
    }

}