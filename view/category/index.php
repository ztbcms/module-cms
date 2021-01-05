
<div id="app" style="padding: 8px;" v-cloak>
    <el-card>

        <el-alert type="success" style="margin-bottom: 10px;">
            <p>温馨提示：</p>
            <p>1、请在添加、修改栏目全部完成后，更新栏目缓存，否则可能出现未知错误</p>
            <p>2、终极栏目为内容， 非终极栏目为目录</p>
        </el-alert>

        <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary" @click="toAdd">
            添加栏目
        </el-button>

        <el-table
            :key="tableKey"
            :data="list"
            highlight-current-row
            style="width: 100%;"
        >

            <el-table-column label="栏目ID" align="center" width="100px">
                <template slot-scope="scope">
                    <span>{{ scope.row.catid }}</span>
                </template>
            </el-table-column>

            <el-table-column label="栏目名称" align="">
                <template slot-scope="scope">
                    <template v-for="i in scope.row.level * 2"><span>&nbsp;</span></template>
                    <template v-if="scope.row.level > 0"><span> ∟</span></template>
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


            <el-table-column label="操作" align="center" width="280" class-name="small-padding fixed-width">
                <template slot-scope="scope">
                    <el-button type="text" size="mini" @click="toEdit(scope.row.catid)">编辑</el-button>
                    <el-button type="text" size="mini" @click="handleDelete(scope.row.catid)" style="color: #F56C6C">删除</el-button>
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


<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                tableKey: 0,
                list: [],
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
                toAdd: function(){
                    var that = this;
                    var url = '{:api_url("/cms/category/addCategory")}';
                    layer.open({
                        type: 2,
                        title: '编辑',
                        content: url,
                        area: ['95%', '95%'],
                        end: function(){
                            that.getList();
                        }
                    })
                },
                toEdit: function(catid){
                    var that = this;
                    var url = '{:api_url("/cms/category/editCategory")}';
                    url += '?catid=' + catid;
                    layer.open({
                        type: 2,
                        title: '编辑',
                        content: url,
                        area: ['95%', '95%'],
                        end: function(){
                            that.getList();
                        }
                    })
                },
                // 获取列表
                getList: function () {
                    var that = this;
                    var data = that.listQuery;
                    data.action = 'getCategoyList';
                    $.ajax({
                        url: "{:api_url('/cms/category/index')}",
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
                // 删除
                handleDelete: function (index) {
                    var that = this;
                    var url = '{:api_url("/cms/category/index")}';
                    layer.confirm('您确定需要删除？', {
                        btn: ['确定','取消'] //按钮
                    }, function(){
                        var data = {
                            "catid" : index,
                            "action" : 'doDelete'
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
            },
            mounted: function () {
                this.getList();
            }
        })
    })
</script>
