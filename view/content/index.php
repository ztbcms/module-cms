<div id="app" style="height: 100%" v-cloak>
    <el-col :span="4">
        <template>
            <div style="margin-top: 20px">

                <el-tree :data="data"
                         default-expand-all
                         :props="defaultProps"
                         @node-click="handleNodeClick">
                </el-tree>

            </div>
        </template>
    </el-col>

    <el-col :span="20" style="">

        <div v-if="src != ''">
            <iframe  :src="src" id="iframe" style="width:100%;border: 0;" :style="{height: iframeHeight + 'px'}"></iframe>
        </div>
        <div v-else>
            <el-alert type="success">
                <p> 温馨提示 ： </p>
                <p> 一 ：终极目录才能进行管理 </p>
            </el-alert>
        </div>


    </el-col>
</div>

<style>
    html,body{
        height: 100%;
        margin: 0;
    }
    #app{
        background: white;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data: function() {
                return {
                    data: [],
                    defaultProps: {
                        children: 'children',
                        label: 'catname'
                    },
                    src : '',
                    iframeHeight: 400
                }
            },
            computed: {},
            watch: {
                filterText: function(val) {
                    this.$refs.tree.filter(val);
                }
            },
            created: function() {
            },
            mounted: function() {
                this.getDetails();
                this.iframeHeight = document.body.clientHeight
            },
            methods: {
                //获取菜单列表
                getDetails: function () {
                    var that = this;
                    var data = {
                        action : 'category_list'
                    };
                    that.httpGet("{:api_url('/cms/Content/index')}", data, function (res) {
                        if (res.status) {
                            that.data = res.data.list;
                        }
                    })
                },
                handleNodeClick : function (data) {
                    if(data.child <= 0) {
                        this.src = "{:api_url('/cms/Content/list')}?catid=" + data.catid;
                    } else {
                        this.src = '';
                    }
                }
            }
        });
    });
</script>

