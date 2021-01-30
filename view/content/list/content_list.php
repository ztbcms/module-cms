<div>
    <div id="app" v-cloak>
        <el-card>
            <el-form :inline="true" :model="searchData" size="mini">

                <template v-for="(item, index) in searchList">
                    <el-form-item :label="item.name">
                        <el-input v-model="item['value']" :placeholder="'请输入'+item.name"></el-input>
                    </el-form-item>
                </template>

                <el-form-item>
                    <el-button type="primary" @click="doSearch">查询</el-button>

                    <el-button @click="editContent(0)" type="primary">添加</el-button>
                </el-form-item>
            </el-form>

            <div>
                <el-table
                    :data="list"
                    style="width: 100%">

                    <template v-for="(item,key) in field_list">
                        <el-table-column
                                :prop="item.field"
                                align="center"
                                :label="item.name"
                                min-width="180">
                            <template slot-scope="scope">
                                <template v-if="item.form_type == 'image'">
                                    <list-item-image v-model="scope.row[item.field]" :config="item"></list-item-image>
                                </template>
                                <template v-else-if="item.form_type == 'images'">
                                    <list-item-images v-model="scope.row[item.field]" :config="item"></list-item-images>
                                </template>
                                <template v-else-if="item.form_type == 'video'">
                                    <list-item-video v-model="scope.row[item.field]" :config="item"></list-item-video>
                                </template>
                                <template v-else-if="item.form_type == 'videos'">
                                    <list-item-videos v-model="scope.row[item.field]" :config="item"></list-item-videos>
                                </template>
                                <template v-else-if="item.form_type == 'checkbox'">
                                    <list-item-checkbox v-model="scope.row[item.field]" :config="item"></list-item-checkbox>
                                </template>
                                <template v-else>
                                    {{ scope.row[item.field] }}
                                </template>

                            </template>
                        </el-table-column>
                    </template>


                    <el-table-column
                        fixed="right"
                        label="操作"
                        align="center"
                        width="220">
                        <template slot-scope="scope">
                            <el-button @click="editContent(scope.row.id)" type="text">编辑</el-button>
                            <el-button @click="deleteItem(scope.row.id)" type="text" style="color:#F56C6C ;">删除</el-button>
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
    <!--image-->
    {include file="../app/cms/view/common/fields/image/list_item.inc.php"}
    <!--images-->
    {include file="../app/cms/view/common/fields/images/list_item.inc.php"}
    <!--video-->
    {include file="../app/cms/view/common/fields/video/list_item.inc.php"}
    <!--videos-->
    {include file="../app/cms/view/common/fields/videos/list_item.inc.php"}
    <!--checkbox-->
    {include file="../app/cms/view/common/fields/checkbox/list_item.inc.php"}


    <script>
        $(document).ready(function () {
            new Vue({
                el: "#app",
                data: {
                    catid: '',
                    //显示的字段
                    field_list : [
                        // {
                        //     'field': 'id',
                        //     'name': 'ID',
                        // },
                    ],
                    //显示的筛选条件
                    base_list : [],
                    //筛选条件
                    searchData: {
                        id: {
                            field: 'id',

                        }
                    },
                    list: [],
                    // 筛选
                    searchList: [
                        {
                            'name': 'ID',
                            'field': 'id',
                            'operator': '=',
                            'value': ''
                        },
                        {
                            'name': '标题',
                            'field': 'title',
                            'operator': 'like',
                            'value': ''
                        }
                    ],
                    page: 1,
                    limit: 10,
                    totalPages: 0,
                    totalItems: 0
                },
                mounted:function() {
                    this.catid = this.getUrlQuery('catid')
                    this.getListParam()
                    // this.getContentList()
                },
                methods: {
                    //获取显示的字段
                    getListParam: function () {
                        var that = this;
                        that.httpGet("{:api_url('/cms/content/content_list')}", {
                            catid: this.catid,
                            _action: "getListParam"
                        }, function (res) {
                            that.field_list = res.data.field_list;
                            // that.base_list = res.data.base_list;
                            that.getContentList()
                        })
                    },
                    // 获取列表信息
                    getContentList: function () {
                        var that = this
                        var where = []
                        for(var i=0;i<this.searchList.length;i++){
                            var item = this.searchList[i]
                            if(item['value'] !== ''){
                                where.push({
                                    field: item['field'],
                                    operator: item['operator'],
                                    value: item['value']
                                })
                            }
                        }
                        var data = {
                            _action: 'getContentList',
                            catid: this.catid,
                            page: this.page,
                            limit: this.limit,
                            where: where,
                        }
                        this.httpGet("{:api_url('/cms/content/content_list')}", data, function (res) {
                            if (res.status) {
                                that.list = res.data.items;
                                that.page = res.data.page;
                                that.limit = res.data.limit;
                                that.totalPages = res.data.total_pages;
                                that.totalItems = res.data.total
                            }
                        })
                    },

                    //删除内容
                    deleteItem: function(id) {
                        var that = this
                        this.$confirm('是否确认删除该记录', '提示', {
                            callback: function (e) {
                                if (e !== 'confirm') {
                                    return;
                                }
                                var data = {
                                    _action : 'deleteContent',
                                    catid : that.catid,
                                    id: id
                                };
                                that.httpPost('{:api_url("/cms/content/content_list_operate")}', data, function (res) {
                                    if (res.status) {
                                        that.$message.success('删除成功');
                                        that.getContentList();
                                    } else {
                                        that.$message.error(res.msg);
                                    }
                                })
                            }
                        });
                    },

                    //筛选
                    doSearch:function() {
                        this.page = 1
                        this.getContentList()
                    },

                    //翻页
                    currentChangeEvent:function(page) {
                        this.page = page;
                        this.getContentList();
                    },
                    
                    // 添加、编辑
                    editContent: function (id) {
                        var that = this;
                        var url = "{:api_url('/cms/content/content_edit')}";
                        url += '?catid=' + this.catid;
                        if (id) url += '&id=' + id;
                        var _layer = layer
                        if (window !== window.parent && window.parent.layer) {
                            _layer = window.parent.layer
                        }
                        _layer.open({
                            type: 2,
                            title: '编辑',
                            content: url,
                            area: ['95%', '95%'],
                            end: function () {
                                that.getContentList();
                            }
                        })
                    },
                }
            })
        });
    </script>
</div>