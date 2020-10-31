<?php

namespace app\cms\fields;

// +----------------------------------------------------------------------
// | 处理信息录入表单
// +----------------------------------------------------------------------

use app\cms\model\ModelFieldModel;
use app\cms\model\ModelModel;
use app\cms\libs\util\Input;
use app\cms\libs\util\Form;

class content_form
{

    //validate表单验证
    public $formValidateRules, $formValidateMessages, $formJavascript;
    //栏目ID
    public $catid = 0;
    //栏目缓存
    public $categorys = array();
    //模型ID
    public $modelid = 0;
    //字段信息
    public $fields = array();
    //模型缓存
    protected $model = array();
    //数据
    protected $data = array();
    //最近错误信息
    protected $error = '';
    // 数据表名（不包含表前缀）
    protected $tablename = '';

    /**
     * 构造函数
     * @param type $modelid 模型ID
     * @param int $catid 栏目id
     */
    public function __construct($modelid, $catid = 0)
    {
        $this->model = ModelModel::model_cache();
        if ($modelid) {
            $this->setModelid($modelid, $catid);
        }
    }

    /**
     * 初始化
     * @param type $modelid
     * @return boolean
     */
    public function setModelid($modelid, $catid)
    {
        if (empty($modelid)) {
            return false;
        }
        $this->modelid = $modelid;
        if (empty($this->model[$this->modelid])) {
            return false;
        }
        $modelField = ModelFieldModel::model_field_cache();
        $this->catid = $catid;
        $this->fields = $modelField[$this->modelid];
        $this->tablename = trim($this->model[$this->modelid]['tablename']);
    }

    /**
     * 魔术方法，获取配置
     * @param type $name
     * @return type
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : (isset($this->$name) ? $this->$name : NULL);
    }

    /**
     *  魔术方法，设置options参数
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 获取模型字段信息
     * @param type $data
     * @return type
     */
    public function get($data = array())
    {
        $this->data = $data;
        $info = array();
        foreach ($this->fields as $fieldInfo) {
            $field = $fieldInfo['field'];
            //判断是否后台 TODO
            if (defined('IN_ADMIN') && IN_ADMIN) {
                //判断是否内部字段，如果是，跳过
                if ($fieldInfo['iscore']) {
                    continue;
                }
            } else {
                //判断是否内部字段或者，是否禁止前台投稿字段
                if ($fieldInfo['iscore']) {
                    continue;
                }
                //是否在前台投稿中显示
                if (!$fieldInfo['isadd']) {
                    continue;
                }
            }
            //字段类型
            $func = $fieldInfo['formtype'];
            //判断对应方法是否存在，不存在跳出本次循环
            if (!method_exists($this, $func)) {
                continue;
            }
            $value = isset($this->data[$field]) ? $this->data[$field] : '';
            //如果是分页类型字段
            if ($func == 'pages' && isset($this->data['maxcharperpage'])) {
                $value = $this->data['paginationtype'] . '|' . $this->data['maxcharperpage'];
            }
            //取得表单HTML代码 传入参数 字段名 字段值 字段信息
            $form = $this->$func($field, $value, $fieldInfo);
            if ($form !== false) {
                $star = $fieldInfo['minlength'] || $fieldInfo['pattern'] ? 1 : 0;
                $fieldConfg = array(
                    'name'         => $fieldInfo['name'],
                    'tips'         => $fieldInfo['tips'],
                    'form'         => $form,
                    'star'         => $star,
                    'isomnipotent' => $fieldInfo['isomnipotent'],
                    'formtype'     => $fieldInfo['formtype'],
                );
                //作为基本信息
                if ($fieldInfo['isbase']) {
                    $info['base'][$field] = $fieldConfg;
                } else {
                    $info['senior'][$field] = $fieldConfg;
                }
            }
        }

        //配合 validate 插件，生成对应的js验证规则
        $this->formValidateRules = $this->ValidateRulesJson($this->formValidateRules);
        $this->formValidateMessages = $this->ValidateRulesJson($this->formValidateMessages, true);

        return $info;
    }

