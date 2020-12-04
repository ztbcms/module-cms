<?php
/**
 * User: cycle_3
 * Date: 2020/12/4
 * Time: 16:39
 */

namespace app\cms\model\model;

use app\cms\libs\module\Operation as ModuleOperation;
use think\Model as PublicModel;
use think\facade\Cache;

/**
 * 模型管理
 * Class Model
 * @package app\cms\model\model
 */
class Model extends PublicModel
{

    protected $name = 'content_model';

    /**
     * 删除模型和模型的表
     * @param int $modelId
     * @return array
     */
    public function deleteModel($modelId = 0)
    {
        if (empty($modelId)) return createReturn(false, '', '删除的id不能为空');

        //这里可以根据缓存获取表名
        $modelData = $this->where("modelid", $modelId)->findOrEmpty();
        if ($modelData->isEmpty()) {
            return createReturn(false, '', '删除的内容不存在');
        }

        //表名
        $modelTable = $modelData['tablename'];

        $this->startTrans();
        //删除模型表的数据
        $this->where("modelid", $modelId)->delete();

        //删除所有和这个模型相关的字段
        $ModelField = new ModelField();
        $ModelField->where([
            ['modelid', '=', $modelId]
        ])->delete();

        $ModuleOperation = new ModuleOperation();

        //删除主表信息
        $ModuleOperation->deleteTable($modelTable);
        if ((int)$modelData['type'] == 0) {
            //删除副表
            $ModuleOperation->deleteTable($modelTable . "_data");
        }
        $this->commit();
        return createReturn(true, '', '删除成功');
    }

    /**
     * 添加模型
     * @param array $data
     * @return array
     */
    public function addModel($data = [])
    {
        if (empty($data) && !isset($data)) {
            return createReturn(false, '', '提交数据不能为空！');
        }

        //数据验证
        $validate = new \app\cms\validate\Model();
        if (!$validate->check($data)) {
            return createReturn(false, '', $validate->getError());
        }

        // 是否重复模型名称
        $checkName = $this->where([
            ['name', '=', $data['name']]
        ])->findOrEmpty();
        if (!$checkName->isEmpty()) {
            return createReturn(false, '', '该模型名称已经存在!');
        }

        // 是否重复表名称
        $ModuleOperation = new ModuleOperation();
        $checkTableName = $ModuleOperation->checkTablename($data['tablename']);
        if (!$checkTableName) {
            return createReturn(false, '', '该表名已经存在!');
        }

        // 检查sql文件是否正常
        $checkTablesql = $ModuleOperation->checkTablesql();
        if (!$checkTablesql) {
            return createReturn(false, '', '创建模型所需要的SQL文件丢失，创建失败!');
        }

        $data['addtime'] = time();
        $this->startTrans();
        $data = $this->create($data);
        if ($data) {
            //强制表名为小写
            $data['tablename'] = strtolower($data['tablename']);
            //添加模型记录
            $modelid = $data->save($data->toArray());
            if ($modelid) {
                //创建数据表
                if ($ModuleOperation->createModel($data['tablename'], $data['id'])) {
                    Cache::set("Model", NULL);
                    $this->commit();
                    return createReturn(true, [
                        'modelid' => $modelid
                    ], '创建成功!');
                } else {
                    //表创建失败
                    $this->where("modelid", $modelid)->delete();
                    return createReturn(false, '', '数据表创建失败!');
                }
            } else {
                return createReturn(false, '', '创建模块失败!');
            }
        } else {
            return createReturn(false, '', '创建模块，检验失败!');
        }
    }

    /**
     * 编辑模型
     * @param array $data
     * @param int $modelid
     * @return array
     */
    public function editModel($data = [], $modelid = 0)
    {
        //模型ID
        $modelid = $modelid ? $modelid : (int)$data['modelid'];
        if (!$modelid) {
            return createReturn(false, '', '模型ID不能为空！');
        }

        //查询模型数据
        $info = $this->where("modelid", $modelid)->findOrEmpty();
        if ($info->isEmpty()) {
            return createReturn(false, '', '该模型不存在！');
        }

        $data['modelid'] = $modelid;
        //数据验证
        $validate = new \app\cms\validate\Model();
        if (!$validate->check($data)) {
            return createReturn(false, '', $validate->getError());
        }

        // 是否重复模型名称
        $checkName = $this
            ->where('name', '=', $data['name'])
            ->where('modelid', '<>', $data['modelid'])
            ->findOrEmpty();
        if (!$checkName->isEmpty()) {
            return createReturn(false, '', '该模型名称已经存在！');
        }
        $ModuleOperation = new ModuleOperation();
        // 检查sql文件是否正常
        $checkTablesql = $ModuleOperation->checkTablesql();
        if (!$checkTablesql) {
            return createReturn(false, '', '创建模型所需要的SQL文件丢失，创建失败！');
        }
        if ($data) {
            //强制表名为小写
            $data['tablename'] = strtolower($data['tablename']);
            //是否更改表名
            if ($info['tablename'] != $data['tablename'] && !empty($data['tablename'])) {
                //检查新表名是否存在
                if ($ModuleOperation->table_exists($data['tablename']) || $ModuleOperation->table_exists($data['tablename'] . '_data')) {
                    return createReturn(false, '', '该表名已经存在！');
                }
                if (false !== $this->where(array("modelid" => $modelid))->save($data)) {
                    //表前缀
                    $dbPrefix = $ModuleOperation->dbConfig['prefix'];
                    //表名更改
                    if (!$ModuleOperation->sql_execute("RENAME TABLE  `{$dbPrefix}{$info['tablename']}` TO  `{$dbPrefix}{$data['tablename']}` ;")) {
                        return createReturn(false, '', '数据库修改表名失败！');
                    }
                    //修改副表
                    if (!$ModuleOperation->sql_execute("RENAME TABLE  `{$dbPrefix}{$info['tablename']}_data` TO  `{$dbPrefix}{$data['tablename']}_data` ;")) {
                        //主表已经修改，进行回滚
                        $ModuleOperation->sql_execute("RENAME TABLE  `{$dbPrefix}{$data['tablename']}` TO  `{$dbPrefix}{$info['tablename']}` ;");
                        return createReturn(false, '', '数据库修改副表表名失败！');
                    }
                    return createReturn(true, '', '更新成功！');
                } else {
                    return createReturn(false, '', '模型更新失败！');
                }
            } else {
                if (false !== $this->where("modelid", $modelid)->save($data)) {
                    return createReturn(true, '', '更新成功！');
                } else {
                    return createReturn(false, '', '模型更新失败！');
                }
            }
        } else {
            return createReturn(false, '', '编辑模型失败！');
        }
    }

}