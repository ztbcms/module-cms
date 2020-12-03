<div id="app" style="padding: 8px;" v-cloak>
    <el-card>

        <div>
            <div class="h_a" style="font-weight: bold;font-size: 26px;">栏目管理</div>
        </div>

        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="210px">

                        <el-form-item label="请选择模型" prop="formtype.info.modelid" required>
                            <el-select v-model="formData.info.modelid" placeholder="请选择模型" :style="{width: '100%'}">
                                {volist name="models" id="vo"}
                                <el-option label="{$vo.name}" value="{$vo.modelid}"></el-option>
                                {/volist}
                            </el-select>
                        </el-form-item>


                        <el-form-item label="上级栏目" prop="formtype.info.parentid" required>
                            <el-select v-model="formData.info.parentid" placeholder="请选择模型" :style="{width: '100%'}">
                                <el-option label="作为一级栏目" value="0"></el-option>
                                {volist name="category" id="vo"}
                                <el-option label="{$vo.catname}" value="{$vo.modelid}"></el-option>
                                {/volist}
                            </el-select>
                        </el-form-item>

                        <el-form-item label="栏目名称" prop="formData.info.catname" required>
                            <el-input v-model="formData.info.catname" placeholder="请输入栏目名称" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="英文目录" prop="formData.info.catdir" required>
                            <el-input v-model="formData.info.catdir" placeholder="请输入英文目录" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="是否为终极栏目" prop="formData.info.child" required>
                            <el-radio v-model="formData.info.child" label="1">是</el-radio>
                            <el-radio v-model="formData.info.child" label="0">否</el-radio>
                        </el-form-item>

                        <el-form-item size="large">
                            <el-button type="primary" @click="submitForm">提交</el-button>

                            <el-button type="primary" @click="runBack">返回列表</el-button>
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
                        catid: "{$catid}",
                        info: {
                            modelid: '',
                            parentid: '0',
                            catname: '',  //栏目名称
                            catdir: '',  //英文目录
                            child: '0', //是否为终极目录
                            description: '', //栏目简介
                            ismenu: '0', //是否在导航栏中显示
                            listorder: '0', //显示排序

                        },
                        setting: {
                            listoffmoving: '1', //关闭列表动态访问
                            showoffmoving: '1', //关闭前台动态访问内容页
                            seturl: '', //指定栏目地址
                            generatehtml: '1', //是否生成内容页
                            generatelish: '0', //不生成内容页

                            member_check: '1', //前台投稿是否需要审核
                            member_admin: '1', //管理投稿 可管理未审核信息
                            member_editcheck: '1', //编辑信息是否需要审核
                            member_generatelish: '0',  //投稿是否生成列表 不生成
                            member_addpoint: '0', //投稿增加点数
                            meta_title: '', //针对搜索引擎设置的标题
                            meta_keywords: '', //栏目关键词
                            meta_description: '', //针对搜索引擎设置的网页描述

                            ishtml: '0', //栏目生成静态,
                            content_ishtml: '0', //内容页是否生成静态,

                        },
                        isbatch: '0',  //单条添加
                        category_php_ruleid: '1',  //是否生成静态目录
                        show_php_ruleid: '1', //内容页URL规则
                    },
                    show: {},
                    disabled: {},
                    setting: '',
                    rules: {},
                }
            },
            computed: {},
            watch: {},
            created: function () {
            },
            mounted: function () {
                if (this.formData.catid > 0) this.getDetails();
            },
            methods: {
                submitForm: function () {
                    var that = this;
                    var url = "{:api_url('/cms/category/details')}";
                    var data = that.formData;
                    if (that.formData.catid > 0) {
                        data.action = 'edit_submit';
                    } else {
                        data.action = 'add_submit';
                    }
                    that.httpPost(url, data, function (res) {
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
                },
                runBack: function () {
                    window.location.href = "{:api_url('/cms/Category/index')}"
                },
                getDetails: function () {
                    var that = this;
                    var url = "{:api_url('/cms/category/details')}";
                    var data = {
                        'catid': this.formData.catid
                    };
                    data.action = 'details';
                    that.httpPost(url, data, function (res) {
                        if (res.data) {
                            that.formData.info.modelid = String(res.data.modelid);
                            that.formData.info.parentid = String(res.data.parentid);
                            that.formData.info.catname = res.data.catname;
                            that.formData.info.catdir = res.data.catdir;
                            that.formData.info.child = String(res.data.child);
                        }
                    })
                }
            }
        });
    });
</script>