    /**
     * 转换为validate表单验证相关的json数据
     * @param $ValidateRules
     * @param bool $suang
     * @return array|string
     */
    public function ValidateRulesJson($ValidateRules, $suang = false)
    {
        $formValidateRules = [];
        if (!empty($ValidateRules)) {
            foreach ($ValidateRules as $formname => $value) {
                $va = array();
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        //如果作为消息，消息内容需要加引号，不然会JS报错，是否验证不需要
                        if ($suang) {
                            $va[] = "$k:'$v'";
                        } else {
                            $va[] = "$k:$v";
                        }
                    }
                }
                $va = "{" . implode(",", $va) . "}";
                $formValidateRules[] = "'$formname':$va";
            }
        }
        $formValidateRules = "{" . implode(",", $formValidateRules) . "}";
        return $formValidateRules;
    }

    /**
     * 设置默认值
     * @param $setting
     */
    function setDefault(&$setting)
    {
        if (empty($setting['width'])) $setting['width'] = '';
        if (empty($setting['height'])) $setting['height'] = '';
        if (empty($setting['mbtoolbar'])) $setting['mbtoolbar'] = '';
        if (empty($setting['defaultvalue'])) $setting['defaultvalue'] = '';
        if (empty($setting['fieldtype'])) $setting['fieldtype'] = 'mediumtext';
        if (empty($setting['minnumber'])) $setting['minnumber'] = '';
        if (empty($setting['size'])) $setting['size'] = '';
        if (empty($setting['ispassword'])) $setting['ispassword'] = '';
        if (empty($setting['relation'])) $setting['relation'] = '';
        if (empty($setting['decimaldigits'])) $setting['decimaldigits'] = '';
        if (empty($setting['minlength'])) $setting['minlength'] = '';
    }

    /**
     * 单行文本框字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function text($field, $value, $fieldinfo)
    {
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        $size = $setting['size'] ? "style=\"width:{$setting['size']}px;\"" : '';
        if (empty($value)) {
            $value = $setting['defaultvalue'];
        }
        //文本框类型
        $type = $setting['ispassword'] ? 'password' : 'text';
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        return '<input type="' . $type . '" name="info[' . $field . ']" id="' . $field . '" ' . $size . ' value="' . $value . '" class="input" ' . $fieldinfo['formattribute'] . ' ' . $fieldinfo['css'] . '>';
    }

    /**
     * 多行文本框 表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return string
     */
    function textarea($field, $value, $fieldinfo)
    {
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        if (empty($value)) {
            $value = $setting['defaultvalue'];
        }
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        $str = "<textarea name='info[{$field}]' id='{$field}' style='width:{$setting['width']}%;height:{$setting['height']}px;' {$fieldinfo['formattribute']} {$fieldinfo['css']}";
        //长度处理
        if ($fieldinfo['maxlength']) {
            $str .= " onkeyup=\"strlen_verify(this, '{$field}_len', {$fieldinfo['maxlength']})\"";
        }
        $str .= ">{$value}</textarea>";
        if ($fieldinfo['maxlength'])
            $str .= '还可以输入<B><span id="' . $field . '_len">' . $fieldinfo['maxlength'] . '</span></B>个字符！ ';

        return $str;
    }

    /**
     * 编辑器字段 表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function editor($field, $value, $fieldinfo)
    {
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        //是否禁用分页和子标题 基本没用。。。
        $disabled_page = isset($disabled_page) ? $disabled_page : 0;
        //编辑器高度
        $height = $setting['height'];
        if (empty($setting['height'])) {
            $height = 300;
        }
        if (defined('IN_ADMIN') && IN_ADMIN) {
            //是否允许上传
            $allowupload = 1;
            //编辑器类型，简洁型还是标准型
            $toolbar = $setting['toolbar'];
        } else {
            //获取当前登录会员组id
            $groupid = cookie('groupid');
            if (isModuleInstall('Member')) {
                $Member_group = cache("Member_group");
                //是否允许上传
                $allowupload = $Member_group[$groupid]['allowattachment'] ? 1 : 0;
            } else {
                $allowupload = 0;
            }
            //编辑器类型，简洁型还是标准型
            $toolbar = $setting['mbtoolbar'] ? $setting['mbtoolbar'] : "basic";
        }

        //内容
        if (empty($value)) {
            $value = $setting['defaultvalue'] ? $setting['defaultvalue'] : '<p></p>';
        }
        if ($setting['minlength'] || $fieldinfo['pattern']) {
            $allow_empty = '';
        }
        //模块
        $module = MODULE_NAME;
        $form = Form::editor($field, $toolbar, $module, $this->catid, $allowupload, $allowupload, '', 10, $height, $disabled_page);
        //javascript
        $this->formJavascript .= "
            //增加编辑器验证规则
            jQuery.validator.addMethod('editor{$field}',function(){
                return " . ($fieldinfo['minlength'] ? "editor{$field}.getContent();" : "true") . "
            });
    ";
        //错误提示
        $errortips = $this->fields[$field]['errortips'];
        //20130428 由于没有设置必须输入时，ajax提交会造成获取不到编辑器的值。所以这里强制进行验证，使其触发编辑器的sync()方法
        // if ($minlength){
        //验证规则
        $this->formValidateRules['info[' . $field . ']'] = array("editor$field" => "true");
        //验证不通过提示
        $this->formValidateMessages['info[' . $field . ']'] = array("editor$field" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        // }
        return "<div id='{$field}_tip'></div>" . '<script type="text/plain" id="' . $field . '" name="info[' . $field . ']">' . $value . '</script>' . $form;
    }


    /**
     * 栏目字段
     * @param type $field 字段名
     * @param type $value 字段值
     * @param type $fieldinfo 该字段的配置信息
     * @return type
     */
    function catid($field, $value, $fieldinfo)
    {
        if (empty($value)) {
            //当值为空时，获取当前添加的栏目ID
            $value = $this->catid;
        }
        //后台管理员搞高级选项
        $publish_str = '';
        if (ACTION_NAME == 'add' && defined("IN_ADMIN") && IN_ADMIN) {
            $publish_str = "<a href='javascript:;' onclick=\"omnipotent('selectid','" . U("Content/Content/public_othors", array("catid" => $this->catid)) . "','同时发布到其他栏目',1);return false;\" style='color:#B5BFBB'>[同时发布到其他栏目]</a>
            <ul class='three_list cc' id='add_othors_text'></ul>";
        }
        $publish_str = '<input type="hidden" name="info[' . $field . ']" value="' . $value . '"/>' . getCategory($value, 'catname') . $publish_str;
        return $publish_str;
    }


    /**
     * 标题字段，表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return string
     */
    function title($field, $value, $fieldinfo)
    {
//        //取得标题样式
//        $style_arr = explode(';', $this->data['style']);
//        //取得标题颜色
//        $style_color = $style_arr[0];
//        //是否粗体
//        $style_font_weight = $style_arr[1] ? $style_arr[1] : '';
        //组合成CSS样式
//        $style = 'color:' . $this->data['style'];

        // 设置默认空
        $style_color = '';
        $style_font_weight = '';
        $style = 'color:#000';

        //错误提示
        $errortips = $fieldinfo['errortips'];
        //是否进行最小长度验证
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : "标题不能为空！");
        }
        $str = '<input type="text" style="width:400px;' . ($style_color ? 'color:' . $style_color . ';' : '') . ($style_font_weight ? 'font-weight:' . $style_font_weight . ';' : '') . '" name="info[' . $field . ']" id="' . $field . '" value="' . Input::forTag($value) . '" style="' . $style . '" class="input input_hd J_title_color" placeholder="请输入标题" onkeyup="strlen_verify(this, \'' . $field . '_len\', ' . $fieldinfo['maxlength'] . ')" />
                <input type="hidden" name="style_font_weight" id="style_font_weight" value="' . $style_font_weight . '">';
        //后台的情况下
        if (defined('IN_ADMIN') && IN_ADMIN)
            $str .= '<input type="button" class="btn" id="check_title_alt" value="标题检测" onclick="$.get(\'' . api_url('Content/Content/public_check_title', array('catid' => $this->catid)) . '\', {data:$(\'#title\').val()}, function(data){if(data.status==false) {$(\'#check_title_alt\').val(\'标题重复\');$(\'#check_title_alt\').css(\'background-color\',\'#FFCC66\');} else if(data.status==true) {$(\'#check_title_alt\').val(\'标题不重复\');$(\'#check_title_alt\').css(\'background-color\',\'#F8FFE1\')}},\'json\')" style="width:73px;"/>
                    <span class="color_pick J_color_pick"><em style="background:' . $style_color . ';" class="J_bg"></em></span><input type="hidden" name="style_color" id="style_color" class="J_hidden_color" value="' . $style_color . '">
                    <img src="' . DIRECTORY_SEPARATOR . 'statics/images/icon/bold.png" width="10" height="10" onclick="input_font_bold()" style="cursor:hand"/>';
        $str .= ' <span>还可输入<B><span id="title_len">' . $fieldinfo['maxlength'] . '</span></B> 个字符</span>';
        return $str;
    }

    /**
     * 选项字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function box($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        if (is_null($value) || $value == '') {
            $value = $setting['defaultvalue'];
        }
        $options = explode("\n", $setting['options']);
        foreach ($options as $_k) {
            $v = explode("|", $_k);
            $k = trim($v[1]);
            $option[$k] = $v[0];
        }
        $values = explode(',', $value);
        $value = array();
        foreach ($values as $_k) {
            if ($_k != '')
                $value[] = $_k;
        }
        $value = implode(',', $value);
        switch ($setting['boxtype']) {
            case 'radio':
                $string = Form::radio($option, $value, "name='info[$field]' {$fieldinfo['formattribute']}", $setting['width'], $field);
                break;

            case 'checkbox':
                $string = Form::checkbox($option, $value, "name='info[$field][]' {$fieldinfo['formattribute']}", 1, $setting['width'], $field);
                break;

            case 'select':
                $string = Form::select($option, $value, "name='info[$field]' id='$field' {$fieldinfo['formattribute']}");
                break;

            case 'multiple':
                $string = Form::select($option, $value, "name='info[$field][]' id='$field ' size=2 multiple='multiple' style='height:60px;' {$fieldinfo['formattribute']}");
                break;
        }
        //如果设置了关联表，显示管理按钮
        if ($setting['relation'] == 1) {
            $id = $fieldinfo['fieldid'];
            $url = U('Content/BoxField/list', ['modelid' => $fieldinfo['modelid'], 'fieldid' => $fieldinfo['fieldid']]);
            $title = '管理' . $fieldinfo['name'];
            $string .= "<span style='margin-left: 20px;'><input type='button' onClick=\"omnipotent({$id},'{$url}','{$title}')\" class='btn btn-default' value='管理" . $fieldinfo['name'] . "'></span>";
        }
        return $string;
    }

    /**
     * 图片字段表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function image($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        $width = $setting['width'] ? $setting['width'] : 180;
        $html = '';
        //图片裁减功能只在后台使用
        if (defined('IN_ADMIN') && IN_ADMIN) {
            $html = " <input type=\"button\" class=\"btn\" onclick=\"crop_cut_" . $field . "($('#$field').val());return false;\" value=\"裁减图片\">
            <input type=\"button\"  class=\"btn\" onclick=\"$('#" . $field . "_preview').attr('src','" . CONFIG_SITEURL_MODEL . "statics/images/icon/upload-pic.png');$('#" . $field . "').val('');return false;\" value=\"取消图片\"><script type=\"text/javascript\">
            function crop_cut_" . $field . "(id){
	if ( id =='' || id == undefined ) {
                      isalert('请先上传缩略图！');
                      return false;
                    }
                    var catid = $('input[name=\"info[catid]\"]').val();
                    if(catid == '' ){
                        isalert('请选择栏目ID！');
                        return false;
                    }
                    Wind.use('artDialog','iframeTools',function(){
                      art.dialog.open(GV.DIMAUB+'index.php?a=public_imagescrop&m=Content&g=Content&catid='+catid+'&picurl='+encodeURIComponent(id)+'&input=$field&preview=" . ($setting['show_type'] && defined('IN_ADMIN') ? $field . "_preview" : '') . "', {
                        title:'裁减图片',
                        id:'crop',
                        ok: function () {
                            var iframe = this.iframe.contentWindow;
                            if (!iframe.document.body) {
                                 alert('iframe还没加载完毕呢');
                                 return false;
                            }
                            iframe.uploadfile();
                            return false;
                        },
                        cancel: true
                      });
                    });
            };
</script>";
        }
        //模块
        $module = MODULE_NAME;
        //生成上传附件验证
        $authkey = upload_key("1,{$setting['upload_allowext']},{$setting['isselectimage']},{$setting['images_width']},{$setting['images_height']},{$setting['watermark']}");
        //图片模式
        if ($setting['show_type']) {
            $preview_img = $value ? $value : CONFIG_SITEURL_MODEL . 'statics/images/icon/upload-pic.png';
            return $str . "<div  style=\"text-align: center;\"><input type='hidden' name='info[$field]' id='$field' value='$value'>
			<a href='javascript:void(0);' onclick=\"flashupload('{$field}_images', '附件上传','{$field}',thumb_images,'1,{$setting['upload_allowext']},{$setting['isselectimage']},{$setting['images_width']},{$setting['images_height']},{$setting['watermark']}','{$module}','$this->catid','$authkey');return false;\">
			<img src='$preview_img' id='{$field}_preview' width='135' height='113' style='cursor:hand' /></a>
                       <br/> " . $html . "
                   </div>";
        } else {
            //文本框模式
            return $str . "<input type='text' name='info[$field]' id='$field' value='$value' style='width:{$width}px;' class='input' />  <input type='button' class='button' onclick=\"flashupload('{$field}_images', '附件上传','{$field}',submit_images,'1,{$setting['upload_allowext']},{$setting['isselectimage']},{$setting['images_width']},{$setting['images_height']},{$setting['watermark']}','{$module}','$this->catid','$authkey')\"/ value='上传图片'>" . $html;
        }
    }

    /**
     * 多图片字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段值
     * @return string
     */
    function images($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        //长度
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        //字段扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        $list_str = '';
        if ($value) {
            $value = unserialize(html_entity_decode($value, ENT_QUOTES));
            if (is_array($value)) {
                foreach ($value as $_k => $_v) {
                    $list_str .= "<div id='image_{$field}_{$_k}' style='padding:1px'><input type='text' name='{$field}_url[]' value='{$_v['url']}' style='width:310px;' ondblclick='image_priview(this.value);' class='input'> <input type='text' name='{$field}_alt[]' value='{$_v['alt']}' style='width:160px;' class='input'> <a href=\"javascript:remove_div('image_{$field}_{$_k}')\">移除</a></div>";
                }
            }
        } else {
            $list_str .= "<center><div class='onShow' id='nameTip'>您最多每次可以同时上传 <font color='red'>{$setting['upload_number']}</font> 张</div></center>";
        }
        $string = '<input name="info[' . $field . ']" type="hidden" value="1">
		<fieldset class="blue pad-10">
        <legend>图片列表</legend>';
        $string .= $list_str;
        $string .= '<div id="' . $field . '" class="picList"></div>
		</fieldset>
		<div class="bk10"></div>
		';
        //模块
        $module = MODULE_NAME;
        //生成上传附件验证
        $authkey = upload_key("{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']},,,{$setting['watermark']}");
        $string .= $str . "<a herf='javascript:void(0);' onclick=\"javascript:flashupload('{$field}_images', '图片上传','{$field}',change_images,'{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']},,,{$setting['watermark']}','{$module}','$this->catid','{$authkey}')\" class=\"btn\"><span class=\"add\"></span>选择图片 </a>";
        return $string;
    }

    /**
     * 数字字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function number($field, $value, $fieldinfo)
    {
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        $size = $setting['size'] ? "style=\"width:{$setting['size']}px;\"" : "";
        if ($value == '') {
            $value = $setting['defaultvalue'];
        }
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        return "<input type='text' name='info[{$field}]' id='{$field}' value='{$value}' class='input' {$size} {$fieldinfo['formattribute']} {$fieldinfo['css']} />";
    }

    /**
     * 日期时间字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function datetime($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        $isdatetime = 0;
        $timesystem = 0;
        //时间格式
        if ($setting['fieldtype'] == 'int') {//整数 显示格式
            if (empty($value) && $setting['defaulttype']) {
                $value = time();
            }
            //整数 显示格式
            $format_class = $setting['format'] == 'Y-m-d' ? 'J_date' : 'J_datetime';
            $format_txt = $setting['format'] == 'm-d' ? 'm-d' : $setting['format'];
            if ($setting['format'] == 'Y-m-d Ah:i:s') {
                $format_txt = 'Y-m-d h:i:s';
            }
            $value = $value ? date($format_txt, $value) : 0;
            $isdatetime = strlen($setting['format']) > 6 ? 1 : 0;
            if ($setting['format'] == 'Y-m-d Ah:i:s') {
                $timesystem = 0;
            } else {
                $timesystem = 1;
            }
        } elseif ($setting['fieldtype'] == 'datetime') {
            $isdatetime = 1;
            $timesystem = 1;
            $format_class = 'J_datetime';
        } elseif ($setting['fieldtype'] == 'datetime_a') {
            $isdatetime = 1;
            $timesystem = 0;
        } else {
            $format_class = 'J_date';
        }
        return Form::date("info[{$field}]", $value, $isdatetime, 1, 'true', $timesystem, $format_class);
    }


    /**
     * 关键字类型字段，表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function keyword($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        //字段最小长度检测
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : "请输入关键字！");
        }
        return "<input type='text' name='info[{$field}]' id='{$field}' value='" . Input::forTag($value) . "' style='width:280px' {$fieldinfo['formattribute']} {$fieldinfo['css']} class='input' placeholder='请输入关键字'>";
    }

    /**
     * Tags表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function tags($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        //最想长度验证
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : "请输入Tags标签！");
        }
        return "<input type='text' name='info[{$field}]' id='{$field}' value='{$value}' style='width:280px' {$fieldinfo['formattribute']} {$fieldinfo['css']} class='input' placeholder='请输入Tags标签'>";
    }

    /**
     * 作者字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function author($field, $value, $fieldinfo)
    {
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        //默认显示
        if ($value == '') {
            $value = $setting['defaultvalue'];
        }
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        //宽度
        $width = $setting['width'] ? ('width:' . $setting['width'] . 'px') : 'width:180px';
        return '<input type="text" class="input" name="info[' . $field . ']" value="' . Input::forTag($value) . '" style="' . $width . '" placeholder="请输入' . $fieldinfo['name'] . '信息">';
    }


    /**
     * 来源字段 表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function copyfrom($field, $value, $fieldinfo)
    {
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        if (empty($value)) {
            $value = $setting['defaultvalue'];
        }
        //错误提示
        $errortips = $fieldinfo['errortips'];
        //字段最小值判断
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        $width = $setting['width'] ? $setting['width'] : 180;
        return "<input type='text' name='info[{$field}]' value='" . Input::forTag($value) . "' style='width:{$width}px;' class='input' placeholder='信息来源'/>";
    }

    /**
     * 转向地址 字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function islink($field, $value, $fieldinfo)
    {
        if ($value) {
            $url = $this->data['url'];
            $checked = 'checked';
            $_GET['islink'] = 1;
        } else {
            $disabled = 'disabled';
            $url = $checked = '';
            $_GET['islink'] = 0;
        }
        $size = $fieldinfo['size'] ? $fieldinfo['size'] : 180;
        return '<input type="hidden" name="info[islink]" value="0"><input type="text" name="linkurl" id="linkurl" value="' . $url . '" style="width:' . $size . 'px;"maxlength="255" ' . $disabled . ' class="input length_3"> <input name="info[islink]" type="checkbox" id="islink" value="1" onclick="ruselinkurl();" ' . $checked . '> <font color="red">转向链接</font>';
    }

//模板字段
    function template($field, $value, $fieldinfo)
    {
        return Form::select_template("", 'content', $value, 'name="info[' . $field . ']" id="' . $field . '"', 'show');
    }


    /**
     * 分页选择字段类型 表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return string
     */
    function pages($field, $value, $fieldinfo)
    {
        if ($value) {
            $v = explode('|', $value);
            $data = "<select name=\"info[{$field}][paginationtype]\" id=\"paginationtype\" onchange=\"if(this.value==1)\$('#paginationtype1').css('display','');else \$('#paginationtype1').css('display','none');\">";
            $type = array(0 => "不分页", 2 => "手动分页");
            if ($v[0] == 1)
                $con = 'style="display:"';
            else
                $con = 'style="display:none"';
            foreach ($type as $i => $val) {
                if ($i == $v[0])
                    $tag = 'selected';
                else
                    $tag = '';
                $data .= "<option value=\"$i\" $tag>$val</option>";
            }
            $data .= "</select><span id=\"paginationtype1\" $con> <input name=\"info[{$field}][maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"$v[1]\" size=\"8\" maxlength=\"8\" class='input'>字符数（包含HTML标记）</span>";
            return $data;
        } else {
            return "<select name=\"info[{$field}][paginationtype]\" id=\"paginationtype\" onchange=\"if(this.value==1)\$('#paginationtype1').css('display','');else \$('#paginationtype1').css('display','none');\">
                <option value=\"0\">不分页</option>
                <option value=\"2\" selected>手动分页</option>
            </select>
	<span id=\"paginationtype1\" style=\"display:none\"><input name=\"info[{$field}][maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"10000\" size=\"8\" maxlength=\"8\" class='input'>字符数（包含HTML标记）</span>";
        }
    }

    /**
     * 类别字段类型
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function typeid($field, $value, $fieldinfo)
    {
        return (int)$value;
    }

    /**
     * 推荐字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return string
     */
    function posid($field, $value, $fieldinfo)
    {
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        //推荐位缓存
        $position = cache('Position');
        if (empty($position)) {
            return '';
        }
        $array = array();
        foreach ($position as $_key => $_value) {
            //如果有设置模型，检查是否有该模型
            if ($_value['modelid'] && !in_array($this->modelid, explode(',', $_value['modelid']))) {
                continue;
            }
            //如果设置了模型，又设置了栏目
            if ($_value['modelid'] && $_value['catid'] && !in_array($this->catid, explode(',', $_value['catid']))) {
                continue;
            }
            //如果设置了栏目
            if ($_value['catid'] && !in_array($this->catid, explode(',', $_value['catid']))) {
                continue;
            }
            $array[$_key] = $_value['name'];
        }
        $posids = array();
        if (ACTION_NAME == 'edit') {
            $result = M('PositionData')->where(array('id' => $this->id, 'modelid' => $this->modelid))->getField("posid,id,catid,posid,module,modelid,thumb,data,listorder,expiration,extention,synedit");
            $posids = implode(',', array_keys($result));
        } else {
            $posids = $setting['defaultvalue'];
        }
        return "<input type='hidden' name='info[{$field}][]' value='-1'>" . Form::checkbox($array, $posids, "name='info[{$field}][]'", '', $setting['width']);
    }


    /**
     * 单文件上传字段表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function downfile($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        //表单长度
        $width = $setting['width'] ? $setting['width'] : 300;
        //生成上传附件验证 //同时允许的上传个数, 允许上传的文件类型, 是否允许从已上传中选择
        $authkey = upload_key("1,{$setting['upload_allowext']},{$setting['isselectimage']}");
        //模块
        $module = MODULE_NAME;
        //文本框模式
        return "<input type='text' name='info[$field]' id='$field' value='$value' style='width:{$width}px;' class='input' />  <input type='button' class='button' onclick=\"flashupload('{$field}_downfile', '附件上传','{$field}',submit_attachment,'1,{$setting['upload_allowext']},{$setting['isselectimage']}','{$module}','$this->catid','$authkey')\"/ value='上传文件'>";
    }

    /**
     * 多文件上传 表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return string
     */
    function downfiles($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        $list_str = '';
        if ($value) {
            $value = unserialize(html_entity_decode($value, ENT_QUOTES));
            if (defined("IN_ADMIN") && IN_ADMIN && isModuleInstall('Member')) {
                $Member_group = cache("Member_group");
                foreach ($Member_group as $v) {
                    if (in_array($v['groupid'], array("1", "7", "8"))) {
                        continue;
                    }
                    $group[$v['groupid']] = $v['name'];
                }
            }
            if (is_array($value)) {
                foreach ($value as $_k => $_v) {
                    if (defined("IN_ADMIN") && IN_ADMIN && isModuleInstall('Member')) {
                        $list_str .= "<div id='multifile{$_k}'><input type='text' name='{$field}_fileurl[]' value='{$_v['fileurl']}' style='width:310px;' class='input'> <input type='text' name='{$field}_filename[]' value='{$_v['filename']}' style='width:160px;' class='input'> 权限：" . Form::select($group, $_v['groupid'], 'name="' . $field . '_groupid[]"', '游客') . " 点数：<input type='text' name='{$field}_point[]' value='" . $_v['point'] . "' style='width:60px;' class='input'> <a href=\"javascript:remove_div('multifile{$_k}')\">移除</a></div>";
                    } else {
                        $list_str .= "<div id='multifile{$_k}'><input type='text' name='{$field}_fileurl[]' value='{$_v['fileurl']}' style='width:310px;' class='input'> <input type='text' name='{$field}_filename[]' value='{$_v['filename']}' style='width:160px;' class='input'> <a href=\"javascript:remove_div('multifile{$_k}')\">移除</a></div>";
                    }
                }
            }
        }
        $string = '<input name="info[' . $field . ']" type="hidden" value="1">
		<fieldset class="blue pad-10">
        <legend>文件列表</legend>';
        $string .= $list_str;
        $string .= '<ul id="' . $field . '" class="picList"></ul>
		</fieldset>
		<div class="bk10"></div>
		';

        //模块
        $module = MODULE_NAME;
        //生成上传附件验证
        $authkey = upload_key("{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']}");
        //后台允许权限设置
        if (defined("IN_ADMIN") && IN_ADMIN && isModuleInstall('Member')) {
            $Member_group = cache("Member_group");
            foreach ($Member_group as $v) {
                if (in_array($v['groupid'], array("1", "7", "8"))) {
                    continue;
                }
                $group[$v['groupid']] = $v['name'];
            }
            $js = '<script type="text/javascript">
function change_multifile_admin(uploadid, returnid) {
    var d = uploadid.iframe.contentWindow;
    var in_content = d.$("#att-status").html().substring(1);
    var in_filename = d.$("#att-name").html().substring(1);
    var str = \'\';
    var contents = in_content.split(\'|\');
    var filenames = in_filename.split(\'|\');
    var group = \'权限：' . Form::select($group, $id, 'name="\' + returnid + \'_groupid[]"', '游客') . '\';
    $(\'#\' + returnid + \'_tips\').css(\'display\', \'none\');
    if (contents == \'\') return true;
    $.each(contents, function (i, n) {
        var ids = parseInt(Math.random() * 10000 + 10 * i);
        var filename = filenames[i].substr(0, filenames[i].indexOf(\'.\'));
        str += "<li id=\'multifile" + ids + "\'><input type=\'text\' name=\'" + returnid + "_fileurl[]\' value=\'" + n + "\' style=\'width:310px;\' class=\'input\'> <input type=\'text\' name=\'" + returnid + "_filename[]\' value=\'" + filename + "\' style=\'width:160px;\' class=\'input\' onfocus=\"if(this.value == this.defaultValue) this.value = \'\'\" onblur=\"if(this.value.replace(\' \',\'\') == \'\') this.value = this.defaultValue;\"> "+group+" 点数：<input type=\'text\' name=\'" + returnid + "_point[]\' value=\'0\' style=\'width:60px;\' class=\'input\'> <a href=\"javascript:remove_div(\'multifile" + ids + "\')\">移除</a> </li>";
    });
    $(\'#\' + returnid).append(str);
}

function add_multifile_admin(returnid) {
    var ids = parseInt(Math.random() * 10000);
    var group = \'权限：' . Form::select($group, $id, 'name="\' + returnid + \'_groupid[]"', '游客') . '\';
    var str = "<li id=\'multifile" + ids + "\'><input type=\'text\' name=\'" + returnid + "_fileurl[]\' value=\'\' style=\'width:310px;\' class=\'input\'> <input type=\'text\' name=\'" + returnid + "_filename[]\' value=\'附件说明\' style=\'width:160px;\' class=\'input\'> "+group+"  点数：<input type=\'text\' name=\'" + returnid + "_point[]\' value=\'0\' style=\'width:60px;\' class=\'input\'>  <a href=\"javascript:remove_div(\'multifile" + ids + "\')\">移除</a> </li>";
    $(\'#\' + returnid).append(str);
};</script>';
            $string .= $str . "<a herf='javascript:void(0);' class=\"btn\"  onclick=\"javascript:flashupload('{$field}_multifile', '附件上传','{$field}',change_multifile_admin,'{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']}','{$module}','$this->catid','{$authkey}')\"><span class=\"add\"></span>多文件上传</a>    <a  class=\"btn\" herf='javascript:void(0);'  onclick=\"add_multifile_admin('{$field}')\"><span class=\"add\"></span>添加远程地址</a>$js";
        } else {
            $string .= $str . "<a herf='javascript:void(0);'  class=\"btn\" onclick=\"javascript:flashupload('{$field}_multifile', '附件上传','{$field}',change_multifile,'{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']}','{$module}','$this->catid','{$authkey}')\"><span class=\"add\"></span>多文件上传</a>    <a herf='javascript:void(0);' class=\"btn\" onclick=\"add_multifile('{$field}')\"><span class=\"add\"></span>添加远程地址</a>";
        }
        return $string;
    }

    /**
     * 万能字段字段类型表单处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */
    function omnipotent($field, $value, $fieldinfo)
    {
        $view = \Think\Think::instance('\Think\View');
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        //特殊处理
        if (in_array($setting['fieldtype'], array('text', 'mediumtext', 'longtext'))) {
            $_value = unserialize($value);
            if ($value && $_value) {
                $value = $_value;
                $this->data[$field] = $value;
            }
        }
        $formtext = str_replace('{FIELD_VALUE}', $value, $setting["formtext"]);
        $formtext = str_replace('{MODELID}', $this->modelid, $formtext);
        $formtext = str_replace('{ID}', $this->id ? $this->id : 0, $formtext);
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        $view->assign($this->data);
        $view->display('', '', '', $formtext, '');
        // 获取并清空缓存
        $formtext = ob_get_clean();
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
        }
        return $formtext;
    }


    /**
     * 关联栏目字段类型表单组合处理
     * @param type $field 字段名
     * @param type $value 字段内容
     * @param type $fieldinfo 字段配置
     * @return type
     */

    function relationbox($field, $value, $fieldinfo)
    {
        //错误提示
        $errortips = $fieldinfo['errortips'];
        if ($fieldinfo['minlength']) {
            //验证规则
            $this->formValidateRules['info[' . $field . ']'] = ["required" => true];
            //验证不通过提示
            $this->formValidateMessages['info[' . $field . ']'] = ["required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！"];
        }
        //扩展配置
        $setting = unserialize($fieldinfo['setting']);
        $this->setDefault($setting);
        if (is_null($value) || $value == '') {
            $value = $setting['defaultvalue'];
        }
        $catid = $setting['options'];
        $catInfo = getCategory($catid);
        $model = Content\Model\ContentModel::getInstance($catInfo['modelid']);
        $records = $model->where(['status' => 99])->select();
        foreach ($records as $record) {
            $option[$record['id']] = $record[$setting['fieldkey']];
        }
        $values = explode(',', $value);
        $value = [];
        foreach ($values as $_k) {
            if ($_k != '')
                $value[] = $_k;
        }
        $value = implode(',', $value);
        switch ($setting['boxtype']) {
            case 'radio':
                $string = Form::radio($option, $value, "name='info[$field]' {$fieldinfo['formattribute']}", $setting['width'], $field);
                break;

            case 'checkbox':
                $string = Form::checkbox($option, $value, "name='info[$field][]' {$fieldinfo['formattribute']}", 1, $setting['width'], $field);
                break;

            case 'select':
                $string = Form::select($option, $value, "name='info[$field]' id='$field' {$fieldinfo['formattribute']}");
                break;

            case 'multiple':
                $string = Form::select($option, $value, "name='info[$field][]' id='$field ' size=2 multiple='multiple' style='height:60px;' {$fieldinfo['formattribute']}");
                break;
        }
        //如果设置了关联表，显示管理按钮
        if ($setting['relation'] == 1) {
            $id = $fieldinfo['fieldid'];
            $url = U('Content/BoxField/list', ['modelid' => $fieldinfo['modelid'], 'fieldid' => $fieldinfo['fieldid']]);
            $title = '管理' . $fieldinfo['name'];
            $string .= "<span style='margin-left: 20px;'><input type='button' onClick=\"omnipotent({$id},'{$url}','{$title}')\" class='btn btn-default' value='管理" . $fieldinfo['name'] . "'></span>";
        }
        return $string;
    }
}
