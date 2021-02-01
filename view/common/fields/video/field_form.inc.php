<script type="text/x-template" id="field-form-video">
    <div class="field-form-video">
        <el-form-item :label="name">
            <div>
                <template v-for="(file, index) in uploadedList">
                    <div class="imgListItem">
                        <img src="/statics/admin/upload/video.png" style="width: 128px;height: 128px;">
                        <div class="deleteMask" >
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete" @click="deleteVideoItem(index)"></span>
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-zoom-in" @click="previewVideoItem(index)"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadPanel(0)" size="mini">上传</el-button>
        </el-form-item>
    </div>
</script>
<style>
    .field-form-video .imgListItem {
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

    .field-form-video .deleteMask {
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

    .field-form-video .deleteMask:hover {
        opacity: 1;
    }
</style>
<script>
    $(function () {
        Vue.component('field-form-video', {
            template: '#field-form-video',
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
                    if(this.value){
                        this.uploadedList = [{'fileurl': this.value}]
                    }
                },
                field_value: function(val){
                    this.syncVModel()
                }
            },
            computed: {},
            data: function () {
                return {
                    field_value: '',
                    name: '',
                    fieldid: '',
                    options: '',
                    upload_callback: 'UPLOAD_VIDEO_',
                    uploadedList: []
                }
            },
            mounted: function () {
                this.name = this.config.name || ''
                this.fieldid = this.config.fieldid || ''
                this.field_value = this.config.default || ''
                this.options = this.config.setting.options || ''
                this.syncVModel()

                this.upload_callback = this.upload_callback + this.fieldid
                window.addEventListener(this.upload_callback, this.onUploadedCallback.bind(this));
            },
            beforeDestroy: function(){
                window.removeEventListener(this.upload_callback, this.onUploadedCallback.bind(this));
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    this.$emit('change', this.field_value)
                },
                gotoUploadPanel: function () {
                    layer.open({
                        type: 2,
                        title: '',
                        closeBtn: false,
                        content: "{:api_url('common/upload.panel/videoUpload')}?max_upload=1&callback="+this.upload_callback,
                        area: ['720px', '550px'],
                    })
                },
                // 上传回调
                onUploadedCallback: function (event) {
                    var files = event.detail.files;
                    if (files && files.length > 0) {
                        this.uploadedList = [files[0]]
                        this.field_value = files[0]['fileurl']
                    }
                },
                // 删除
                deleteVideoItem: function (index) {
                    this.uploadedList.splice(index, 1)
                    this.field_value = ''
                },
                // 预览
                previewVideoItem: function (index) {
                    window.open(this.uploadedList[index]['fileurl'])
                }
            }
        });
    })
</script>