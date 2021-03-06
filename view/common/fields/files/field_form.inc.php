<script type="text/x-template" id="field-form-files">
    <div class="field-form-files">
        <el-form-item :label="name">
            <div>
                <template v-for="(file, index) in uploadedList">
                    <div class="imgListItem">
                        <img :src="file.filethumb" :alt="file.filename" style="width: 80px;height: 80px;">
                        <div class="deleteMask" >
                            <span style="line-height: 80px;font-size: 22px" class="el-icon-delete" @click="deleteVideoItem(index)"></span>
                            <span style="line-height: 80px;font-size: 22px" class="el-icon-zoom-in" @click="previewVideoItem(index)"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadPanel(0)" size="mini">上传</el-button>
        </el-form-item>
    </div>
</script>
<style>
    .field-form-files .imgListItem {
        height: 80px;
        border: 1px dashed #d9d9d9;
        border-radius: 6px;
        display: inline-flex;
        margin-right: 10px;
        margin-bottom: 10px;
        position: relative;
        cursor: pointer;
        vertical-align: top;
    }

    .field-form-files .deleteMask {
        position: absolute;
        top: 0;
        left: 0;
        width: 80px;
        height: 80px;
        text-align: center;
        background-color: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 40px;
        opacity: 0;
    }

    .field-form-files .deleteMask:hover {
        opacity: 1;
    }
</style>
<script>
    $(function () {
        Vue.component('field-form-files', {
            template: '#field-form-files',
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
                            filethumb: this.value[i]['thumb'] || '/statics/admin/upload/file.png',
                            fileurl: this.value[i]['url']
                        })
                    }
                    this.uploadedList = result
                },
                field_value: function(val){
                    this.syncVModel()
                }
            },
            computed: {},
            data: function () {
                return {
                    field_value: [],
                    name: '',
                    fieldid: '',
                    options: '',
                    upload_callback: 'UPLOAD_FILE_',
                    uploadedList: []
                }
            },
            mounted: function () {
                this.name = this.config.name || ''
                this.fieldid = this.config.fieldid || ''
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
                        content: "{:api_url('common/upload.panel/fileUpload')}?callback="+this.upload_callback,
                        area: ['720px', '550px'],
                    })
                },
                // 上传回调
                onUploadedCallback: function (event) {
                    var files = event.detail.files;
                    if (files && files.length > 0) {
                        for (var i = 0; i < files.length; i++) {
                            this.uploadedList.push(files[i])
                            this.field_value.push({
                                thumb: files[i]['filethumb'] || '/statics/admin/upload/file.png',
                                url: files[i]['fileurl']
                            })
                        }
                    }
                },
                // 删除
                deleteVideoItem: function (index) {
                    this.uploadedList.splice(index, 1)
                    this.field_value.splice(index, 1)
                },
                // 预览
                previewVideoItem: function (index) {
                    window.open(this.uploadedList[index]['fileurl'])
                }
            }
        });
    })
</script>