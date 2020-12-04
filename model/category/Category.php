<?php
/**
 * User: cycle_3
 * Date: 2020/12/4
 * Time: 16:49
 */

namespace app\cms\model\category;

use think\Model;
use think\facade\App;
use app\common\libs\helper\TreeHelper;
use think\facade\Db;

/**
 * 栏目管理
 * Class Category
 * @package app\cms\model\category
 */
class Category extends Model
{

    protected $name = 'content_category';

}