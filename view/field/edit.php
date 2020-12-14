<div id="app" style="padding: 8px;" v-cloak>
    <el-card>

        <div>
            <div class="h_a" style="font-weight: bold;font-size: 26px;">模型信息</div>
            <div class="prompt_text" style="font-weight: bold;">
                <p>模型名称: {$modelinfo['name']}</p>
                <p>表名: {$modelinfo['tablename']}</p>
            </div>
        </div>

        <el-col :xs="24" :md="8">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px">

                        <el-form-item label="字段属性" prop="formtype">
                            <el-select :disabled="disabled.formtype"  @change="getParameter()" v-model="formData.formtype" placeholder="请选择字段属性" :style="{width: '100%'}">
                                {volist name="all_field" id="vo"}
                                <el-option label="{$vo}" value="{$key}"></el-option>
                                {/volist}
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

                        <el-form-item size="large">
                            <el-button type="primary" :disabled="disabled.formtype" @click="submitForm">提交</el-button>
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
                        formtype : "{$isEditField}"
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

                if("{$is_disabled_formtype}" <= 0) {
                    this.disabled.formtype = false;
                } else {
                    this.disabled.formtype = true;
                }
                this.getParameter();
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
                            that.formData.issystem = String(res.data.data.issystem);
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
