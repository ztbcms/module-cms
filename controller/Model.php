<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 9:51
 */

namespace app\cms\controller;

use app\cms\service\ContentModelService;
use app\common\controller\AdminController;
use think\App;
use think\facade\Config;
use think\facade\View;
use app\cms\service\ModelService;

/**
 * 模型管理
 * Class Model
 *
 * @package app\cms\controller
 */
class Model extends AdminController
{
    /**
     * 显示模型列表
     */
    public function index()
    {
        $action = input('action', '', 'trim');
        if ($action == 'getModelsList') {
            //获取模型列表
            return ModelService::getModelsList();
        } else {
            if ($action == 'delModel') {
                //删除模型
                $modelid = input('modelid', '', 'trim');
                return ModelService::delModel($modelid);
            } else {
                if ($action == 'disabled') {
                    //禁用模型
                    $modelid = input('modelid', '', 'trim');
                    $disabled = input('disabled', 0, 'trim') ? 1 : 0;
                    $disabled = !$disabled;
                    return ModelService::disabled($modelid, $disabled);
                }
            }
        }
        return View::fetch('index');
    }

    /**
     * 添加模型
     * @deprecated
     * @return array|string
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            return ModelService::addModel($data);
        } else {
            return View::fetch('add', ModelService::getBasicsConfig());
        }
    }


    /**
     * 编辑模型
     * @deprecated
     * @return array|\think\response\View
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            return ModelService::editModel($data);
        } else {
            return View('edit', ModelService::getBasicsConfig());
        }
    }

    /**
     * 添加模型
     *
     * @return \think\response\Json|\think\response\View
     */
    function addModel()
    {
        $action = input('_action', '');
        if ($this->request->isGet() && $action == 'getFormParam') {
            return $this->_getModelFormParam();
        }
        if ($this->request->isPost()) {
            $data = input('post.');
            $res = ContentModelService::addModel($data);
            return json($res);
        }

        return view('addOrEditModel');
    }

    /**
     * @return array|\think\response\Json|\think\response\View|void
     */
    function editModel()
    {
        $action = input('_action', '');
        if ($this->request->isGet() && $action == 'getFormParam') {
            return $this->_getModelFormParam();
        }
        if ($this->request->isGet() && $action == 'getDetail') {
            $modelid = input('modelid', 0, 'intval');
            $res = ContentModelService::getModel($modelid);
            return json($res);
        }

        if ($this->request->isPost()) {
            $data = input('post.');
            $res =  ContentModelService::editModel($data);
            return json($res);
        }

        return view('addOrEditModel');
    }

    /**
     * @return \think\response\Json
     */
    function _getModelFormParam()
    {
        $ret = [
            'table_prefix'        => $tablepre = Config::get('database.connections.'.Config::get('database.default').'.prefix').'content_',
            'category_template'   => 'category.php',
            'list_template'       => 'list.php',
            'show_template'       => 'show.php',
            'list_customtemplate' => 'list.php',
            'add_customtemplate'  => 'add.php',
            'edit_customtemplate' => 'edit.php'
        ];

        return self::makeJsonReturn(true, $ret);
    }

    /**
     * 获取模型详情
     *
     * @return array
     */
    public function getDetail()
    {
        $modelId = input('modelid', 0, 'intval');
        return ModelService::getModelDetail($modelId);
    }

    /**
     * 模型导入
     *
     * @return array|\think\response\View
     */
    public function import()
    {
        if ($this->request->isPost()) {
            //模型表键名
            $tablename = input('tablename', '', 'trim');
            //模型名称
            $name = input('name', '', 'trim');
            return json(ModelService::importModel($tablename, $name));
        } else {
            return View();
        }
    }

    /**
     * 模型导出
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export()
    {
        //需要导出的模型ID
        $modelId = input('modelid', 0, 'intval');
        return json(ModelService::exportModel($modelId));
    }

}
