<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" size="medium" label-width="210px">
                        <div v-if="field_list" v-for="(item,key) in field_list">
                            <!--栏目-->
                            <div v-if="item.formtype === 'catid'">

                            </div>

                            <!--单行文本-->
                            <div v-if="item.formtype === 'title'">
                                {include file="../app/cms/fields/text/content_form.inc.php"}
                            </div>
                        </div>


                        <el-form-item size="large">
                            <el-button type="primary" @click="submitForm">保存</el-button>
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
            data: function() {
                return {
                    field_list : [],
                    formData : {

                    }
                }
            },
            computed: {},
            watch: {},
            created: function() {
                this.getDisplaySettin();
            },
            mounted: function() {

            },
            methods: {
                //获取显示的字段
                getDisplaySettin : function () {
                    var that = this;
                    that.httpPost("{:api_url('/cms/content/details')}", {
                        catid : "{$catid}",
                        action : "getDisplaySettin"
                    }, function (res) {
                        that.formData = res.data.form_data;
                        that.field_list = res.data.field_list;
                    });
                },
                submitForm: function() {
                    var that = this;
                    var url = "{:api_url('/cms/Content/details')}";

                    var where = Object.assign({
                        action : 'submitForm',
                        catid : "{$catid}"
                    }, this.formData);

                    that.httpPost(url,where, function(res){
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
                }
            }
        });
    });
</script>