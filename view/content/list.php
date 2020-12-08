<div>
    <div id="app" v-cloak>
        <el-card>
            <div>
                <el-form :inline="true" :model="searchData" class="demo-form-inline">

                    <div v-for="(item,key) in base_list">
                        <el-form-item :label="item.name">
                            <el-input v-model="searchData.keywords[item.field]"
                                      :placeholder="'请输入小程序'+item.name"></el-input>
                        </el-form-item>
                    </div>

                    <el-form-item>
                        <el-button type="primary" @click="searchEvent">查询</el-button>

                        <el-button @click="detailsEvent(0)" type="primary">新增</el-button>
                    </el-form-item>
                </el-form>
            </div>
            <div>
                <el-table
                    :data="list"
                    style="width: 100%">

                    <div v-for="(item,key) in field_list">
                        <el-table-column
                                :prop="item.field"
                                align="center"
                                :label="item.name"
                                min-width="180">
                        </el-table-column>
                    </div>


                    <el-table-column
                        fixed="right"
                        label="操作"
                        align="center"
                        width="220">
                        <template slot-scope="scope">
                            <el-button @click="detailsEvent(scope.row.id)" type="text">编辑</el-button>
                            <el-button @click="deleteEvent(scope.row.id)" type="text" style="color:#F56C6C ;">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="page-container">
                <el-pagination
                    background
                    :page-size="limit"
                    :page-count="totalPages"
                    :current-page="page"
                    :total="totalItems"
                    layout="prev, pager, next"
                    @current-change="currentChangeEvent">
                </el-pagination>
            </div>
        </el-card>
    </div>
    <style>
        .avatar {
            width: 60px;
            height: 60px;
        }

        .page-container {
            margin-top: 0px;
            text-align: center;
            padding: 10px;
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: "#app",
                data: {
                    //显示的字段
                    field_list : [],
                    //显示的筛选条件
                    base_list : [],
                    //筛选条件
                    searchData: {
                        keywords : {}
                    },
                    list: [

                    ],
                    page: 1,
                    limit: 10,
                    totalPages: 0,
                    totalItems: 0
                },
                mounted:function() {
                    this.getDisplaySettin();

                    this.getTemplateList();
                },
                methods: {

                    //获取显示的字段
                    getDisplaySettin: function () {
                        var that = this;
                        that.httpPost("{:api_url('/cms/content/list')}", {
                            catid : "{$catid}",
                            action : "getDisplaySettin"
                        }, function (res) {
                            that.field_list = res.data.field_list;
                            that.base_list = res.data.base_list;
                        })
                    },

                    //获取显示的列表
                    getTemplateList: function (){
                        var _this = this;

                        var where = Object.assign({
                            page: this.page,
                            limit: this.limit,
                            action : 'getTemplateList',
                            catid : "{$catid}"
                        }, this.searchData);

                        $.ajax({
                            url: "{:api_url('/cms/content/list')}",
                            dataType: 'json',
                            type: 'get',
                            data: where,
                            success: function (res) {
                                if (res.status) {
                                    _this.list = res.data.data;
                                    _this.page = res.data.current_page;
                                    _this.limit = res.data.per_page;
                                    _this.totalPages = res.data.last_page;
                                    _this.totalItems = res.data.total
                                }
                            }
                        })
                    },

                    //删除内容
                    deleteEvent:function(id) {
                        var postData = {
                            id: id,
                            action : 'delTemplate',
                            catid : "{$catid}"
                        };
                        var _this = this;
                        this.$confirm('是否确认删除该记录', '提示', {
                            callback: function (e) {
                                if (e !== 'confirm') {
                                    return;
                                }
                                _this.httpPost('{:api_url("/cms/content/list")}', postData, function (res) {
                                    if (res.status) {
                                        _this.$message.success('删除成功');
                                        _this.getTemplateList();
                                    } else {
                                        _this.$message.error(res.msg);
                                    }
                                })
                            }
                        });
                    },

                    //筛选
                    searchEvent:function() {
                        this.page = 1;
                        this.getTemplateList();
                    },

                    //翻页
                    currentChangeEvent:function(page) {
                        this.page = page;
                        this.getTemplateList();
                    },

                    //详情
                    detailsEvent : function (id) {
                        var that = this;
                        var url = "{:api_url('/cms/content/details')}";
                        url += '?catid=' + "{$catid}";
                        if(id) url += '&id=' + id;
                        layer.open({
                            type: 2,
                            title: '管理',
                            content: url,
                            area: ['100%', '100%'],
                            end: function(){
                                that.getTemplateList();
                            }
                        })
                    }
                }
            })
        });
    </script>
</div>