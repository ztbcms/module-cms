<div id="app" style="padding: 8px;" v-cloak>
    <el-card v-loading="finishLoadAmount < maxLoadAmount">

        <div>
            <div class="h_a" style="font-weight: bold;font-size: 26px;">模型信息</div>
            <div class="prompt_text" style="font-weight: bold;">
                <p>模型名称: {{model_info.name}}</p>
                <p>表名: {{model_info.table}}</p>
            </div>
        </div>

        <el-col :xs="24" :md="8">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData"  size="medium" label-width="100px">

                        <el-form-item label="字段属性" prop="form_type">
                            <el-select v-model="formData.form_type" placeholder="请选择字段属性" @change="changeFormtype" >
                                <template v-for="item in form_type_list">
                                    <el-option :label="item.name" :value="item.type"></el-option>
                                </template>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="字段名"  required>
                            <el-input v-model="formData.field" placeholder="请输入字段名" clearable></el-input>
                            <small>只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾</small>
                        </el-form-item>

                        <el-form-item label="字段别名" required>
                            <el-input v-model="formData.name" placeholder="请输入字段别名" clearable></el-input>
                        </el-form-item>

                        <el-form-item label="字段提示">
                            <el-input v-model="formData.tips" placeholder="请输入字段提示" clearable></el-input>
                        </el-form-item>

                        <el-form-item label="字符长度">
                            <el-input v-model="formData.field_length" value="0" size="5" placeholder="" clearable  type="number"></el-input>
                            <small>数据库中的字段长度</small>
                        </el-form-item>

                        <el-form-item label="编辑页展示">
                            <el-radio-group v-model="formData.enable_edit_show">
                                <el-radio label="0">隐藏</el-radio>
                                <el-radio label="1">展示</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item label="列表页展示">
                            <el-radio-group v-model="formData.enable_list_show">
                                <el-radio label="0">隐藏</el-radio>
                                <el-radio label="1">展示</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <h3>属性相关参数</h3>

                        <div v-if="formData.form_type === 'text'">
                            <field-setting-text v-model="formData.setting"></field-setting-text>
                        </div>

                        <div v-if="formData.form_type === 'textarea'">
                            <field-setting-textarea v-model="formData.setting"></field-setting-textarea>
                        </div>

                        <div v-if="formData.form_type === 'editor'">
                            <field-setting-editor v-model="formData.setting"></field-setting-editor>
                        </div>

                        <div v-if="formData.form_type === 'number'">
                            <field-setting-number v-model="formData.setting"></field-setting-number>
                        </div>

                        <div v-if="formData.form_type === 'image'">
                            <field-setting-image v-model="formData.setting"></field-setting-image>
                        </div>

                        <div v-if="formData.form_type === 'images'">
                            <field-setting-images v-model="formData.setting"></field-setting-images>
                        </div>

                        <div v-if="formData.form_type === 'video'">
                            <field-setting-video v-model="formData.setting"></field-setting-video>
                        </div>

                        <div v-if="formData.form_type === 'videos'">
                            <field-setting-videos v-model="formData.setting"></field-setting-videos>
                        </div>

                        <div v-if="formData.form_type === 'file'">
                            <field-setting-file v-model="formData.setting"></field-setting-file>
                        </div>

                        <div v-if="formData.form_type === 'files'">
                            <field-setting-files v-model="formData.setting"></field-setting-files>
                        </div>

                        <div v-if="formData.form_type === 'radio'">
                            <field-setting-radio v-model="formData.setting"></field-setting-radio>
                        </div>

                        <div v-if="formData.form_type === 'checkbox'">
                            <field-setting-checkbox v-model="formData.setting"></field-setting-checkbox>
                        </div>

                        <div v-if="formData.form_type === 'select'">
                            <field-setting-select v-model="formData.setting"></field-setting-select>
                        </div>

                        <div v-if="formData.form_type === 'datetime'">
                            <field-setting-datetime v-model="formData.setting"></field-setting-datetime>
                        </div>

                        <el-form-item size="large">
                            <el-button type="primary"  @click="submitForm">提交</el-button>
                        </el-form-item>

                    </el-form>
                </div>
            </template>
        </el-col>
    </el-card>
</div>

