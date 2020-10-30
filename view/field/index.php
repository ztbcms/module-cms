<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="24">
            <div class="filter-container">
                <el-button @click="add" size="small" type="primary">
                    添加字段
                </el-button>
                <el-button @click="showModel" size="small" type="primary">
                    预览模型
                </el-button>
            </div>

            <div style="margin-top: 10px">
                <div class="h_a">模型信息</div>
                <div class="prompt_text">
                    <p>名称: {$modelinfo['name']}</p>
                    <p>表名: {$modelinfo['tablename']}</p>
                </div>
            </div>

            <div style="margin-bottom: 80px;">
                <el-table
                    :data="fieldsList"
                    tooltip-effect="dark"
                    style="width: 100%"
                    @selection-change="handleSelectionChange">

                    <el-table-column
                        type="selection"
                        width="55"
                        label="全选">
                    </el-table-column>

                    <el-table-column label="排序" align="center">
                        <template slot-scope="scope">
                            <span>{{ scope.row.listorder }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="字段名" align="center">
                        <template slot-scope="scope">
                            <span>{{ scope.row.field }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="别名" align="center">
                        <template slot-scope="scope">
                            <span>{{ scope.row.name }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="字段类型" align="center">
                        <template slot-scope="scope">
                            <span>{{ scope.row.formtype }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="主表" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.issystem == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="必填" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.minlength == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="搜索" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.issearch == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="排序" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.isorder == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="投稿" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.isadd == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="基本信息" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.isbase == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="管理操作" align="center" width="250" class-name="small-padding fixed-width">
                        <template slot-scope="scope">
                            <?php if (\app\admin\libs\system\Rbac::ableAccess('cms', 'field', 'edit')): ?>
                                <el-button type="primary" size="mini"
                                           @click="edit(scope.row.modelid,scope.row.fieldid)">修改
                                </el-button>
                            <?php endif; ?>

                            <?php if (\app\admin\libs\system\Rbac::ableAccess('cms', 'field', 'disabled')): ?>
                                <el-button :type="scope.row.disabled ? 'primary' : 'danger' " size="mini"
                                           @click="changeStatus(scope.row.fieldid,scope.row.disabled)">
                                    <span v-if="scope.row.disabled">启用</span>
                                    <span v-else>禁用</span>
                                </el-button>
                            <?php endif; ?>

                            <?php if (\app\admin\libs\system\Rbac::ableAccess('cms', 'field', 'disabled')): ?>
                                <el-button type="danger" size="mini"
                                           @click="clickDel(scope.row.modelid,scope.row.fieldid)">删除
                                </el-button>
                            <?php endif; ?>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <div class="btn_wrap" style="margin-bottom: 10px;">
                <div class="btn_wrap_pd">
                    <el-button type="primary" size="small" @click="listOrder">排序</el-button>
                    <el-button type="primary" size="small" @click="changeStatusItems(0)">禁用字段</el-button>
                    <el-button type="primary" size="small" @click="changeStatusItems(1)">启用字段</el-button>
                    <el-button type="primary" size="small" @click="batchDel">删除</el-button>
                </div>
            </div>
        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        var App = new Vue({
            el: '#app',
            data: {
                tabSelected: "first",
                modelid: "{:input('modelid')}",
                multipleSelection: [],
                fieldsList: []
            },
            computed: {},
            methods: {
                // 全选
                handleSelectionChange: function (val) {
                    this.multipleSelection = val;
                    console.log(this.multipleSelection)
                },
                // 获取数据
                fetchData: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('cms/field/getFieldData')}",
                        type: "get",
                        data: {
                            modelid: "{:input('modelid')}"
                        },
                        dataType: "json",
                        success: function (res) {
                            if (res.status) {
                                that.fieldsList = res.data;
                            } else {
                                layer.msg('操作繁忙，请稍后再试')
                            }
                        }
                    })
                },
                // 删除字段
                clickDel: function (modelid, fieldid) {
                    var that = this;
                    layer.confirm('确认要删除?', function () {
                        $.ajax({
                            url: "{:api_url('/cms/field/delFields')}",
                            type: "post",
                            data: {
                                modelid: modelid,
                                fieldid: [fieldid],
                            },
                            dataType: "json",
                            success: function (res) {
                                layer.msg(res.msg)
                                if (res.status) {
                                    that.fetchData();
                                }
                            }
                        })
                    });
                },
                // 批量删除字段
                batchDel: function () {
                    var that = this;
                    var fieldids = [];
                    this.multipleSelection.forEach(function (val, index) {
                        fieldids.push(val.fieldid)
                    });
                    if (fieldids.length > 0) {
                        layer.confirm('确认要删除?', function () {
                            $.ajax({
                                url: "{:api_url('/cms/field/delFields')}",
                                type: "post",
                                data: {
                                    modelid: that.modelid,
                                    fieldid: fieldids,
                                },
                                dataType: "json",
                                success: function (res) {
                                    layer.msg(res.msg)
                                    if (res.status) {
                                        that.fetchData();
                                    }
                                }
                            })
                        });
                    } else {
                        layer.msg('请选择')
                    }
                },
                // 启用/禁用
                changeStatus: function (fieldid, disabled) {
                    var that = this
                    $.ajax({
                        url: "{:api_url('/cms/field/disabled')}",
                        type: "post",
                        data: {
                            fieldid: [fieldid],
                            disabled: disabled
                        },
                        dataType: "json",
                        success: function (res) {
                            layer.msg(res.msg)
                            if (res.status) {
                                that.fetchData();
                            }
                        }
                    })
                },

                // 批量启用/禁用
                changeStatusItems: function (disabled) {
                    var that = this;
                    var fieldids = [];
                    this.multipleSelection.forEach(function (val, index) {
                        fieldids.push(val.fieldid)
                    });
                    if (fieldids.length > 0) {
                        $.ajax({
                            url: "{:api_url('/cms/field/disabled')}",
                            type: "post",
                            data: {
                                fieldid: fieldids,
                                disabled: disabled
                            },
                            dataType: "json",
                            success: function (res) {
                                layer.msg(res.msg)
                                if (res.status) {
                                    that.fetchData();
                                }
                            }
                        })
                    } else {
                        layer.msg('请选择')
                    }
                },
                // 编辑字段
                edit: function (modelid,fieldid) {
                    var that = this
                    var url = "{:api_url('/cms/field/edit')}" + '?modelid=' + modelid + '&fieldid=' + fieldid;
                    location.href = url
                },


                // 添加字段
                add: function () {
                    var that = this
                    layer.open({
                        type: 2,
                        title: '添加',
                        content: "{:api_url('/cms/model/add')}",
                        area: ['100%', '100%'],
                        end: function () {  //回调函数
                            that.fetchData()
                        }
                    })
                },
                // 预览模型
                showModel: function () {
                    window.location.href = "{:api_url('/cms/FieldExport/exportTableFields')}"
                },


                // 导出模型 下载
                exportModel: function (modelid) {
                    var url = "{:api_url('/cms/model/export')}" + '?modelid=' + modelid
                    window.open(url)
                }
            },
            mounted: function () {
                this.fetchData();
            }
        })
    });
</script>
