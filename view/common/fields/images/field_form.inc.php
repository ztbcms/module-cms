<script type="text/x-template" id="field-form-images">
    <div class="field-form-images">
        <el-form-item :label="name">
            <div>
                <template v-for="(file, index) in uploadedList">
                    <div class="imgListItem">
                        <img :src="file.fileurl" style="width: 128px;height: 128px;">
                        <div class="deleteMask" >
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete" @click="deleteImageItem(index)"></span>
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-zoom-in" @click="previewImageItem(index)"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadImage(0)">上传图片</el-button>
        </el-form-item>
    </div>
</script>
<style>
    .field-form-images .imgListItem {
        height: 128px;
        border: 1px dashed #d9d9d9;
        border-radius: 6px;
        display: inline-flex;
        margin-right: 10px;
        margin-bottom: 10px;
        position: relative;
        cursor: pointer;
        vertical-align: top;
    }

    .field-form-images .deleteMask {
        position: absolute;
        top: 0;
        left: 0;
        width: 128px;
        height: 128px;
        text-align: center;
        background-color: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 40px;
        opacity: 0;
    }

    .field-form-images .deleteMask:hover {
        opacity: 1;
    }
</style>
<script>
    $(function () {
        Vue.component('field-form-images', {
            template: '#field-form-images',
            model: {
                // 指定接收 v-model 值/修改事件
                prop: 'value',
                event: 'change'
            },
            props: {
                value: {},
                config: {
                    type: Object,
                    defalut: function () {
                        return {};
                    }
                }
            },
            watch: {
                value: function(){
                    this.field_value = this.value
                    var result = [];
                    for (var i = 0; i < this.value.length; i++) {
                        result.push({
                            fileurl: this.value[i]
                        })
                    }
                    this.uploadedList = result
                },
                field_value: function(val){
                    this.syncVModel()
                }
            },
            computed: {
            },
            data: function () {
                return {
                    field_value: [],
                    name: '',
                    fieldid: '',
                    options: '',
                    upload_callback: 'UPLOAD_IMAGE_',
                    uploadedList: []
                }
            },
            mounted: function () {

                this.name = this.config.name || ''
                this.fieldid = this.config.fieldid || ''
                if(this.config.default ){
                    this.field_value = [this.config.default]
                } else {
                    this.field_value = []
                }
                this.options = this.config.setting.options || ''
                this.syncVModel()

                this.upload_callback = this.upload_callback + this.fieldid
                window.addEventListener(this.upload_callback, this.onUploadedImage.bind(this));
            },
            beforeDestroy: function(){
                window.removeEventListener(this.upload_callback, this.onUploadedImage.bind(this));
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    this.$emit('change', this.field_value)
                },
                gotoUploadImage: function () {
                    layer.open({
                        type: 2,
                        title: '',
                        closeBtn: false,
                        content: "{:api_url('common/upload.panel/imageUpload')}?callback="+this.upload_callback,
                        area: ['720px', '550px'],
                    })
                },
                // 图片回调
                onUploadedImage: function (event) {
                    var files = event.detail.files;
                    if (files && files.length > 0) {
                        for (var i = 0; i < files.length; i++) {
                            this.uploadedList.push(files[i])
                        }
                    }
                },
                // 删除
                deleteImageItem: function (index) {
                    this.uploadedList.splice(index, 1)
                },
                // 预览
                previewImageItem: function (index) {
                    window.open(this.uploadedList[index]['fileurl'])
                }
            }
        });
    })
</script>