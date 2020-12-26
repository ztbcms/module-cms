<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>模型属性</h3>
        <el-row>
            <el-col :span="12">
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="150px">
                        <el-form-item label="模型名称：">
                            <el-input v-model="form.name" placeholder="中文名"></el-input>
                        </el-form-item>
                        <el-form-item label="模型表名：">
                            <el-input v-model="form.table" placeholder="英文小写"></el-input>
                            <small v-if="formParam.table_prefix" class="gray">建议前缀为: {{ formParam.table_prefix }}</small>
                        </el-form-item>
                        <el-form-item label="描述：">
                            <el-input v-model="form.description"></el-input>
                        </el-form-item>

                        <el-form-item label="栏目首页模板">
                            <el-input v-model="form.category_template" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <small class="gray">模板以category_x.php形式</small>
                        </el-form-item>

                        <el-form-item label="栏目列表模板">
                            <el-input v-model="form.list_template" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <small class="gray">模板以list_x.php形式</small>
                        </el-form-item>

                        <el-form-item label="内容详情模板">
                            <el-input v-model="form.show_template" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <small class="gray">模板以show_x.php形式</small>
                        </el-form-item>

                        <el-form-item label="后台列表模板：">
                            <el-input v-model="form.list_customtemplate" placeholder="默认后台列表页，如admin_xx.php"></el-input>
                            <small class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Listtemplate/里增加文件</small>
                        </el-form-item>

                        <el-form-item label="后台添加模板：">
                            <el-input v-model="form.add_customtemplate" placeholder="默认后台列表页，如add_xx.php"></el-input>
                            <small class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Addtemplate/里增加文件</small>
                        </el-form-item>

                        <el-form-item label="后台编辑模板：">
                            <el-input v-model="form.edit_customtemplate" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <small class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Edittemplate/里增加文件</small>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">保存</el-button>
                            <el-button @click="onCancel(1)">取消</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
            <el-col :span="16">
                <div class="grid-content "></div>
            </el-col>
        </el-row>

    </el-card>
</div>

<style>
    .gray {
        color: gray;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                form: {
                    modelid: '',
                    name: '',
                    table: '',
                    description: '',
                    category_template: '',
                    list_template: '',
                    show_template: '',
                    list_customtemplate: '',
                    add_customtemplate: '',
                    edit_customtemplate: ''
                },
                formParam: {
                    table_prefix: '',
                    category_template: '',
                    list_template: '',
                    show_template: '',
                    list_customtemplate: '',
                    add_customtemplate: '',
                    edit_customtemplate: ''
                }
            },
            watch: {},
            filters: {},
            computed: {
                request_url: function () {
                    var url = "{:api_url('/cms/Model/addModel')}"
                    if (this.form.modelid) {
                        url = "{:api_url('/cms/Model/editModel')}"
                    }
                    return url;
                }
            },
            methods: {
                // 获取详情
                getDetail: function () {
                    var that = this;

                    this.httpGet(this.request_url, {modelid: this.form.modelid, '_action': 'getDetail'}, function (res) {
                        if (res.status) {
                            that.form = res.data
                        }
                    })
                },
                onSubmit: function () {
                    var that = this;
                    this.httpPost(this.request_url, this.form, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.onCancel(1000)
                        }
                    })
                },
                onCancel: function (time) {
                    if (window !== window.parent) {
                        setTimeout(function () {
                            window.parent.layer.closeAll();
                        }, time);
                    }
                },
                getFormParam: function () {
                    var that = this;
                    this.httpGet(this.request_url, {'_action': 'getFormParam'}, function (res) {
                        if (res.status) {
                            that.formParam = res.data
                            if (!that.form.modelid) {
                                that.form.table = res.data.table_prefix
                                that.form.category_template = res.data.category_template
                                that.form.list_template = res.data.list_template
                                that.form.show_template = res.data.show_template
                                that.form.list_customtemplate = res.data.list_customtemplate
                                that.form.add_customtemplate = res.data.add_customtemplate
                                that.form.edit_customtemplate = res.data.edit_customtemplate
                            }
                        }
                    })
                }
            },
            mounted: function () {
                this.form.modelid = this.getUrlQuery('modelid')
                this.getFormParam()
                if (this.form.modelid) {
                    this.getDetail()
                }
            },

        })
    })
</script>
