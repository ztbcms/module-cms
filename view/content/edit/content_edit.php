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

                            <div v-if="item.form_type === 'images'">
                                <field-form-images v-model="formData[item.field]" :config="item">
                                </field-form-images>
                            </div>

                            <div v-if="item.form_type === 'video'">
                                <field-form-video v-model="formData[item.field]" :config="item">
                                </field-form-video>
                            </div>

                            <div v-if="item.form_type === 'videos'">
                                <field-form-videos v-model="formData[item.field]" :config="item">
                                </field-form-videos>
                            </div>

                            <div v-if="item.form_type === 'datetime'">
                                <field-form-datetime v-model="formData[item.field]" :config="item">
                                </field-form-datetime>
                            </div>

                            <div v-if="item.form_type === 'editor'">
                                <field-form-editor v-model="formData[item.field]" :config="item">
                                </field-form-editor>
                            </div>

                            <div v-if="item.form_type === 'catid'">
                                <field-form-catid v-model="formData[item.field]" :config="item" :category-list="categoryList">
                                </field-form-catid>
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
<!--images-->
{include file="../app/cms/view/common/fields/images/field_form.inc.php"}
<!--video-->
{include file="../app/cms/view/common/fields/video/field_form.inc.php"}
<!--videos-->
{include file="../app/cms/view/common/fields/videos/field_form.inc.php"}
<!--datetime-->
{include file="../app/cms/view/common/fields/datetime/field_form.inc.php"}
<!--editor-->
{include file="../app/common/view/ueditor_simplicity.php"}
{include file="../app/cms/view/common/fields/editor/field_form.inc.php"}
<!--catid-->
{include file="../app/cms/view/common/fields/catid/field_form.inc.php"}


<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data: function () {
                return {
                    field_list: [],
                    formData: {
                        catid: '',
                        id: ''
                    },
                    categoryList: []
                }
            },
            computed: {
                request_url: function(){
                    if(this.formData.id){
                        return "{:api_url('/cms/content/content_edit')}"
                    }
                    return "{:api_url('/cms/content/content_add')}"
                }
            },
            watch: {},
            created: function () {
                this.formData.catid = this.getUrlQuery('catid') || ''
                this.formData.id = this.getUrlQuery('id') || ''
                this.getFormSetting()
            },
            mounted: function () {},
            methods: {
                // 提交
                submitForm: function () {
                    var that = this
                    var data = {
                        _action: 'submitForm',
                        content: this.formData
                    }
                    that.httpPost(that.request_url, data, function (res) {
                        layer.msg(res.msg);
                        if (res.status) {
                            //添加成功
                            if (window !== window.parent && window.parent.layer) {
                                window.parent.layer.closeAll()
                            }
                        }
                    })
                },
                // 表单设置
                getFormSetting: function (){
                    var that = this;
                    var data = {
                        catid: this.formData.catid,
                        _action: "getFormSetting"
                    }
                    this.httpGet(this.request_url, data, function (res) {
                        that.field_list = res.data.field_list
                        that.categoryList = res.data.category_list
                        // 获取详情
                        if(that.formData.id){
                            that.getDetail()
                        }
                    })
                },
                // 获取详情
                getDetail: function(){
                    var that = this
                    var data = {
                        catid: this.formData.catid,
                        id: this.formData.id,
                        _action: "getDetail"
                    }
                    this.httpGet(this.request_url, data, function (res) {
                        if(res.status){
                            that.formData = res.data
                            console.log(that.formData)
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                }
            }
        });
    });
</script>