<!--text-->
{include file="../app/cms/view/common/fields/text/field_setting.inc.php"}
<!--textarea-->
{include file="../app/cms/view/common/fields/textarea/field_setting.inc.php"}
<!--editor-->
{include file="../app/cms/view/common/fields/editor/field_setting.inc.php"}
<!--number-->
{include file="../app/cms/view/common/fields/number/field_setting.inc.php"}
<!--image-->
{include file="../app/cms/view/common/fields/image/field_setting.inc.php"}
<!--images-->
{include file="../app/cms/view/common/fields/images/field_setting.inc.php"}
<!--video-->
{include file="../app/cms/view/common/fields/video/field_setting.inc.php"}
<!--videos-->
{include file="../app/cms/view/common/fields/videos/field_setting.inc.php"}
<!--file-->
{include file="../app/cms/view/common/fields/file/field_setting.inc.php"}
<!--files-->
{include file="../app/cms/view/common/fields/files/field_setting.inc.php"}
<!--radio-->
{include file="../app/cms/view/common/fields/radio/field_setting.inc.php"}
<!--checkbox-->
{include file="../app/cms/view/common/fields/checkbox/field_setting.inc.php"}
<!--select-->
{include file="../app/cms/view/common/fields/select/field_setting.inc.php"}
<!--datetime-->
{include file="../app/cms/view/common/fields/datetime/field_setting.inc.php"}

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            components: {},
            props: [],
            data: function() {
                return {
                    all_field : [],
                    formData: {
                        modelid : '',
                        fieldid : "",
                        form_type : "",
                        field_length: 0,
                        field : "", //字段名
                        name : "", //字段别名
                        tips : "", //字段提示
                        enable_edit_show: '1',
                        enable_list_show: '0',
                        setting: {}
                    },
                    model_info: {
                        name: '',
                        table: '',
                    },
                    // 字段类型列表
                    form_type_list: [],
                    show : {
                        isFormattribute : true,
                        isCss : true
                    },
                    rules: {},
                    // 已加载数量
                    finishLoadAmount: 0,
                    // 最大需要加载量
                    maxLoadAmount: 1,
                }
            },
            watch: {},
            created: function() { },
            computed: {
              request_url: function(){
                  if(this.formData.fieldid){
                     return "{:api_url('/cms/field/editField')}"
                  }
                  return "{:api_url('/cms/field/addField')}"
              }
            },
            mounted: function() {
                this.formData.modelid =  this.getUrlQuery('modelid') || ''
                this.formData.fieldid =  this.getUrlQuery('fieldid') || ''

                this.getFormPrams()

                if(this.formData.fieldid){
                    this.maxLoadAmount = 2
                    this.getDetail()
                }
            },
            methods: {
                getPatternVal: function () {
                    this.formData.pattern = this.formData.pattern_select;
                },
                submitForm: function () {
                    var that = this
                    console.log(that.formData)
                    that.$refs['elForm'].validate(function (valid) {
                        if (!valid) return;
                        that.httpPost(this.request_url, that.formData, function (res) {
                            layer.msg(res.msg);
                            if (res.status) {
                                //添加成功
                                if (window !== window.parent && window.parent.layer) {
                                    setTimeout(function () {
                                        window.parent.layer.closeAll()
                                    }, 1000);
                                }
                            }
                        })
                    })
                },
                // 获取表单参数
                getFormPrams: function () {
                    var that = this;
                    that.httpGet(this.request_url, {_action: "getFormParam", modelid: this.formData.modelid}, function (res) {
                        if (res.status) {
                            that.form_type_list = res.data.form_type
                            that.model_info = res.data.model_info
                        }
                        that.finishLoadAmount++
                    });
                },
                getDetail: function () {
                    var that = this;
                    that.httpGet(this.request_url, {
                        fieldid: this.formData.fieldid,
                        _action: 'getDetail'
                    }, function (res) {
                        if (res.status) {
                            that.formData.modelid = res.data.field_info.modelid
                            that.formData.fieldid = res.data.field_info.fieldid
                            that.formData.name = res.data.field_info.name || ''
                            that.formData.form_type = String(res.data.field_info.form_type)
                            that.formData.field_length= res.data.field_info.field_length || 0
                            that.formData.field = String(res.data.field_info.field)
                            that.formData.tips = res.data.field_info.tips || ''
                            that.formData.setting = res.data.field_info.setting || {}
                            that.formData.enable_edit_show = String(res.data.field_info.enable_edit_show)
                            that.formData.enable_list_show = String(res.data.field_info.enable_list_show)

                            that.finishLoadAmount++
                        }
                    });
                },
                changeFormtype: function (type){
                    for (var i=0;i<this.form_type_list.length;i++){
                        if(this.form_type_list[i]['type'] == type){
                            this.formData.field_length = this.form_type_list[i]['length']
                            return
                        }
                    }
                }
            }
        });
    });
</script>
