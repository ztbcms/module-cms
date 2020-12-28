<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="24">
            <div id="app" style="padding-left: 20px;padding-top: 20px;" v-cloak>
                <div class="filter-container" style="margin-bottom: 20px;">

                    <el-button @click="add" size="small" type="primary">
                        创建新模型
                    </el-button>
                    <el-button @click="importData" size="small" type="primary">
                        模型导入
                    </el-button>

                    <el-button @click="searchField" size="small" type="primary">
                        手动填写表名导出字典
                    </el-button>
                    <el-button @click="exportAllField" size="small" type="primary">
                        导出全部模型数据字典
                    </el-button>


                    <el-alert style="margin-top: 20px;"
                            type="warning">
                        <p>使用提示 ：</p>
                        <p>1.当模块禁用的时候无法对字段管理进行删除等操作</p>
                    </el-alert>
                </div>

                <div>
                    <el-table
                        style="margin-bottom: 30px;"
                        :data="modelsList"
                        highlight-current-row
                        style="width: 100%;"
                    >
                        <el-table-column label="Model ID" align="center">
                            <template slot-scope="scope">
                                <span>{{ scope.row.modelid }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="模型名称" align="center">
                            <template slot-scope="scope">
                                <span>{{ scope.row.name }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="数据表" align="center">
                            <template slot-scope="scope">
                                <span>{{ scope.row.table }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="描述" align="center">
                            <template slot-scope="scope">
                                <span>{{ scope.row.description }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="模块状态" width="" align="center">
                            <template slot-scope="{row}">
                                <span v-if="row.disabled == '1'">
                                    <i @click="changeStatus(row.modelid,row.disabled)" class="el-icon-error" style="color: red;font-size: 24px;"></i>
                                </span>
                                <span v-else>
                                    <i @click="changeStatus(row.modelid,row.disabled)" class="el-icon-success" style="color: green;font-size: 24px;"></i>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="管理操作" align="center" width="400" class-name="small-padding fixed-width">
                            <template slot-scope="scope">

                                <el-button type="text" size="mini" @click="edit(scope.row.modelid)">修改</el-button>

                                <el-button type="text" size="mini" @click="modelField(scope.row.modelid)">字段管理</el-button>

                                <el-button type="text" style="" size="mini" @click="exportDictionary(scope.row.modelid)">导出数据字典
                                </el-button>

                                <el-button type="text" size="mini" @click="exportModel(scope.row.modelid)">导出模型
                                </el-button>

                                <el-button type="text" style="color: #f56c6c;" size="mini" @click="clickDelteItem(scope.row.modelid)">删除
                                </el-button>

                            </template>
                        </el-table-column>
                    </el-table>
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
                modelsList: []
            },
            computed: {},
            methods: {

                // 获取数据
                fetchData: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/cms/model/index')}",
                        data : {
                            'action' : 'getModelsList'
                        },
                        type: "get",
                        dataType: "json",
                        success: function (res) {
                            if (res.status) {
                                that.modelsList = res.data;
                            } else {
                                that.modelsList = [];
                            }
                        }
                    })
                },

                // 模型导入跳转
                importData: function () {
                    var that = this;
                    layer.open({
                        type: 2,
                        title: '模型导入',
                        content: "{:api_url('/cms/model/import')}",
                        area: ['100%', '100%'],
                        end: function () {  //回调函数
                            that.fetchData()
                        }
                    })
                },
                // 查询字段
                searchField:function() {
                    window.location.href = "{:api_url('/cms/FieldExport/exportTableFields')}"
                },
                // 导出全部模型数据字典
                exportAllField:function() {
                    var url = "{:api_url('/cms/FieldExport/exportModelFields')}"
                    layer.open({
                        type: 2,
                        title: '数据字典',
                        content: url,
                        area: ['100%', '100%'],
                    })
                },
                // 字段管理
                modelField:function(modelid) {
                    var url = "{:api_url('/cms/Field/index')}" + '?modelid=' +  modelid
                    layer.open({
                        type: 2,
                        title: '字段管理',
                        content: url,
                        area: ['100%', '100%'],
                    })
                },
                // 添加模型
                add: function () {
                    var that = this
                    layer.open({
                        type: 2,
                        title: '添加',
                        content: "{:api_url('/cms/model/addModel')}",
                        area: ['100%', '100%'],
                        end: function () {  //回调函数
                            that.fetchData()
                        }
                    })
                },
                // 编辑模型
                edit: function (modelid) {
                    var that = this
                    layer.open({
                        type: 2,
                        title: '编辑',
                        content: "{:api_url('/cms/model/editModel')}" + '?modelid=' + modelid,
                        area: ['100%', '100%'],
                        end: function () {  //回调函数
                            that.fetchData()
                        }
                    })
                },
                // 导出数据字典
                exportDictionary: function (modelid) {
                    var url = "{:api_url('/cms/FieldExport/exportModelFields')}" + '?modelid=' + modelid
                    layer.open({
                        type: 2,
                        title: '导出数据字典',
                        content: url,
                        area: ['100%', '100%'],
                    })
                },
                // 导出模型 下载
                exportModel: function (modelid) {
                    window.location.href = "{:api_url('/cms/model/export')}" + '?modelid=' + modelid
                },
                // 删除模型
                clickDelteItem: function (modelid) {
                    var that = this;
                    layer.confirm('确认要删除?', function () {
                        $.ajax({
                            url: "{:api_url('/cms/model/index')}",
                            type: "get",
                            data: {
                                modelid: modelid,
                                action : 'delModel'
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
                // 改变模型状态
                changeStatus: function (modelid, disabled) {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/cms/model/index')}",
                        type: "get",
                        data: {
                            modelid: modelid,
                            disabled: disabled,
                            action : 'disabled'
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
            },
            mounted: function () {
                this.fetchData();
            }
        })
    });
</script>


<style>

</style>
