<div id="app" style="padding: 8px;" v-cloak>
    <el-card>

        <h3>栏目编辑</h3>

        <el-col :sm="24" :md="9">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" size="medium" label-width="110px">

                        <el-form-item label="栏目模型"  required>
                            <el-select v-model="formData.modelid" placeholder="请选择模型" :style="{width: '100%'}">

                                <el-option v-for="item in modelList"
                                           :id="item.modelid"
                                           :label="item.name"
                                           :value="item.modelid"></el-option>

                            </el-select>
                        </el-form-item>

                        <el-form-item label="上级栏目" required>
                            <el-select v-model="formData.parentid" placeholder="请选择模型" :style="{width: '100%'}">
                                <el-option label="作为一级栏目" value="0"></el-option>
                                <el-option
                                        v-for="item in categoryList"
                                        :key="item.id"
                                        :label="item.catname"
                                        :value="item.catid">
                                    <template v-for="i in item.level * 2"><span>&nbsp;</span></template>
                                    <template v-if="item.level > 0"><span> ∟</span></template>
                                    <span>{{ item.catname }}</span>
                                </el-option>

                            </el-select>
                        </el-form-item>

                        <el-form-item label="栏目名称"  required>
                            <el-input v-model="formData.catname" placeholder="请输入栏目名称" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="英文目录" required>
                            <el-input v-model="formData.catdir" placeholder="请输入英文目录" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="类型" required>
                            <el-radio v-model="formData.type" label="0">内容栏目</el-radio>
                            <el-radio v-model="formData.type" label="1">栏目组</el-radio>
                            <el-radio v-model="formData.type" label="2">外部链接</el-radio>
                        </el-form-item>

                        <el-form-item label="后台列表模板：">
                            <el-input v-model="formData.list_customtemplate" placeholder="默认后台列表页，如admin_xx.php"></el-input>
                            <small class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Listtemplate/里增加文件</small>
                        </el-form-item>

                        <el-form-item label="后台添加模板：">
                            <el-input v-model="formData.add_customtemplate" placeholder="默认后台列表页，如add_xx.php"></el-input>
                            <small class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Addtemplate/里增加文件</small>
                        </el-form-item>

                        <el-form-item label="后台编辑模板：">
                            <el-input v-model="formData.edit_customtemplate" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <small class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Edittemplate/里增加文件</small>
                        </el-form-item>

                        <el-form-item size="large">
                            <el-button type="primary" @click="submitForm">提交</el-button>
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
            data: function () {
                return {
                    all_field: [],
                    formData: {
                        catid: "",
                        modelid: '',
                        parentid: '0',
                        catname: '',  //栏目名称
                        catdir: '',  //英文目录
                        type: '0', //是否为终极目录
                        description: '', //栏目简介
                        listorder: '0', //显示排序
                        setting: {
                            meta_title: '', //针对搜索引擎设置的标题
                            meta_keywords: '', //栏目关键词
                            meta_description: '', //针对搜索引擎设置的网页描述
                        },
                        add_customtemplate: '',//后台添加页
                        edit_customtemplate: '',// 后台编辑页
                        list_customtemplate: '',// 后台列表页
                        category_template: '',
                        list_template: '',
                        show_template: '',
                    },
                    show: {},
                    disabled: {},
                    setting: '',
                    rules: {},
                    // 栏目树状列表
                    categoryList: [],
                    // 模型列表
                    modelList: []
                }
            },
            computed: {
                request_url: function() {
                    if(this.formData.catid){
                        return "{:api_url('/cms/category/editCategory')}";
                    }
                    return "{:api_url('/cms/category/addCategory')}";
                }
            },
            watch: {},
            created: function () {
            },
            mounted: function () {
                this.getFormParam()
                if (this.formData.catid > 0) this.getDetails();
            },
            methods: {
                submitForm: function () {
                    var that = this;
                    that.httpPost(this.request_url, this.formData, function (res) {
                        layer.msg(res.msg);
                        if (res.status) {
                            //添加成功
                            if (window !== window.parent) {
                                setTimeout(function () {
                                    window.parent.layer.closeAll()
                                }, 1000);
                            }
                        }
                    })
                },
                // 详情
                getDetails: function () {
                    var that = this;
                    var data = {
                        'catid': this.formData.catid
                    };
                    data._action = 'getDetail';
                    that.httpPost(this.request_url, data, function (res) {
                        if (res.data) {
                            that.formData.modelid = res.data.modelid ;
                            that.formData.parentid = String(res.data.parentid);
                            that.formData.catname = res.data.catname;
                        }
                    })
                },
                // 表单参数
                getFormParam: function () {
                    var that = this;
                    var data = {
                        _action: 'getFormParam'
                    };
                    that.httpGet(this.request_url, data, function (res) {
                        if (res.data) {
                            that.categoryList = res.data.categoryList
                            that.modelList = res.data.modelList
                        }
                    })
                }
            }
        });
    });
</script>