<?php
/**
 * User: cycle_3
 * Date: 2020/12/2
 * Time: 11:23
 */

namespace app\cms\model;


use think\Model;
use think\facade\App;
use app\common\libs\helper\TreeHelper;

class CmsCategory extends Model
{

    protected $name = 'category';

    /**
     * 获取可用列表
     * @return array
     */
    public function getAvailableList()
    {
        $array = $this->select()->toArray() ?: [];
        foreach ($array as $k => $v) {
            $array[$k] = getCategory($v['catid']);
            if ($v['child'] == '0') {
                $array[$k]['disabled'] = "disabled";
            } else {
                $array[$k]['disabled'] = "";
            }
        }
        return $array;
    }

    /**
     * 进行添加栏目操作
     * @param $post
     * @return array
     */
    public function addCategory($post = [])
    {
        if (empty($post)) {
            return createReturn(false, '', '添加栏目数据不能为空！');
        }
        $data = $post['info'];
        //栏目类型
        if (isset($post['type'])) {
            $data['type'] = (int)$post['type'];
        } else {
            $data['type'] = 0;
        }
        $data['setting'] = $post['setting'];
        //终极栏目设置
        if (!isset($data['child'])) {
            $data['child'] = 1;
        }
        //栏目生成静态配置
        if ($data['setting']['ishtml']) {
            $data['setting']['category_ruleid'] = $post['category_html_ruleid'];
        } else {
            $data['setting']['category_ruleid'] = $post['category_php_ruleid'];
        }
        //栏目是否生成静态
        $data['sethtml'] = $data['setting']['ishtml'] ? 1 : 0;
        //内容生成静态配置
        if ($data['setting']['content_ishtml']) {
            $data['setting']['show_ruleid'] = $post['show_html_ruleid'];
        } else {
            $data['setting']['show_ruleid'] = $post['show_php_ruleid'];
        }

        //判断URL规则是否有设置
        if ((int)$data['type'] == 0) {
            //内部栏目
            if (empty($data['setting']['category_ruleid'])) {
                return createReturn(false, '', '栏目URL规则没有设置');
            }
            if (empty($data['setting']['show_ruleid']) && $data['child']) {
                return createReturn(false, '', '栏目内容页URL规则没有设置！');
            }
            //添加modelid自动验证规则
            if (!$data['modelid']) {
                return createReturn(false, '', '对不起所属的模型不能为空！');
            }
        } else if ((int)$data['type'] == 1) {
            //单页栏目
            if (empty($data['setting']['category_ruleid'])) {
                return createReturn(false, '', '栏目URL规则没有设置！');
            }
        }

        include App::getRootPath() . 'app/cms/common/iconvfunc.php';
        //栏目拼音
        $catname = iconv('utf-8', 'gbk', $data['catname']);
        $letters = gbk_to_pinyin($catname);
        $data['letter'] = strtolower(implode('', $letters));
        //序列化setting数据
        $data['setting'] = serialize($data['setting']);

        if ((int)$data['type'] != 2 && isset($data['url'])) {
            //绑定域名
            $data['domain'] = $data['url'];
        }

        if (!$data['catname']) return createReturn(false, '', '栏目名称不能为空！');
        if (!$data['catdir']) return createReturn(false, '', '英文目录不能为空！');

        if ($this->where(['catdir' => $data['catdir']])->count()) {
            return createReturn(false, '', '目录名称已存在！');
        };

        if (!self::checkSetting($data['setting'], '1')) {
            return createReturn(false, '', 'Setting配置信息有误！');
        }

        if (!($data['type'] == 0 || $data['type'] == 1 || $data['type'] == 2)) {
            return createReturn(false, '', '栏目类型错误！');
        }

        $catid = $this->insertGetId($data);
        if ($catid) {
            //更新缓存
            cache('Category', NULL);
            //更新附件状态
            if (isset($data['image'])) {
                //todo 更新附件状态，把相关附件和文章进行管理
//                service("Attachment")->api_update('', 'catid-' . $catid, 1);
            }
            //扩展字段处理
            if (isset($post['extend'])) {
                $this->extendField($catid, $post);
            }
            return createReturn(true,['catid'=>$catid],'添加成功');
        } else {
            return createReturn(false, '', '栏目添加失败！');
        }
    }

