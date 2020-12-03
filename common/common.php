<?php

/**
 * 调试，用于保存数组到txt文件 正式生产删除
 * 用法：array2file($info, SITE_PATH.'post.txt');
 * @param array $array
 * @param string $filename
 * @return  boolean
 */
function array2file($array, $filename) {
	if (defined("APP_DEBUG") && APP_DEBUG) {
		//修改文件时间
		file_exists($filename) or touch($filename);
		if (is_array($array)) {
			$str = var_export($array, TRUE);
		} else {
			$str = $array;
		}
		return file_put_contents($filename, $str);
	}
	return false;
}

/**
 * 获取模型数据
 * @param string $modelid 模型ID
 * @param string $field 返回的字段，默认返回全部，数组
 * @return boolean|string
 */
function getModel($modelid, $field = '') {
    if (empty($modelid)) {
        return false;
    }
    $key = 'getModel_' . $modelid;
    $cache = cache($key);
    if ($cache === 'false') {
        return false;
    }
    if (empty($cache)) {
        //读取数据
        $cache = \think\facade\Db::name('model')->where('modelid', $modelid)->findOrEmpty();
        if (empty($cache)) {
            cache($key, 'false', 60);
            return false;
        } else {
            cache($key, $cache, 3600);
        }
    }
    if ($field) {
        return $cache[$field];
    } else {
        return $cache;
    }
}
