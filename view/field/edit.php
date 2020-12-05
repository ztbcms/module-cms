<div id="app" style="padding: 8px;" v-cloak>
    <el-card>

        <div>
            <div class="h_a" style="font-weight: bold;font-size: 26px;">模型信息</div>
            <div class="prompt_text" style="font-weight: bold;">
                <p>模型名称: {$modelinfo['name']}</p>
                <p>表名: {$modelinfo['tablename']}</p>
            </div>
        </div>

        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="210px">

                        <el-form-item label="字段属性" prop="formtype">
                            <el-select :disabled="disabled.formtype"  @change="getParameter()" v-model="formData.formtype" placeholder="请选择字段属性" :style="{width: '100%'}">
                                {volist name="all_field" id="vo"}
                                <el-option label="{$vo}" value="{$key}"></el-option>
                                {/volist}
                            </el-select>
                        </el-form-item>

                        <el-form-item label="是否作为主表" prop="issystem">
                            <el-select :disabled="disabled.issystem" v-model="formData.issystem" placeholder="请选择" :style="{width: '100%'}">
                                <el-option label="是" value="1"></el-option>
                                <el-option label="否" value="0"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="字段名" prop="field" required>
                            <el-input v-model="formData.field" placeholder="请输入字段名" clearable :style="{width: '100%'}"></el-input>
                            <span>只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾</span>
                        </el-form-item>

                        <el-form-item label="字段别名" prop="name" required>
                            <el-input v-model="formData.name" placeholder="请输入字段别名" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="字段提示" prop="tips">
                            <el-input v-model="formData.tips" placeholder="请输入字段提示" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <h3>相关参数</h3>
                        <div v-if="setting === 'text'">
                            {include file="../app/cms/fields/text/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'textarea'">
                            {include file="../app/cms/fields/textarea/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'editor'">
                            {include file="../app/cms/fields/editor/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'box'">
                            {include file="../app/cms/fields/box/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'image'">
                            {include file="../app/cms/fields/image/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'images'">
                            {include file="../app/cms/fields/images/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'number'">
                            {include file="../app/cms/fields/number/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'datetime'">
                            {include file="../app/cms/fields/datetime/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'author'">
                            {include file="../app/cms/fields/author/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'copyfrom'">
                            {include file="../app/cms/fields/copyfrom/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'islink'">
                            {include file="../app/cms/fields/islink/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'posid'">
                            {include file="../app/cms/fields/posid/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'downfile'">
                            {include file="../app/cms/fields/downfile/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'downfiles'">
                            {include file="../app/cms/fields/downfiles/field_form.inc.php"}
                        </div>
                        <div v-if="setting === 'omnipotent'">
                            {include file="../app/cms/fields/omnipotent/field_form.inc.php"}
                        </div>

                        <h3>通用参数</h3>

                        <el-form-item label="表单附加属性" prop="formattribute" v-show="show.isFormattribute">
                            <el-input v-model="formData.formattribute" placeholder="请输入表单附加属性" clearable :style="{width: '100%'}"></el-input>
                            <span>可以通过此处加入javascript事件</span>
                        </el-form-item>

                        <el-form-item label="表单样式名" prop="css" v-show="show.isCss">
                            <el-input v-model="formData.css" placeholder="请输入表单样式名" clearable :style="{width: '100%'}"></el-input>
                            <span>定义表单的CSS样式名</span>
                        </el-form-item>

                        <el-form-item label="字符长度取值范围">
                            <el-input v-model="formData.minlength" value="0" size="5" placeholder="最小值" clearable :style="{width: '100%'}" type="number"></el-input>
                            <br>
                            <br>
                            <el-input v-model="formData.maxlength" value="0" size="5"  placeholder="最大值" clearable :style="{width: '100%'}" type="number"></el-input>
                            <span>系统将在表单提交时检测数据长度范围是否符合要求，如果不想限制长度请留空</span>
                        </el-form-item>

                        <el-form-item label="数据校验正则" prop="css">

                            <el-input v-model="formData.pattern"  placeholder="数据校验正则" clearable :style="{width: '100%'}"></el-input>
                            <br>
                            <br>
                            <el-select @change="getPatternVal()" v-model="formData.pattern_select" placeholder="请选择字段属性" :style="{width: '100%'}">
                                <el-option label="常用正则" value=""></el-option>
                                <el-option label="数字" value="/^[0-9.-]+$/"></el-option>
                                <el-option label="整数" value="/^[0-9-]+$/"></el-option>
                                <el-option label="字母" value="/^[a-z]+$/i"></el-option>
                                <el-option label="数字+字母" value="/^[0-9a-z]+$/i"></el-option>
                                <el-option label="E-mail" value="/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/"></el-option>
                                <el-option label="QQ" value="/^[0-9]{5,20}$/"></el-option>
                                <el-option label="超级链接" value="/^http:\/\//"></el-option>
                                <el-option label="手机号码" value="/^(1)[0-9]{10}$/"></el-option>
                                <el-option label="电话号码" value="/^[0-9-]{6,13}$/"></el-option>
                            </el-select>

                            <span>系统将通过此正则校验表单提交的数据合法性，如果不想校验数据请留空</span>
                        </el-form-item>

                        <el-form-item label="数据校验未通过的提示信息" >
                            <el-input v-model="formData.errortips" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="后台信息处理函数" >
                            <el-input v-model="formData.setting.backstagefun" clearable :style="{width: '100%'}"></el-input>
                            <el-radio v-model="formData.setting.backstagefun_type" label="1">入库前</el-radio>
                            <el-radio v-model="formData.setting.backstagefun_type" label="2">入库后</el-radio>
                            <el-radio v-model="formData.setting.backstagefun_type" label="3">入库前后</el-radio>

                            <br>
                            <span>用法：直接填写函数名称，如果有附带参数可以在函数名后面加###参数1,参数2.完整例子：usfun###a1,a2</span>
                        </el-form-item>

                        <el-form-item label="前台信息处理函数" >
                            <el-input v-model="formData.setting.frontfun" clearable :style="{width: '100%'}"></el-input>
                            <el-radio v-model="formData.setting.frontfun_type" label="1">入库前</el-radio>
                            <el-radio v-model="formData.setting.frontfun_type" label="2">入库后</el-radio>
                            <el-radio v-model="formData.setting.frontfun_type" label="3">入库前后</el-radio>
                            <br>
                            <span>用法：直接填写函数名称，如果有附带参数可以在函数名后面加###参数1,参数2.完整例子：usfun###a1,a2</span>
                        </el-form-item>

                        <el-form-item label="值唯一" :disabled="disabled.isunique">
                            <el-radio v-model="formData.isunique" label="1">是</el-radio>
                            <el-radio v-model="formData.isunique" label="0">否</el-radio>
                        </el-form-item>

                        <el-form-item label="作为基本信息" >
                            <el-radio v-model="formData.isbase" label="1">是</el-radio>
                            <el-radio v-model="formData.isbase" label="0">否</el-radio>

                            <br>
                            <span>基本信息将在添加页面左侧显示</span>
                        </el-form-item>

                        <el-form-item label="作为搜索条件" :disabled="disabled.issearch">
                            <el-radio v-model="formData.issearch" label="1">是</el-radio>
                            <el-radio v-model="formData.issearch" label="0">否</el-radio>
                        </el-form-item>

                        <el-form-item label="在前台投稿中显示" >
                            <el-radio v-model="formData.isadd" label="1">是</el-radio>
                            <el-radio v-model="formData.isadd" label="0">否</el-radio>
                        </el-form-item>

                        <el-form-item label="作为全站搜索信息" :disabled="disabled.isfulltext">
                            <el-radio v-model="formData.isfulltext" label="1">是</el-radio>
                            <el-radio v-model="formData.isfulltext" label="0">否</el-radio>
                        </el-form-item>

                        <el-form-item label="作为万能字段的附属字段" >
                            <el-radio v-model="formData.isomnipotent" label="1">是</el-radio>
                            <el-radio v-model="formData.isomnipotent" label="0">否</el-radio>
                            <br>
                            <span>
                                必须与万能字段结合起来使用，否则内容添加的时候不会正常显示，使用时直接在使用“{当前字段名}”例如{keywords}
                            </span>
                        </el-form-item>

                        <el-form-item label="在推荐位标签中调用" >
                            <el-radio v-model="formData.isposition" label="1">是</el-radio>
                            <el-radio v-model="formData.isposition" label="0">否</el-radio>
                        </el-form-item>


                        <el-form-item size="large">
                            <el-button type="primary" :disabled="disabled.formtype" @click="submitForm">提交</el-button>
                            <el-button type="primary" @click="runBack">返回列表</el-button>
                        </el-form-item>

                        <el-form-item size="large">
                            <el-alert type="error">
                                <p>字段属性为作者，来源，转向链接，推荐位无法进行编辑</p>
                            </el-alert>
                        </el-form-item>
                    </el-form>
                </div>
            </template>
        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data: function() {
                return {
                    all_field : [],
                    formData: {
                        modelid : '{$modelid}',
                        fieldid : "{$fieldid}",
                        formtype : "{$data['formtype']}",
                        issystem : "", //是否作为主表
                        field : "", //字段名
                        name : "", //字段别名
                        tips : "", //字段提示
                        css : "",
                        formattribute  : "",
                        minlength : "",
                        maxlength : "",
                        pattern : "",
                        pattern_select : "",
                        errortips : "",
                        setting : {
                            'fieldtype' : "",
                            'backstagefun' : "",
                            'backstagefun_type' : "",
                            'frontfun' : "",
                            'frontfun_type' : "",

                            'size' : "",
                            'defaultvalue' : "",
                            'ispassword' : "",

                            'width' : "",
                            'height' : "",
                            'enablehtml' : "",

                            'toolbar' : "",
                            'enablesaveimage' : "",

                            'options' : "",
                            'boxtype' : "",
                            'minnumber' : "",
                            'maxnumber' : "",
                            'outputtype' : "",

                            'show_type' : "",
                            'upload_allowext' : "",
                            'watermark' : "",
                            'isselectimage' : "",
                            'images_width' : "",
                            'images_height' : "",
                            'upload_number' : "",

                            'decimaldigits' : "",
                            'format' : "",
                            'defaulttype' : "",

                            'statistics' : "",
                            'downloadlink' : "",
                            'formtext' : ""
                        },
                        isunique : "", //值唯一
                        isbase : "", //作为基本信息
                        issearch : "", //是否作为筛选条件
                        isadd : "", //是否在前端中显示
                        isfulltext : "", //是否作为全站搜索信息
                        isomnipotent : "", //作为万能字段
                        isposition : "" //推荐位标签中调用
                    },
                    show : {
                        isFormattribute : true,
                        isCss : true
                    },
                    disabled : {
                        issystem : false,
                        issearch :false,
                        isfulltext : false,
                        isunique : false,
                        formtype : true
                    },
                    setting : '',
                    rules: {},
                }
            },
            computed: {},
            watch: {},
            created: function() {
                //编辑的情况
                if("{$fieldid}" > 0) this.getDetails();
            },
            mounted: function() {
                this.getParameter();
                if("{$is_disabled_formtype}" <= 0) {
                    this.disabled.formtype = false;
                } else {
                    this.disabled.formtype = true;
                }


            },
            methods: {
                getPatternVal : function () {
                    this.formData.pattern = this.formData.pattern_select;
                },
                getParameter:function () {
                    var that = this;

                    that.show.isFormattribute = false;
                    that.show.isCss = false;

                    $.each(['text', 'textarea', 'box', 'number', 'keyword', 'typeid'], function (i, n) {
                        if (that.formData.fieldtype === n) {
                            that.show.isFormattribute = true;
                            that.show.isCss = true;
                        }
                    });

                    var url = "{:api_url('/cms/field/publicFieldSetting')}";
                    that.httpPost(url, {
                        fieldtype : that.formData.formtype
                    }, function(res){
                        var data = res.data;
                        if (data.field_basic_table === 1) {
                            that.disabled.issystem = false;
                        } else {
                            that.disabled.issystem = true;
                            that.formData.issystem = '0';
                        }

                        if (data.field_allow_search === 1) {
                            that.disabled.issearch = false;
                        } else {
                            that.disabled.issearch = true;
                            that.formData.issearch = '0';
                        }

                        if (data.field_allow_fulltext === 1) {
                            that.disabled.isfulltext = false;
                        } else {
                            that.disabled.isfulltext = true;
                            that.formData.isfulltext = '0';
                        }

                        if (data.field_allow_isunique === 1) {
                            that.disabled.isunique = false;
                        } else {
                            that.disabled.isunique = true;
                            that.formData.isunique = '0';
                        }
                        that.formData.field_minlength = data.field_minlength;
                        that.formData.field_maxlength = data.field_maxlength;
                        that.setting = that.formData.formtype;
                    });
                },
                submitForm: function() {
                    var that = this;
                    that.$refs['elForm'].validate(function(valid){
                        if (!valid) return;
                        var url = "{:api_url('/cms/field/edit')}";
                        that.httpPost(url, that.formData, function(res){
                            layer.msg(res.msg);
                            if (res.status) {
                                //添加成功
                                if (window !== window.parent) {
                                    setTimeout(function () {
                                        location.href = res.url;
                                    }, 1000);
                                }
                            }
                        })
                    })
                },
                runBack :function () {
                    window.location.href = "{:api_url('/cms/Field/index')}?modelid=" + this.formData.modelid
                },
                getDetails : function () {
                    var that = this;
                    var url = "{:api_url('/cms/field/getFieldDetails')}";
                    that.httpGet(url, {
                        modelid : "{$modelid}",
                        fieldid : "{$fieldid}"
                    }, function(res){
                        if (res.status) {
                            that.formData.modelid = '{$modelid}';
                            that.formData.fieldid = "{$fieldid}";
                            that.formData.formtype = res.data.data.formtype;
                            that.formData.issystem = res.data.data.issystem;
                            that.formData.field = String(res.data.data.field);
                            that.formData.name = res.data.data.name;
                            that.formData.tips = res.data.data.tips;
                            that.formData.css = res.data.data.css;
                            that.formData.formattribute = res.data.data.formattribute;
                            that.formData.minlength = res.data.data.minlength;
                            that.formData.maxlength = res.data.data.maxlength;
                            that.formData.pattern = res.data.data.pattern;
                            that.formData.pattern_select = res.data.data.pattern_select;
                            that.formData.errortips = res.data.data.errortips;
                            that.formData.isunique = String(res.data.data.isunique);
                            that.formData.isbase = String(res.data.data.isbase);
                            that.formData.issearch = String(res.data.data.issearch);
                            that.formData.isadd = String(res.data.data.isadd);
                            that.formData.isfulltext = String(res.data.data.isfulltext);
                            that.formData.isomnipotent = String(res.data.data.isomnipotent);
                            that.formData.isposition = String(res.data.data.isposition);
                            that.formData.setting.fieldtype = res.data.setting.fieldtype;
                            that.formData.setting.backstagefun = res.data.setting.backstagefun;
                            that.formData.setting.backstagefun_type = String(res.data.setting.backstagefun_type);
                            that.formData.setting.frontfun = res.data.setting.frontfun;
                            that.formData.setting.frontfun_type = String(res.data.setting.frontfun_type);
                            that.formData.setting.size = res.data.setting.size;
                            that.formData.setting.defaultvalue = res.data.setting.defaultvalue;
                            that.formData.setting.ispassword = res.data.setting.ispassword;
                            that.formData.setting.width = res.data.setting.width;
                            that.formData.setting.height = res.data.setting.height;
                            that.formData.setting.enablehtml = res.data.setting.enablehtml;
                            that.formData.setting.toolbar = res.data.setting.toolbar;
                            that.formData.setting.enablesaveimage = res.data.setting.enablesaveimage;
                            that.formData.setting.options = res.data.setting.options;
                            that.formData.setting.boxtype = res.data.setting.boxtype;
                            that.formData.setting.width = res.data.setting.width;
                            that.formData.setting.minnumber = res.data.setting.minnumber;
                            that.formData.setting.maxnumber = res.data.setting.maxnumber;
                            that.formData.setting.outputtype = res.data.setting.outputtype;
                            that.formData.setting.show_type = res.data.setting.show_type;
                            that.formData.setting.upload_allowext = res.data.setting.upload_allowext;
                            that.formData.setting.watermark = res.data.setting.watermark;
                            that.formData.setting.isselectimage = res.data.setting.isselectimage;
                            that.formData.setting.images_width = res.data.setting.images_width;
                            that.formData.setting.images_height = res.data.setting.images_height;
                            that.formData.setting.upload_number = res.data.setting.upload_number;
                            that.formData.setting.decimaldigits = res.data.setting.decimaldigits;
                            that.formData.setting.format = res.data.setting.format;
                            that.formData.setting.defaulttype = res.data.setting.defaulttype;
                            that.formData.setting.statistics = res.data.setting.statistics;
                            that.formData.setting.downloadlink = res.data.setting.downloadlink;
                            that.formData.setting.formtext = res.data.setting.formtext;
                        }
                    });
                }
            }
        });
    });
</script>
