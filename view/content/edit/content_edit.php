<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="9">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" size="medium" label-width="110px">

                        <div  v-for="(item,key) in field_list">

                            <div v-if="item.form_type === 'text'">
                                <field-form-text v-model="formData[item.field]" :config="item">
                                </field-form-text>
                            </div>

                            <div v-if="item.form_type === 'number'">
                                <field-form-number v-model="formData[item.field]" :config="item">
                                </field-form-number>
                            </div>

                            <div v-if="item.form_type === 'textarea'">
                                <field-form-textarea v-model="formData[item.field]" :config="item">
                                </field-form-textarea>
                            </div>

                            <div v-if="item.form_type === 'radio'">
                                <field-form-radio v-model="formData[item.field]" :config="item">
                                </field-form-radio>
                            </div>

                            <div v-if="item.form_type === 'checkbox'">
                                <field-form-checkbox v-model="formData[item.field]" :config="item">
                                </field-form-checkbox>
                            </div>

                            <div v-if="item.form_type === 'select'">
                                <field-form-select v-model="formData[item.field]" :config="item">
                                </field-form-select>
                            </div>

                            <div v-if="item.form_type === 'image'">
                                <field-form-image v-model="formData[item.field]" :config="item">
                                </field-form-image>
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


<!--text-->
{include file="../app/cms/view/common/fields/text/field_form.inc.php"}
<!--textarea-->
{include file="../app/cms/view/common/fields/textarea/field_form.inc.php"}
<!--number-->
{include file="../app/cms/view/common/fields/number/field_form.inc.php"}
<!--radio-->
{include file="../app/cms/view/common/fields/radio/field_form.inc.php"}
<!--checkbox-->
{include file="../app/cms/view/common/fields/checkbox/field_form.inc.php"}
<!--select-->
{include file="../app/cms/view/common/fields/select/field_form.inc.php"}
<!--image-->
{include file="../app/cms/view/common/fields/image/field_form.inc.php"}




<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data: function () {
                return {
                    catid: '',
                    id: '',
                    field_list: [],
                    formData: {},
                    editor: {}
                }
            },
            computed: {
                request_url: function(){
                    if(this.id){
                        return "{:api_url('/cms/content/content_edit')}"
                    }
                    return "{:api_url('/cms/content/content_add')}"
                }
            },
            watch: {},
            created: function () {
                var that = this;

                // that.getDisplaySettin();

            },
            mounted: function () {
                this.catid = this.getUrlQuery('catid') || ''
                this.id = this.getUrlQuery('catid') || ''

                this.getFormSetting()
            },
            methods: {
                //获取显示的字段
                getDisplaySettin: function () {
                    var that = this;
                    this.httpPost("{:api_url('/cms/content/details')}", {
                        catid: this.catid,
                        id: this.id,
                        _action: "getDisplaySetting"
                    }, function (res) {

                        that.field_list = res.data.field_list;
                        that.formData = res.data.form_data;

                    });
                },
                submitForm: function () {
                    var that = this;
                    var url = "{:api_url('/cms/Content/details')}";

                    var where = Object.assign({
                        _action: 'submitForm',
                        catid: this.catid
                    }, this.formData);

                    //处理富文本的信息
                    for (var i in that.editor) {
                        where[i] = that.editor[i].getContent();
                    }

                    that.httpPost(url, where, function (res) {
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
                },
                getFormSetting: function (){
                    var that = this;
                    var data = {
                        catid: this.catid,
                        _action: "getFormSetting"
                    }
                    this.httpGet(this.request_url, data, function (res) {
                        that.field_list = res.data;
                    });
                },
                getDetail: function(){

                }
            }
        });
    });
</script>