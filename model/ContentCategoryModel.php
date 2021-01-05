<?php
/**
 * Author: jayinton
 */

namespace app\cms\model;


use think\Model;

/**
 * 内容分类
 *
 * @package app\cms\model
 */
class ContentCategoryModel extends Model
{
    protected $name = 'content_category';

    /**
     * 获取栏目类型
     * @param $type
     *
     * @return string
     */
    static function getCategoryTypeName($type)
    {
        switch ($type){
            case 0:
                return '内容栏目';
            case 1:
                return '栏目组';
            case 2:
                return '外部链接';
            default:
                return '';
        }
    }
}