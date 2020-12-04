<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 9:51
 */

namespace app\cms\controller;


use app\common\controller\AdminController;
use think\App;
use think\facade\View;
use app\cms\service\ModelService;

/**
 * 模型管理
 * Class Model
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
        if($action == 'getModelsList') {
            //获取模型列表
            return ModelService::getModelsList();
        } else if($action == 'delModel') {
            //删除模型
            $modelid = input('modelid','','trim');
            return ModelService::delModel($modelid);
        } else if($action == 'disabled') {
            //禁用模型
            $modelid = input('modelid','','trim');
            $disabled = input('disabled',0,'trim') ? 1 : 0;
            $disabled = !$disabled;
            return ModelService::disabled($modelid,$disabled);
        }
        return View::fetch('index');
    }

    /**
     * 添加模型
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
     * @return array|\think\response\View
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            return ModelService::editModel($data);
        } else {
            return View('edit',ModelService::getBasicsConfig());
        }
    }

    /**
     * 获取模型详情
     * @return array
     */
    public function getDetail()
    {
        $modelId = input('modelid',0,'intval');
        return ModelService::getModelDetail($modelId);
    }

    /**
     * 模型导入
     * @return \think\response\Json|\think\response\View
     */
    public function import()
    {
        if ($this->request->isPost()) {
            if (empty($_FILES['file'])) {
                return self::makeJsonReturn(false, null, "请选择上传文件！");
            }
            $filename = $_FILES['file']['tmp_name'];
            if (strtolower(substr($_FILES['file']['name'], -3, 3)) != 'txt') {
                return self::makeJsonReturn(false, null, "上传的文件格式有误！");
            }
            //读取文件
            $data = file_get_contents($filename);
            //删除
            @unlink($filename);
            //模型名称

            $name = $this->request->post('name',null,'trim');
            //模型表键名
            $tablename = $this->request->post('tablename',null,'trim');
            //导入
            $ModelModel= new ModelModel();
            $status = $ModelModel->import($data, $tablename, $name);
            if ($status) {
                return self::makeJsonReturn(true, null, "模型导入成功，请及时更新缓存！");
            } else {
                $error = $ModelModel->error ?: '模型导入失败！';
                return self::makeJsonReturn(false, null, $error);
            }
        } else {
            return View();
        }
    }

    /**
     * 模型导出
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export()
    {
        //需要导出的模型ID
        $modelId = $this->request->get('modelid', 0, 'intval');
        if (empty($modelId)) {
            return self::makeJsonReturn(false, '', '请指定需要导出的模型!');
        }
        //导出模型
        $ModelModel = new ModelModel();
        $res = $ModelModel->export($modelId);
        if ($res) {
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=ztb_model_" . $modelId . '.txt');
            echo $res;
        } else {
            $error = $ModelModel->error ?: '模型导出失败！';
            return self::makeJsonReturn(false, '', $error);
        }
    }

}