    /**
     * 编辑栏目信息
     * @param array $post
     * @return array|bool
     */
    public function editCategory($post = []){
        if (empty($post)) {
            return createReturn(false, '', '添加栏目数据不能为空！');
        }
        $catid = $post['catid'];
        $data = $post['info'];
        //查询该栏目是否存在
        $info = $this->where(array('catid' => $catid))->find();
        if (empty($info)) {
            return createReturn(false, '', '该栏目不存在！');
        }
        unset($data['catid'], $info['catid'], $data['module'], $data['child']);

        //表单令牌
        $data['setting'] = $post['setting'];

        //内部栏目
        if ((int) $info['type'] != 2) {
            if ($data['setting']['ishtml']) {
                $data['setting']['category_ruleid'] = $post['category_html_ruleid'];
            } else {
                $data['setting']['category_ruleid'] = $post['category_php_ruleid'];
                $data['url'] = '';
            }
        }

        //栏目生成静态配置
        if ($data['setting']['ishtml']) {
            $data['setting']['category_ruleid'] = $post['category_html_ruleid'];
        } else {
            $data['setting']['category_ruleid'] = $post['category_php_ruleid'];
        }
        //内容生成静态配置
        if ($data['setting']['content_ishtml']) {
            $data['setting']['show_ruleid'] = $post['show_html_ruleid'];
        } else {
            $data['setting']['show_ruleid'] = $post['show_php_ruleid'];
        }
        //栏目是否生成静态
        $data['sethtml'] = $data['setting']['ishtml'] ? 1 : 0;
        //判断URL规则是否有设置
        if ((int) $info['type'] == 0) {
            //内部栏目
            if (empty($data['setting']['category_ruleid'])) {
                return createReturn(false, '', '栏目URL规则没有设置！');
            }
            if (empty($data['setting']['show_ruleid']) && isset($data['child'])) {
                return createReturn(false, '', '栏目内容页URL规则没有设置！');
            }

            //添加modelid自动验证规则
            if (!$data['modelid']) {
                return createReturn(false, '', '对不起所属的模型不能为空！');
            }
        } else if ((int) $info['type'] == 1) {

            //单页栏目
            if (empty($data['setting']['category_ruleid'])) {
                return createReturn(false, '', '栏目URL规则没有设置！');
            }
        }

        //栏目生成静态配置
        if ($data['setting']['ishtml']) {
            $data['setting']['category_ruleid'] = $post['category_html_ruleid'];
        } else {
            $data['setting']['category_ruleid'] = $post['category_php_ruleid'];
        }

        //内容生成静态配置
        if ($data['setting']['content_ishtml']) {
            $data['setting']['show_ruleid'] = $post['show_html_ruleid'];
        } else {
            $data['setting']['show_ruleid'] = $post['show_php_ruleid'];
        }
        //栏目是否生成静态
        $data['sethtml'] = $data['setting']['ishtml'] ? 1 : 0;

        //判断URL规则是否有设置
        if ((int) $info['type'] == 0) {
            //内部栏目
            if (empty($data['setting']['category_ruleid'])) {
                return createReturn(false, '', '栏目URL规则没有设置！');
            }

            if (empty($data['setting']['show_ruleid']) && isset($data['child'])) {
                return createReturn(false, '', '栏目内容页URL规则没有设置！');
            }

            //添加modelid自动验证规则
            if (!$data['modelid']) {
                return createReturn(false, '', '对不起所属的模型不能为空！');
            }
        } else if ((int) $info['type'] == 1) {
            //单页栏目
            if (empty($data['setting']['category_ruleid'])) {
                return createReturn(false, '', '栏目URL规则没有设置！');
            }
        }

        include App::getRootPath() . 'app/cms/common/iconvfunc.php';
        //栏目拼音
        $catname = iconv('utf-8', 'gbk', $data['catname']);
        $letters = gbk_to_pinyin($catname);
        $data['letter'] = strtolower(implode('', $letters));
        //序列化setting数据
        $data['setting'] = serialize($data['setting']);

        if (!$data['catname']) return createReturn(false, '', '栏目名称不能为空！');
        if (!$data['catdir']) return createReturn(false, '', '英文目录不能为空！');


        if ($this->where([
            ['catdir' ,'=', $data['catdir']],
            ['catid' ,'<>', $catid]
        ])->count()) {
            return createReturn(false, '', '目录名称已存在！');
        };

//        if (!self::checkSetting($data['setting'], 2)) {
//            return createReturn(false, '', 'Setting配置信息有误！');
//        }

//        if (!($data['type'] == 0 || $data['type'] == 1 || $data['type'] == 2)) {
//            return createReturn(false, '', '栏目类型错误！');
//        }

//        if ((int) $info['type'] != 2) {
//            //绑定域名
//            $data['domain'] = $data['url'];
//        }

        if ($this->where(['catid'=>$catid])->update($data) !== false) {
            //更新缓存
            cache('Category', NULL);
            getCategory($catid, '', true);
            //更新附件状态
            if (isset($data['image'])) {
                //更新附件状态，把相关附件和文章进行管理
//                service("Attachment")->api_update('', 'catid-' . $catid, 1);
            }
            //扩展字段处理
            if (isset($post['extend'])) {
                $this->extendField($catid, $post);
            }
            return createReturn(true,['catid'=>$catid],'编辑成功');
        } else {
            return createReturn(false, '', '栏目修改失败！');
        }
    }

