<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" size="medium" label-width="210px">

                        <div v-if="field_list" v-for="(item,key) in field_list">

                            <div v-if="item.formtype === 'catid'">
                                <default-catid v-model="formData[item.field]"
                                           :field="item" :field_data="formData">
                                </default-catid>
                            </div>

                            <div v-if="item.formtype === 'typeid'">
                                <default-typeid v-model="formData[item.field]"
                                               :field="item" :field_data="formData">
                                </default-typeid>
                            </div>

                            <div v-if="item.formtype === 'title'">
                                <default-title v-model="formData[item.field]"
                                               :field="item" :field_data="formData">
                                </default-title>
                            </div>

                            <div v-if="item.formtype === 'keyword'">
                                <default-keyword v-model="formData[item.field]"
                                               :field="item" :field_data="formData">
                                </default-keyword>
                            </div>

                            <div v-if="item.formtype === 'tags'">
                                <default-tags v-model="formData[item.field]"
                                                 :field="item" :field_data="formData">
                                </default-tags>
                            </div>

                            <div v-if="item.formtype === 'textarea'">
                                <default-textarea v-model="formData[item.field]"
                                              :field="item" :field_data="formData">
                                </default-textarea>
                            </div>

                            <div v-if="item.formtype === 'editor'">
                                <default-editor v-model="formData[item.field]"
                                                  :field="item" :field_data="formData">
                                </default-editor>
                            </div>

                            <div v-if="item.formtype === 'image'">
                                <default-image v-model="formData[item.field]"
                                                :field="item" :field_data="formData">
                                </default-image>
                            </div>

                            <div v-if="item.formtype === 'omnipotent'">
                                <default-omnipotent v-model="formData[item.field]"
                                               :field="item" :field_data="formData">
                                </default-omnipotent>
                            </div>

                            <div v-if="item.formtype === 'datetime'">
                                <default-datetime v-model="formData[item.field]"
                                            :field="item" :field_data="formData">
                                </default-datetime>
                            </div>

                            <div v-if="item.formtype === 'posid'">
                                <default-posid v-model="formData[item.field]"
                                                  :field="item" :field_data="formData">
                                </default-posid>
                            </div>

                            <div v-if="item.formtype === 'text'">
                                <default-text v-model="formData[item.field]"
                                               :field="item" :field_data="formData">
                                </default-text>
                            </div>

                            <div v-if="item.formtype === 'template'">
                                <default-template v-model="formData[item.field]"
                                              :field="item" :field_data="formData">
                                </default-template>
                            </div>

                            <div v-if="item.formtype === 'box'">
                                <default-box v-model="formData[item.field]"
                                                  :field="item" :field_data="formData">
                                </default-box>
                            </div>

                            <div v-if="item.formtype === 'islink'">
                                <default-islink v-model="formData[item.field]"
                                             :field="item" :field_data="formData">
                                </default-islink>
                            </div>

                            <div v-if="item.formtype === 'number'">
                                <default-number v-model="formData[item.field]"
                                                :field="item" :field_data="formData">
                                </default-number>
                            </div>

                            <div v-if="item.formtype === 'images'">
                                <default-images v-model="formData[item.field]"
                                                :field="item" :field_data="formData">
                                </default-images>
                            </div>

                            <div v-if="item.formtype === 'downfile'">
                                <default-downfile v-model="formData[item.field]"
                                                :field="item" :field_data="formData">
                                </default-downfile>
                            </div>

                            <div v-if="item.formtype === 'author'">
                                <default-author v-model="formData[item.field]"
                                                  :field="item" :field_data="formData">
                                </default-author>
                            </div>

                            <div v-if="item.formtype === 'downfiles'">
                                <default-downfiles v-model="formData[item.field]"
                                                :field="item" :field_data="formData">
                                </default-downfiles>
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


<!--栏目主键-->
{include file="../app/cms/view/content/details/admin_default/components/catid.inc.php"}

<!--类别主键-->
{include file="../app/cms/view/content/details/admin_default/components/typeid.inc.php"}

<!--单行文本主键-->
{include file="../app/cms/view/content/details/admin_default/components/title.inc.php"}

<!--关键词主键-->
{include file="../app/cms/view/content/details/admin_default/components/keyword.inc.php"}

<!--标签主键-->
{include file="../app/cms/view/content/details/admin_default/components/tags.inc.php"}

<!--多行文本主键-->
{include file="../app/cms/view/content/details/admin_default/components/textarea.inc.php"}

<!--富文本主键-->
{include file="../app/cms/view/content/details/admin_default/components/editor.inc.php"}

<!--缩略图主键-->
{include file="../app/cms/view/content/details/admin_default/components/image.inc.php"}

<!--相关文章主键-->
{include file="../app/cms/view/content/details/admin_default/components/omnipotent.inc.php"}

<!--时间主键-->
{include file="../app/cms/view/content/details/admin_default/components/datetime.inc.php"}

<!--推荐位主键-->
{include file="../app/cms/view/content/details/admin_default/components/posid.inc.php"}

<!--单行文本主键-->
{include file="../app/cms/view/content/details/admin_default/components/text.inc.php"}

<!--内容页模板主键-->
{include file="../app/cms/view/content/details/admin_default/components/template.inc.php"}

<!--内容页模板主键-->
{include file="../app/cms/view/content/details/admin_default/components/box.inc.php"}

<!--转向链接主键-->
{include file="../app/cms/view/content/details/admin_default/components/islink.inc.php"}

<!--数字主键-->
{include file="../app/cms/view/content/details/admin_default/components/number.inc.php"}

<!--多图片主键-->
{include file="../app/cms/view/content/details/admin_default/components/images.inc.php"}

<!--文件上传主键-->
{include file="../app/cms/view/content/details/admin_default/components/downfile.inc.php"}

<!--作者主键-->
{include file="../app/cms/view/content/details/admin_default/components/author.inc.php"}

<!--多文件上传主键-->
{include file="../app/cms/view/content/details/admin_default/components/downfiles.inc.php"}


<!-- 引入UEditor   -->
{include file="../app/cms/view/common/ueditor.php"}
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
                    formData: {},
                    editor: {}
                }
            },
            computed: {},
            watch: {},
            created: function () {
                var that = this;

                that.getDisplaySettin();

            },
            mounted: function () {
                var that = this;
                //加载富文本编辑器
                var editor = "{$editor}";
                setInterval(function working(){
                    var arr = editor.split(',');
                    arr.filter(function (element,index, self) {
                        that.editor[element] = UE.getEditor(element);
                    });
                },1000);
            },
            methods: {
                //获取显示的字段
                getDisplaySettin: function () {
                    var that = this;
                    that.httpPost("{:api_url('/cms/content/details')}", {
                        catid: "{$catid}",
                        id: "{$id}",
                        action: "getDisplaySettin"
                    }, function (res) {

                        that.field_list = res.data.field_list;
                        that.formData = res.data.form_data;

                    });
                },
                submitForm: function () {
                    var that = this;
                    var url = "{:api_url('/cms/Content/details')}";

                    var where = Object.assign({
                        action: 'submitForm',
                        catid: "{$catid}"
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
                }
            }
        });
    });
</script>