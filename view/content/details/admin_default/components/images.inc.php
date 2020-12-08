<script type="text/x-template" id="default-images">
    <div class="default-images">
        <el-form-item :label="field.name">
            <template v-for="(item,index) in field_data[field.field]">
                <div class="imgListItem">
                    <img :src="item" style="width: 120px;height: 120px;">
                    <div class="deleteMask" @click="delImage(index)">
                        <span style="line-height: 120px;font-size: 22px" class="el-icon-delete"></span>
                    </div>
                </div>
            </template>
            <div class="imgListItem">
                <div @click="uploadImg" style="width: 120px;height: 120px;text-align: center;">
                    <span style="line-height: 120px;font-size: 22px" class="el-icon-plus"></span>
                </div>
            </div>
        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-images', {
            props: {
                field: {
                    type: Object,
                    defalut: function () {
                        return {};
                    }
                },
                field_data: {
                    type: Object,
                    defalut: function () {
                        return {};
                    }
                }
            },
            watch: {},
            template: '#default-images',
            data: function () {
                return {}
            },
            created: function () {

            },
            mounted: function () {
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
                    var that = this;
                    if (files) {
                        for (var k in files) {
                            that.field_data[that.field.field].push(files[k].fileurl);
                        }
                    }
                },
                delImage: function (index) {
                    var that = this;
                    Vue.delete(that.field_data[that.field.field], index);
                }
            }
        });
    })
</script>


<style>
    .default-images .imgListItem {
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

    .default-images .deleteMask {
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

    .default-images.deleteMask:hover {
        opacity: 1;
    }
</style>