<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" size="medium" label-width="210px">
                        <div v-if="field_list" v-for="(item,key) in field_list">
                            <!--栏目-->
                            <div v-if="item.formtype === 'catid'">
                                {include file="../app/cms/fields/catid/content_form.inc.php"}
                            </div>

                            <!--标题-->
                            <div v-if="item.formtype === 'title'">
                                {include file="../app/cms/fields/title/content_form.inc.php"}
                            </div>

                            <!--关键词-->
                            <div v-if="item.formtype === 'keyword'">
                                {include file="../app/cms/fields/keyword/content_form.inc.php"}
                            </div>

                            <div v-if="item.formtype === 'tags'">
                                {include file="../app/cms/fields/tags/content_form.inc.php"}
                            </div>

                            <!--多行文本-->
                            <div v-if="item.formtype === 'textarea'">
                                {include file="../app/cms/fields/textarea/content_form.inc.php"}
                            </div>

                            <!--来源-->
                            <div v-if="item.formtype === 'copyfrom'">
                                {include file="../app/cms/fields/copyfrom/content_form.inc.php"}
                            </div>

                            <!--富文本编辑器-->
                            <div v-if="item.formtype === 'editor'">
                                {include file="../app/cms/fields/editor/content_form.inc.php"}
                            </div>

                            <!--缩略图-->
                            <div v-if="item.formtype === 'image'">
                                {include file="../app/cms/fields/image/content_form.inc.php"}
                            </div>

                            <!--时间-->
                            <div v-if="item.formtype === 'datetime'">
                                {include file="../app/cms/fields/datetime/content_form.inc.php"}
                            </div>

                            <!--文本-->
                            <div v-if="item.formtype === 'text'">
                                {include file="../app/cms/fields/text/content_form.inc.php"}
                            </div>

                            <!--转向链接-->
                            <div v-if="item.formtype === 'islink'">
                                {include file="../app/cms/fields/islink/content_form.inc.php"}
                            </div>

                            <!--数字-->
                            <div v-if="item.formtype === 'number'">
                                {include file="../app/cms/fields/number/content_form.inc.php"}
                            </div>

                            <!--多选框-->
                            <div v-if="item.formtype === 'box'">
                                {include file="../app/cms/fields/box/content_form.inc.php"}
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

<!-- 引入UEditor   -->
{include file="../app/cms/view/common/ueditor.php"}
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

                    },
                    editor : {

                    },
                    upload_flag : '',
                    is_upload_more : false
                }
            },
            computed: {},
            watch: {},
            created: function() {
                var that = this;

                that.getDisplaySettin();

            },
            mounted: function() {
                window.addEventListener('ZTBCMS_UPLOAD_IMAGE', this.onUploadedImage.bind(this));

                var that = this;
                //加载富文本编辑器
                var editor = "{$editor}";
                setInterval(function working(){
                    var arr = editor.split(',');
                    arr.filter(function (element,index, self) {
                        that.editor[element] = UE.getEditor(element);
                    });
                },2000);
            },
            methods: {

                //获取显示的字段
                getDisplaySettin : function () {
                    var that = this;
                    that.httpPost("{:api_url('/cms/content/details')}", {
                        catid : "{$catid}",
                        id : "{$id}",
                        action : "getDisplaySettin"
                    }, function (res) {

                        that.field_list = res.data.field_list;
                        that.formData = res.data.form_data;

                    });
                },

                uploadImg: function (upload_flag,is_upload_more) {
                    this.upload_flag = upload_flag;
                    this.is_upload_more = is_upload_more;

                    layer.open({
                        type: 2,
                        title: '',
                        closeBtn: false,
                        content: '{:api_url("/common/upload.panel/imageUpload")}',
                        area: ['70%', '80%']
                    })
                },

                onUploadedImage: function (event) {
                    var that = this;
                    var files = event.detail.files;
                    if (files) {
                        that.formData[that.upload_flag] = files[0].fileurl;
                    }
                },
                
                submitForm: function() {
                    var that = this;
                    var url = "{:api_url('/cms/Content/details')}";

                    var where = Object.assign({
                        action : 'submitForm',
                        catid : "{$catid}"
                    }, this.formData);

                    //处理富文本的信息
                    for(var i in that.editor){
                        where[i] = that.editor[i].getContent();
                    }

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

<style>
    .imgListItem {
        height: 120px;
        border: 1px dashed #d9d9d9;
        border-radius: 6px;
        display: inline-flex;
        margin-right: 10px;
        margin-bottom: 10px;
        position: relative;
        cursor: pointer;
        vertical-align: top;
    }
    .deleteMask {
        position: absolute;
        top: 0;
        left: 0;
        width: 120px;
        height: 120px;
        text-align: center;
        background-color: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 40px;
        opacity: 0;
    }
    .deleteMask:hover {
        opacity: 1;
    }
</style>