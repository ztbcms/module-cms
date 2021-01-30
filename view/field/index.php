<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="24">

            <div style="margin-top: 10px">
                <h3>字段管理</h3>
                <div>
                    <p><strong>模型名称</strong>: {{ model_info['name'] }}</p>
                    <p><strong>模型表名</strong>: {{ model_info['table'] }}</p>
                </div>
            </div>

            <div class="filter-container">
                <el-button @click="add" size="small" type="primary">
                    添加字段
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

                    <el-table-column label="排序" align="center"  width="100">
                        <template slot-scope="scope">
                            <span><el-input v-model="scope.row.listorder"></el-input></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="字段名">
                        <template slot-scope="scope">
                            <span>{{ scope.row.field }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="名称">
                        <template slot-scope="scope">
                            <span>{{ scope.row.name }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="字段类型">
                        <template slot-scope="scope">
                            <span>{{ scope.row.form_type }}</span>
                        </template>
                    </el-table-column>


                    <el-table-column label="编辑页展示" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.enable_edit_show == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>

                    <el-table-column label="列表展示" align="center">
                        <template slot-scope="scope">
                             <span class="el-icon-success" style="color: green;font-size: 24px;"
                                   v-if="scope.row.enable_list_show == '1'"></span>
                            <span class="el-icon-error" style="color: red;font-size: 24px;"
                                  v-else></span>
                        </template>
                    </el-table-column>


                    <el-table-column label="操作" align="center" width="250" class-name="small-padding fixed-width">
                        <template slot-scope="scope">
                                <el-button type="text" size="mini"
                                           v-if="scope.row.enable_delete == 1"
                                           @click="edit(scope.row.modelid,scope.row.fieldid)">修改
                                </el-button>

                                <el-button type="text" size="mini" style="color: rgb(245, 108, 108);"
                                           v-if="scope.row.enable_delete == 1"
                                           @click="clickDel(scope.row.modelid,scope.row.fieldid)">删除
                                </el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="btn_wrap" style="margin-top: 20px;">
                    <div class="btn_wrap_pd">
                        <el-button type="primary" size="small" @click="listOrder">排序</el-button>
                        <el-button type="danger" size="small" @click="batchDel">删除</el-button>
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
                modelid: "",
                multipleSelection: [],
                fieldsList: [],
                model_info: {
                    name: '',
                    table: ''
                }
            },
            computed: {
                request_url: function(){
                    return "{:api_url('cms/field/index')}";
                }
            },
            methods: {
                // 获取数据
                fetchData: function () {
                    var that = this;
                    var data = {
                        'modelid' : "{:input('modelid')}",
                        '_action' : 'getFieldData'
                    }
                    that.httpGet(that.request_url, data, function(res){
                        if (res.status) {
                            that.fieldsList = res.data.field_list
                            that.model_info = res.data.model_info
                        } else {
                            layer.msg('操作繁忙，请稍后再试')
                        }
                    })
                },

                // 删除字段
                clickDel: function (modelid, fieldid) {
                    var that = this;
                    layer.confirm('确认要删除?', function () {
                        var data = {
                            modelid: modelid,
                            fieldid: [fieldid],
                            _action : 'delFields'
                        }
                        that.httpPost(that.request_url, data, function(res){
                            layer.msg(res.msg)
                            if (res.status) {
                                that.fetchData();
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
                            var data = {
                                modelid: that.modelid,
                                fieldid: fieldids,
                                _action : 'delFields'
                            }
                            that.httpPost(that.request_url, data, function(res){
                                layer.msg(res.msg)
                                if (res.status) {
                                    that.fetchData();
                                }
                            })
                        });
                    } else {
                        layer.msg('请选择')
                    }
                },

                // 全选
                handleSelectionChange: function (val) {
                    this.multipleSelection = val;
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
                                url: "{:api_url('/cms/field/index')}",
                                type: "post",
                                data: {
                                    'data': formData,
                                    '_action' : 'listOrderFields'
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
                    } else {
                        layer.msg('请选择')
                    }
                },

                // 编辑字段
                edit: function (modelid, fieldid) {
                    var that = this
                    var url = "{:api_url('/cms/field/editField')}" + '?modelid=' + modelid + '&fieldid=' + fieldid;
                    layer.open({
                        type: 2,
                        title: '字段编辑',
                        content: url,
                        area: ['100%', '100%'],
                        end: function () {  //回调函数
                            that.fetchData()
                        }
                    })
                },
                // 添加字段
                add: function () {
                    var that = this
                    var url = "{:api_url('/cms/field/addField')}" + '?modelid=' + this.modelid
                    layer.open({
                        type: 2,
                        title: '字段编辑',
                        content: url,
                        area: ['100%', '100%'],
                        end: function () {  //回调函数
                            that.fetchData()
                        }
                    })
                }
            },
            mounted: function () {
                this.modelid = this.getUrlQuery('modelid')
                this.fetchData();
            }
        })
    });
</script>
