<?php
/**
 * Created by FHYI.
 * Date 2020/10/29
 * Time 9:51
 */

namespace app\cms\controller;


use app\admin\service\AdminConfigService;
use app\cms\model\ModelModel;
use app\cms\model\CategoryModel;
use app\cms\service\ModelService;
use app\common\controller\AdminController;
use think\App;
use think\facade\Config;
use think\facade\View;

/**
 * 模型管理
 * Class Model
 * @package app\cms\controller
 */
class Model extends AdminController
{
    private $filepath;
    private $tp_category;
    private $tp_list;
    private $tp_show;
    private $tp_page;
    private $tp_comment;


    /**
     * Model constructor.
     * @param App $app
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        // 系统配置
        $adminConfigService = new AdminConfigService();
        $config = $adminConfigService->getConfig()['data'];

        //取得当前内容模型模板存放目录
        $this->filepath = Config::get('cms.template_path') . (empty($config['theme']) ? "Default" : $config['theme']) . "/Content/";
        //取得栏目频道模板列表
        $this->tp_category = str_replace($this->filepath . "CategoryModel/", '', glob($this->filepath . 'CategoryModel/category*'));
        //取得栏目列表模板列表
        $this->tp_list = str_replace($this->filepath . "List/", '', glob($this->filepath . 'List/list*'));
        //取得内容页模板列表
        $this->tp_show = str_replace($this->filepath . "Show/", '', glob($this->filepath . 'Show/show*'));
        //取得单页模板
        $this->tp_page = str_replace($this->filepath . "Page/", '', glob($this->filepath . 'Page/page*'));
        //取得评论模板列表
        $this->tp_comment = str_replace($this->filepath . "Comment/", '', glob($this->filepath . 'Comment/comment*'));
    }

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
        }
        return View::fetch('index');
    }

    /**
     * 添加模型
     * @return string|\think\response\Json
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (empty($data)) {
                return self::makeJsonReturn(false, '', '提交数据不能为空！');
            }
            $ModelModel = new ModelModel();
            $res = $ModelModel->addModel($data);
            if ($res) {
                return self::makeJsonReturn(true, '', '添加模型成功！');
            } else {
                $error = $ModelModel->error;
                return self::makeJsonReturn(false, '', $error ? $error : '添加失败！');
            }
        } else {
            View::assign([
                'tp_category'          => $this->tp_category,
                'tp_list'              => $this->tp_list,
                'tp_show'              => $this->tp_show,
                'tmpl_template_suffix' => Config::get('template.tmpl_template_suffix')
            ]);
            return View::fetch('add');
        }
    }

    /**
     * 模型的禁用与启用
     * @return \think\response\Json
     */
    public function disabled()
    {
        $modelId = $this->request->get('modelid', 0, 'intval');
        $disabled = $this->request->get('disabled', 0) ? 1 : 0;
        $disabled = !$disabled;
        $res = ModelModel::where('modelid', $modelId)->update(compact('disabled'));
        if ($res) {
            return self::makeJsonReturn(true, '', '操作成功！');
        } else {
            return self::makeJsonReturn(false, '', '操作失败！');
        }
    }

    /**
     * 编辑模型
     * @return \think\response\Json|\think\response\View
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (empty($data)) {
                return self::makeJsonReturn(false, '', '提交数据不能为空！');
            }
            $ModelModel = new ModelModel();
            if ($ModelModel->editModel($data)) {
                return self::makeJsonReturn(true, '', '模型修改成功！');
            } else {
                $error = $ModelModel->error;
                return self::makeJsonReturn(false, '', $error ? $error : '修改失败！');
            }
        } else {
            View::assign([
                'tp_category'          => $this->tp_category,
                'tp_list'              => $this->tp_list,
                'tp_show'              => $this->tp_show,
                'tmpl_template_suffix' => Config::get('template.tmpl_template_suffix')
            ]);
            return View();
        }
    }

    /**
     * 获取模型详情
     * @return \think\response\Json
     */
    public function getDetail()
    {
        $modelId = $this->request->get('modelid', 0, 'intval');
        $data = ModelModel::where("modelid", $modelId)->findOrEmpty();
        return self::makeJsonReturn(true, $data);
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
