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
                            <el-input v-model="form.tablename" placeholder="英文小写"></el-input>
                        </el-form-item>
                        <el-form-item label="描述：">
                            <el-input v-model="form.description"></el-input>
                        </el-form-item>

                        <el-form-item label="栏目首页模板">
                            <el-input v-model="form.category_template" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <span class="gray">模板以category_x {$tmpl_template_suffix}形式</span>
                        </el-form-item>

                        <el-form-item label="栏目列表模板">
                            <el-input v-model="form.list_template" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <span class="gray">模板以list_x{$tmpl_template_suffix}形式</span>
                        </el-form-item>

                        <el-form-item label="内容详情模板">
                            <el-input v-model="form.show_template" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <span class="gray">模板以show_x{$tmpl_template_suffix}形式</span>
                        </el-form-item>

                        <el-form-item label="后台列表模板：">
                            <el-input v-model="form.list_customtemplate" placeholder="默认后台列表页，如admin_xx.php"></el-input>
                            <span class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Listtemplate/里增加文件</span>
                        </el-form-item>

                        <el-form-item label="后台添加模板：">
                            <el-input v-model="form.add_customtemplate" placeholder="默认后台列表页，如add_xx.php"></el-input>
                            <span class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Addtemplate/里增加文件</span>
                        </el-form-item>

                        <el-form-item label="后台编辑模板：">
                            <el-input v-model="form.edit_customtemplate" placeholder="默认后台列表页，如edit_xx.php"></el-input>
                            <span class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Edittemplate/里增加文件</span>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">添加</el-button>
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
                    modelid: '{:input("modelid",0)}',
                    category_template: 'category{$tmpl_template_suffix}',
                    list_template: 'list{$tmpl_template_suffix}',
                    show_template: 'show{$tmpl_template_suffix}',
                }
            },
            watch: {},
            filters: {},
            methods: {
                // 获取详情
                getDetail:function(){
                    var that = this;
                    this.httpGet("{:api_url('/cms/Model/getDetail')}", this.form, function (res) {
                        if (res.status) {
                            that.form = res.data
                        }
                    })
                },
                onSubmit: function () {
                    var that = this;
                    this.httpPost("{:api_url('/cms/Model/edit')}", this.form, function (res) {
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
            },
            mounted: function () {
                if(this.form.modelid){
                    this.getDetail()
                }
            },

        })
    })
</script>
