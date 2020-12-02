
<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <div class="h_a">温馨提示</div>
        <div class="prompt_text">
            <p>1、请在添加、修改栏目全部完成后，<a href="{:api_url("/cms/category/public_cache")}">更新栏目缓存</a>，否则可能出现未知错误！</p>
            <p>2、栏目<font color="blue">ID</font>为<font color="blue">蓝色</font>才可以添加内容。可以使用“终极属性转换”进行转换！</p>
        </div>

        <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary" @click="details('')">
            添加栏目
        </el-button>

        <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary" @click="listOrder()">
            排序
        </el-button>


        <el-table
            :key="tableKey"
            :data="list"
            highlight-current-row
            style="width: 100%;"
            @selection-change="handleSelectionChange"
        >
            <el-table-column
                type="selection"
                width="55"
                label="全选">
            </el-table-column>

            <el-table-column label="排序" width="80px" align="center">
                <template slot-scope="{row}">
                    <el-input v-model="row.listorder"></el-input>
                </template>
            </el-table-column>

            <el-table-column label="栏目ID" align="center" width="100px">
                <template slot-scope="scope">
                    <span>{{ scope.row.catid }}</span>
                </template>
            </el-table-column>

            <el-table-column label="栏目名称" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.catname }}</span>
                </template>
            </el-table-column>

            <el-table-column label="栏目类型" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.type_name }}</span>
                </template>
            </el-table-column>

            <el-table-column label="所属模型" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.model_name }}</span>
                </template>
            </el-table-column>

            <el-table-column label="访问" width="100px" align="center">
                <template slot-scope="{row}">
                    <el-link @click="updateCache(row.url,row.url_jump)">{{row.url_text}}</el-link>
                </template>
            </el-table-column>

            <el-table-column label="域名绑定须知" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.catname }}</span>
                </template>
            </el-table-column>

            <el-table-column label="操作" align="center" width="280" class-name="small-padding fixed-width">
                <template slot-scope="scope">
                    <el-button type="text" size="mini" @click="details(scope.row.catid)">修改</el-button>
                    <el-button type="text" size="mini" @click="handleDelete(scope.row.catid)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination
                background
                layout="prev, pager, next, jumper"
                :total="listQuery.total"
                v-show="listQuery.total > 0"
                :current-page.sync="listQuery.page"
                :page-size.sync="listQuery.limit"
                @current-change="getList"
            >
            </el-pagination>
        </div>

    </el-card>
</div>

<style>
    .filter-container {
        padding-bottom: 10px;
    }

    .pagination-container {
        padding: 32px 16px;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                tableKey: 0,
                list: [],
                multipleSelection: [],
                total: 0,
                listQuery: {
                    page: 1,
                    limit: 20,
                    total: 0,
                    keyword: ''
                }
            },
            watch: {},
            filters: {},
            methods: {
                // 全选
                handleSelectionChange: function (val) {
                    this.multipleSelection = val;
                    console.log(this.multipleSelection)
                },
                // 获取列表
                getList: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/cms/category/getCategoyList')}",
                        type: "get",
                        dataType: "json",
                        data: that.listQuery,
                        success: function (res) {
                            if (res.status) {
                                that.list = res.data;
                            }
                        }
                    })
                },
                // 排序批量
                listOrder: function () {
                    var that = this;
                    var formData = [];
                    this.multipleSelection.forEach(function (val, index) {
                        formData.push({
                            catid: val.catid,
                            listorder: val.listorder,
                        })
                    });
                    if (formData.length > 0) {
                        layer.confirm('确认要进行排序?', function () {
                            $.ajax({
                                url: "{:api_url('/cms/category/listOrder')}",
                                type: "post",
                                data: {
                                    data: formData
                                },
                                dataType: "json",
                                success: function (res) {
                                    layer.msg(res.msg)
                                    if (res.status) {
                                        that.getList();
                                    }
                                }
                            })
                        });
                    } else {
                        layer.msg('请选择')
                    }
                },
                // 更新缓存或者打开访问链接
                updateCache:function(url,type){
                    var that = this;
                    if(type == 'update'){
                        that.httpPost(url, {}, function(res){
                            if(res.status){
                                layer.msg('操作成功', {icon: 1});
                                that.getList();
                            } else {
                                layer.msg(res.msg);
                            }
                        });
                    }
                    if(type == 'open'){
                        window.open(url)
                    }
                },
                // 删除
                handleDelete: function (index) {
                    var that = this;
                    var url = '{:api_url("/admin/Menu/doDelete")}';
                    layer.confirm('您确定需要删除？', {
                        btn: ['确定','取消'] //按钮
                    }, function(){
                        var data = {
                            "id": index
                        };
                        that.httpPost(url, data, function(res){
                            if(res.status){
                                layer.msg('操作成功', {icon: 1});
                                that.getList();
                            } else {
                                layer.msg(res.msg);
                            }
                        });
                    });
                },

                details : function (id) {
                    var that = this;
                    var url = '{:api_url("/cms/Category/details")}';
                    if(id) url += '&id=' + id;
                    layer.open({
                        type: 2,
                        title: '管理',
                        content: url,
                        area: ['95%', '95%'],
                        end: function(){
                            that.getList();
                        }
                    })
                },
                linkMenuAdd: function (parentid) {
                    var that = this;
                    var url = '{:api_url("/admin/Menu/details")}';
                    if(parentid) url += '&parentid=' + parentid;
                    layer.open({
                        type: 2,
                        title: '管理',
                        content: url,
                        area: ['95%', '95%'],
                        end: function(){
                            that.getList();
                        }
                    })
                },
            },
            mounted: function () {
                this.getList();
            },
        })
    })
</script>
<script src="/statics/js/common.js"></script>
<link href="/statics/css/admin_style.css" rel="stylesheet"/>
