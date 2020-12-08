<script type="text/x-template" id="default-image">
    <div>
        <div class="block">
            <el-form-item :label="field.name">
                <template v-if="field_data[field.field] != ''">
                    <div class="imgListItem">
                        <img :src="field_data[field.field]" style="width: 120px;height: 120px;">
                        <div class="deleteMask" @click="uploadImg()">
                            <span style="line-height: 120px;font-size: 22px" class="el-icon-upload"></span>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="imgListItem">
                        <div
                            @click="uploadImg()" style="width: 120px;height: 120px;text-align: center;">
                            <span style="line-height: 120px;font-size: 22px" class="el-icon-plus"></span>
                        </div>
                    </div>
                </template>
            </el-form-item>
        </div>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-image', {
            props: {
                field: {
                    type: Object,
                    defalut:function() {
                        return {};
                    }
                },
                field_data : {
                    type: Object,
                    defalut:function() {
                        return {};
                    }
                }
            },
            watch: {

            },
            template: '#default-image',
            data: function() {
                return {

                }
            },
            created: function() {

            },
            mounted:function() {
                window.addEventListener('ZTBCMS_UPLOAD_IMAGE', this.onUploadedImage.bind(this));
            },
            methods: {
                uploadImg: function () {
                    layer.open({
                        type: 2,
                        title: '',
                        closeBtn: false,
                        content: '{:api_url("/common/upload.panel/imageUpload")}',
                        area: ['70%', '80%']
                    })
                },
                onUploadedImage: function (event) {
                    var files = event.detail.files;
                    if (files) {
                        this.field_data[this.field.field] = files[0].fileurl;
                    }
                }
            }
        });
    })
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