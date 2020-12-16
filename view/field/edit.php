<div id="app" style="padding: 8px;" v-cloak>
    <el-card v-loading="finishLoadAmount < maxLoadAmount">

        <div>
            <div class="h_a" style="font-weight: bold;font-size: 26px;">模型信息</div>
            <div class="prompt_text" style="font-weight: bold;">
                <p>模型名称: {{model_info.name}}</p>
                <p>表名: {{model_info.tablename}}</p>
            </div>
        </div>

        <el-col :xs="24" :md="8">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px">

                        <el-form-item label="字段属性" prop="formtype">
                            <el-select v-model="formData.formtype" placeholder="请选择字段属性" @change="changeFormtype" >
                                <template v-for="item in field_type_list">
                                    <el-option :label="item.name" :value="item.type"></el-option>
                                </template>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="字段名" prop="field" required>
                            <el-input v-model="formData.field" placeholder="请输入字段名" clearable></el-input>
                            <small>只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾</small>
                        </el-form-item>

                        <el-form-item label="字段别名" prop="name" required>
                            <el-input v-model="formData.name" placeholder="请输入字段别名" clearable></el-input>
                        </el-form-item>

                        <el-form-item label="字段提示" prop="tips">
                            <el-input v-model="formData.tips" placeholder="请输入字段提示" clearable></el-input>
                        </el-form-item>


                        <el-form-item label="字符长度">
                            <el-input v-model="formData.length" value="0" size="5" placeholder="" clearable  type="number"></el-input>
                            <small>数据库中的字段长度</small>
                        </el-form-item>

                        <el-form-item size="large">
                            <el-button type="primary"  @click="submitForm">提交</el-button>
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
            components: {},
            props: [],
            data: function() {
                return {
                    all_field : [],
                    formData: {
                        modelid : '',
                        fieldid : "",
                        formtype : "",
                        field : "", //字段名
                        name : "", //字段别名
                        tips : "", //字段提示
                        length: 0
                    },
                    model_info: {
                        name: '',
                        tablename: '',
                    },
                    // 字段类型列表
                    field_type_list: [],
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
            computed: {},
            watch: {},
            created: function() { },
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
                    return
                    that.$refs['elForm'].validate(function (valid) {
                        if (!valid) return;
                        var url = "{:api_url('/cms/field/edit')}";
                        that.httpPost(url, that.formData, function (res) {
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
                // 获取表单参数
                getFormPrams: function () {
                    var that = this;
                    var url = "{:api_url('/cms/field/add')}"
                    if (this.formData.fieldid) {
                        url = "{:api_url('/cms/field/edit')}";
                    }
                    that.httpGet(url, {_action: "getFormParam",}, function (res) {
                        if (res.status) {
                            that.field_type_list = res.data.field_type
                        }
                        that.finishLoadAmount++
                    });
                },
                getDetail: function () {
                    var that = this;
                    var url = "{:api_url('/cms/field/edit')}";
                    that.httpGet(url, {
                        modelid: this.formData.modelid,
                        fieldid: this.formData.fieldid,
                        _action: 'getDetail'
                    }, function (res) {
                        if (res.status) {
                            that.formData.modelid = res.data.field_info.modelid
                            that.formData.fieldid = res.data.field_info.fieldid
                            that.formData.field = String(res.data.field_info.field)
                            that.formData.name = res.data.field_info.name || ''
                            that.formData.tips = res.data.field_info.tips || ''
                            that.formData.length = res.data.field_info.length || 0
                            that.model_info = res.data.model_info
                            that.finishLoadAmount++
                        }
                    });
                },
                changeFormtype: function (type){
                    for (var i=0;i<this.field_type_list.length;i++){
                        if(this.field_type_list[i]['type'] == type){
                            this.formData.length = this.field_type_list[i]['length']
                            return
                        }
                    }
                }
            }
        });
    });
</script>
