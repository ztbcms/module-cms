<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 16:39
 */

use think\facade\Db;
use think\facade\Cache;

/**
 * 获取数据库配置
 * @param $key
 * @return mixed
 */
function getDbConfig($key = null)
{
    $config = \think\facade\Config::get('database');
    if (!empty($key)) {
        $config = $config['connections'][$config['default']];
        return $config[$key];
    }

    return $config['connections'][$config['default']];
}

// 设置栏目缓存全部
function getAllCategory(){

}

/**
 * 获取栏目相关信息
 * @param string $catid 栏目id
 * @param string $field 返回的字段，默认返回全部，数组
 * @param boolean $newCache 是否强制刷新
 * @return boolean
 */
function getCategory($catid, $field = '', $newCache = false)
{
    if (empty($catid)) {
        return false;
    }
    $key = 'getCategory_' . $catid;
    //强制刷新缓存
    if ($newCache) {
        cache($key, NULL);
    }
    $cache = cache($key);
    if ($cache === 'false') {
        return false;
    }
    if (empty($cache)) {
        //读取数据
        $cache = \app\cms\model\CmsCategory::where('catid', $catid)->findOrEmpty();
        if (empty($cache)) {
            cache($key, 'false', 60);
            return false;
        } else {
            //扩展配置
            $cache['setting'] = unserialize($cache['setting']);
            //栏目扩展字段
            $cache['extend'] = $cache['setting']['extend'] ?? [];
            cache($key, $cache, 3600);
        }
    }
    if ($field) {
        //支持var.property，不过只支持一维数组
        if (false !== strpos($field, '.')) {
            $vars = explode('.', $field);
            return $cache[$vars[0]][$vars[1]];
        } else {
            return $cache[$field];
        }
    } else {
        return $cache;
    }
}

