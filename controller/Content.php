<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 9:52
 */

namespace app\cms\controller;

use app\common\controller\AdminController;
use think\facade\View;

/**
 * 内容管理
 * Class Content
 * @package app\cms\controller
 */
class Content extends AdminController
{
    // TODO
    public function index(){
        return View::fetch('');
    }
}
