<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="24">

            <div style="margin-top: 10px">
                <div class="h_a" style="font-weight: bold;font-size: 26px;">模型信息</div>
                <div class="prompt_text" style="font-weight: bold;">
                    <p>模型名称: {$modelinfo['name']}</p>
                    <p>表名: {$modelinfo['tablename']}</p>
                </div>
            </div>

            <div class="filter-container">
                <el-button @click="runBack" size="small" type="primary">
                    返回列表
                </el-button>

                <el-button @click="add" size="small" type="primary">
                    添加字段
                </el-button>
                <el-button @click="showModel" size="small" type="primary">
                    预览模型
                </el-button>
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
                            <span><el-input v-model="scope.row.listorder"></el-input></span>
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
                                <el-button type="primary" size="mini"
                                           @click="edit(scope.row.modelid,scope.row.fieldid)">修改
                                </el-button>
                                <el-button :type="scope.row.disabled ? 'primary' : 'danger' " size="mini"
                                           @click="changeStatus(scope.row.fieldid,scope.row.disabled)">
                                    <span v-if="scope.row.disabled">启用</span>
                                    <span v-else>禁用</span>
                                </el-button>

                                <el-button type="danger" size="mini"
                                           @click="clickDel(scope.row.modelid,scope.row.fieldid)">删除
                                </el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="btn_wrap" style="margin-top: 20px;">
                    <div class="btn_wrap_pd">
                        <el-button type="primary" size="small" @click="listOrder">排序</el-button>
                        <el-button type="primary" size="small" @click="changeStatusItems(0)">禁用字段</el-button>
                        <el-button type="primary" size="small" @click="changeStatusItems(1)">启用字段</el-button>
                        <el-button type="primary" size="small" @click="batchDel">删除</el-button>
                    </div>
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
                                layer.msg(res.msg);
                                if (res.status) {
                                    that.fetchData();
                                }
                            }
                        })
                    });
                },


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
                // 排序批量
                listOrder: function () {
                    var that = this;
                    var formData = [];
                    this.multipleSelection.forEach(function (val, index) {
                        formData.push({
                            fieldid: val.fieldid,
                            listorder: val.listorder,
                        })
                    });
                    if (formData.length > 0) {
                        layer.confirm('确认要进行排序?', function () {
                            $.ajax({
                                url: "{:api_url('/cms/field/listOrder')}",
                                type: "post",
                                data: {
                                    data: formData
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
                            layer.msg(res.msg);
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
                edit: function (modelid, fieldid) {
                    location.href = "{:api_url('/cms/field/edit')}" + '?modelid=' + modelid + '&fieldid=' + fieldid;
                },

                // 添加字段
                add: function () {
                    location.href = "{:api_url('/cms/field/add')}" + '?modelid=' + this.modelid
                },
                // 预览模型
                showModel: function () {
                    var url = "{:api_url('/cms/field/priview')}" + '?modelid=' + this.modelid
                    Ztbcms.openNewIframeByUrl('预览模型', url);
                },

                // 返回列表
                runBack: function () {
                    window.location.href = "{:api_url('/cms/model/index')}"
                }
            },
            mounted: function () {
                this.fetchData();
            }
        })
    });
</script>
