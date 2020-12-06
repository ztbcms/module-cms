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
        $cache = \app\cms\model\category\Category::where('catid', $catid)->findOrEmpty();
        if (empty($cache)) {
            cache($key, 'false', 60);
            return false;
        } else {
            //扩展配置
            $cache['setting'] = unserialize($cache['setting']);
            //栏目扩展字段
            $cache['extend'] = isset($cache['setting']['extend']) ?: [];
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

/**
 * 递归实现无限极分类 根据给定的散列数组结构，整理成树状结构
 *
 * @param $array
 * @param  int  $pid  父ID
 * @param  int  $level  分类级别
 * @return array 分好类的数组 直接遍历即可 $level可以用来遍历缩进
 */
function getTreeShapeArray(array $array = [], $pid = 0, $level = 0,$config = [])
{
    $curConfig = [
        'idKey'       => isset($config['idKey']) ? $config['idKey'] : 'id',// 节点的ID字段名
        'parentKey'   => isset($config['parentKey']) ? $config['parentKey'] : 'parentid', // 父节点的ID字段名
        'levelKey'    => isset($config['levelKey']) ? $config['levelKey'] : 'level',// 层级的key名，按从1开始
    ];

    //声明静态数组,避免递归调用时,多次声明导致数组覆盖
    static $list = [];
    foreach ($array as $key => $value) {
        //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
        if ($value[$curConfig['parentKey']] == $pid) {
            //父节点为根节点的节点,级别为0，也就是第一级
            $value[$curConfig['levelKey']] = $level;

            if($value['child']) $value['catname'] .= '(目录)';
            else $value['catname'] .= '(内容)';

            //把数组放到list中
            $list[] = $value;
            //把这个节点从数组中移除,减少后续递归消耗
            unset($array[$key]);

            //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
            getTreeShapeArray($array, $value[$curConfig['idKey']], $level + 1,$config);
        }
    }
    return $list;
}

/**
 * 根据给定的散列数组结构，整理成树状结构
 * @param  array  $array  待处理数组
 * @param  int|string  $pid  父节点ID
 * @param  array  $config  配置
 * @param  int  $level
 *
 * @return array 散列数组
 */
function arrayToTree(array $array, $pid, $config = [], $level = 0)
{
    $curConfig = [
        'idKey'       => isset($config['idKey']) ? $config['idKey'] : 'id',// 节点的ID字段名
        'parentKey'   => isset($config['parentKey']) ? $config['parentKey'] : 'pid', // 父节点的ID字段名
        'childrenKey' => isset($config['childrenKey']) ? $config['childrenKey'] : 'children', // 子列表的key名
        'maxLevel'    => isset($config['maxLevel']) ? $config['maxLevel'] : 0,// 最大层级，0为不限制。父节点算一层
        'levelKey'    => isset($config['levelKey']) ? $config['levelKey'] : 'level',// 层级的key名，按从1开始
    ];
    $nodeList = [];
    foreach ($array as $index => $item) {
        if ($item[$curConfig['parentKey']] == $pid) {
            // 寻找下一级
            if ($curConfig['maxLevel'] === 0 || $level + 1 <= $curConfig['maxLevel']) {
                if (!empty($curConfig['levelKey'])) {
                    $item[$curConfig['levelKey']] = $level;
                }

                if($item['child']) $item['catname'] .= '(目录)';
                else $item['catname'] .= '(内容)';

                $item[$curConfig['childrenKey']] = arrayToTree($array, $item[$curConfig['idKey']], $config, $level + 1);
            }
            $nodeList[] = $item;
        }
    }
    return $nodeList;
}