    /**
     * 验证setting配置信息
     * @param string $setting
     * @return boolean
     */
    static function checkSetting($setting, $type = "")
    {
        $type = $type ? $type : (int)$_REQUEST['type'];
        if ($type == 2) {
            return true;
        }
        if (!$setting) {
            return true;
        }
        $setting = unserialize($setting);
        if ((!$setting['category_ruleid'] || !$setting['category_ruleid']) && (int)$type != 2) {
            return false;
        }
        return true;
    }

    /**
     * 扩展字段处理
     * @param string $catid 栏目ID
     * @param array $post 数据
     * @return boolean
     */
    public function extendField($catid, $post)
    {
        if (empty($catid) || intval($catid) < 1 || empty($post)) {
            return createReturn(false, '', '栏目不能为空！');
        }

        //时间
        $time = time();
        //栏目信息
        $info = $this->where(array('catid' => $catid))->find();
        if (empty($info)) {
            return createReturn(false, '', '栏目不能为空！');
        }
        $info['setting'] = unserialize($info['setting']);

        $CmsCategoryFieldModel = new CmsCategoryField();

        //删除不存在的选项
        if (!empty($post['extenddelete'])) {
            $extenddelete = explode('|', $post['extenddelete']);
            $CmsCategoryWhere[] = ['fid', 'IN', $extenddelete];
            $CmsCategoryFieldModel->where($CmsCategoryWhere)->delete();
        }

        //查询出该栏目扩展字段列表
        $extendFieldLisr = array();
        foreach (
            $CmsCategoryFieldModel
                ->where(array('catid' => $catid))
                ->field('fieldname')
                ->select()->toArray() as $rs
        ) {
            $extendFieldLisr[] = $rs['fieldname'];
        }

        //检查是否有新怎字段
        if (!empty($post['extend_config']) && is_array($post['extend_config'])) {
            foreach ($post['extend_config'] as $field => $rs) {

                //如果已经存在则跳过
                if (in_array($field, $extendFieldLisr)) {
                    continue;
                }

                $rs['catid'] = $catid;
                if(!$rs['catid'])   return createReturn(false, '', '栏目ID不能为空！');
                if(!$rs['fieldname']) return createReturn(false, '', '键名不能为空！');
                if(!$rs['type']) return createReturn(false, '', '类型不能为空！');
                if(!$rs['fieldname']) return createReturn(false, '', '键名不能为空！');

                $data = $rs;
                $data['createtime'] = $time;
                $setting = $data['setting'];
                if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
                    $option = array();
                    $optionList = explode("\n", $setting['option']);
                    if (is_array($optionList)) {
                        foreach ($optionList as $rs) {
                            $rs = explode('|', $rs);
                            if (!empty($rs)) {
                                $option[] = array(
                                    'title' => $rs[0],
                                    'value' => $rs[1],
                                );
                            }
                        }
                        $setting['option'] = $option;
                    }
                }
                $data['setting'] = serialize($setting);
                $fieldId = $CmsCategoryFieldModel->insert($data);
                if ($fieldId) {
                    $extendFieldLisr[] = $field;
                }
            }
        }

        //值更新
        $extend = array();
        if (!empty($post['extend']) || is_array($post['extend'])) {
            foreach ($post['extend'] as $field => $value) {
                if (in_array($field, $extendFieldLisr)) {
                    $extend[$field] = $value;
                }
            }
            $info['setting']['extend'] = $extend;
        }

        //更新栏目
        $status = $this
            ->where(array('catid' => $catid))
            ->update([
                'setting' => serialize($info['setting'])
            ]);
        return $status !== false ? true : false;
    }

    /**
     * 获取所有栏目
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getCategoryTree(){
        $list = self::select();
        if(!$list->isEmpty())  $list = $list->toArray();
        $config['idKey'] = 'catid';
        return TreeHelper::getTreeShapeArray($list,0,0,$config);
    }


}