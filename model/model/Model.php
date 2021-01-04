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
                        'modelid' => $data['id']
                    ], '创建成功');
                } else {
                    //表创建失败
                    $this->where("modelid", $data['id'])->delete();
                    return createReturn(false, '', '数据表创建失败');
                }
            } else {
                return createReturn(false, '', '创建模块失败');
            }
        } else {
            return createReturn(false, '', '创建模块，检验失败');
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

    /**
     * 导入模型
     * @param $data
     * @param string $tablename
     * @param string $name
     * @return array
     */
    public function importModel($data, $tablename = '', $name = ''){
        if (!isset($data) || empty($data)) {
            return createReturn(false, '', '没有导入数据！');
        }

        //解析
        $data = json_decode(base64_decode($data), true);
        if (!isset($data) || empty($data)) {
            return createReturn(false, '', '解析数据失败，无法进行导入！');
        }

        //取得模型数据
        $model = $data['model'];
        if (!isset($model) || empty($model)) {
            return createReturn(false, '', '解析数据失败，无法进行导入！');
        }

        if ($name) $model['name'] = $name;
        if ($tablename) $model['tablename'] = $tablename;

        //导入模型
        $addModelRes = $this->addModel($model);

        if ($addModelRes['status']) {

            $modelid = $addModelRes['data']['modelid'];

            //处理模块的字段
            if (isset($data['field']) && !empty($data['field'])) {
                foreach ($data['field'] as $value) {
                    $value['modelid'] = $modelid;
                    if ($value['setting']) $value['setting'] = unserialize($value['setting']);
                    $ModelField = new ModelField();
                    // 添加字段
                    if ($ModelField->addField($value)['status']) {

                        $value['setting'] = serialize($value['setting']);
                        $ModelField->where([
                            ['modelid','=',$modelid],
                            ['field','=',$value['field']],
                            ['name','=',$value['name']]
                        ])->update($value);
                    }
                    unset($ModelField);
                }
            }
        }

        return $addModelRes;
    }

    /**
     * 生成模型缓存，以模型ID为下标的数组
     * 可用作获取
     * @param bool $isForce
     * @return array|mixed
     */
    public static function model_cache($isForce = false)
    {
        // 不强制则检查
        if(!$isForce){
            $check = cache('Model');
            if(empty($check)){
                $data = self::getModelAll();
                cache('Model', $data);
                return $data;
            }
            return $check;
        }
        $data = self::getModelAll();
        cache('Model', $data);
        return $data;
    }

    /**
     * 根据模型类型取得数据用于缓存
     * @param null $type
     * @return array
     */
    public static function getModelAll($type = null)
    {
        $where = array('disabled' => 0);
        if (!is_null($type)) {
            $where['type'] = $type;
        }
        $data = self::where($where)->select();
        $Cache = array();
        foreach ($data as $v) {
            $Cache[$v['modelid']] = $v;
        }
        return $Cache;
    }

    /**
     * 导出模型
     * @param $modelId
     * @return array|string
     */
    public function exportModel($modelId){
        if (empty($modelId)) {
            return createReturn(false, '', '请指定需要导出的模型！');
        }

        //取得模型信息
        $info = $this->where(array('modelid' => $modelId, 'type' => 0))->findOrEmpty()->toArray() ?: [];
        if (empty($info)) {
            return createReturn(false, '', '该模型不存在，无法导出！');
        }

        unset($info['modelid']);

        //数据
        $data = array();
        $data['model'] = $info;

        $ModelField = new ModelField();

        //取得对应模型字段
        $fieldList = $ModelField->where('modelid', $modelId)->select()->toArray() ?: [];
        if (empty($fieldList)) {
            $fieldList = array();
        }

        //去除fieldid，modelid字段内容
        foreach ($fieldList as $k => $v) {
            unset($fieldList[$k]['fieldid'], $fieldList[$k]['modelid']);
        }

        $data['field'] = $fieldList;
        $res['data'] = base64_encode(json_encode($data));
        return createReturn(true, $res);
    }

    /**
     * 获取可用模块列表
     * @return array
     */
    public function getAvailableList(){
        $where[] = ['disabled','=',0];
        $availableList = $this->where($where)->select()->toArray() ?: [];
        return $availableList;
    }

}