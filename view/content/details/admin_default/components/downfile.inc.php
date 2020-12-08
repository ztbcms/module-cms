<script type="text/x-template" id="default-downfile">
    <div>
        <div class="block">

            <el-form-item :label="field.name">
                <div>
                    <template v-if="field_data[field.field]">
                        <p style="margin-top: 0;">
                            <a :href="field_data[field.field]" target="_blank">
                                <span style="color: #0e85d5">{{field_data[field.field]}}</span>
                            </a>
                            <span class="el-icon-delete" @click="deleteItem()"></span>
                        </p>
                    </template>
                </div>
                <el-button type="primary" @click="gotoUploadFile('source_file')">点击上传</el-button>
            </el-form-item>
        </div>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-downfile', {
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
            template: '#default-downfile',
            data: function() {
                return {

                }
            },
            created: function() {

            },
            mounted:function() {
                window.addEventListener('ZTBCMS_UPLOAD_FILE', this.onUploadedFile.bind(this));
            },
            methods: {
                gotoUploadFile: function () {
                    layer.open({
                        type: 2,
                        title: '',
                        closeBtn: false,
                        content: '{:api_url("/common/upload.panel/fileUpload")}',
                        area: ['70%', '80%']
                    })
                },
                onUploadedFile: function (event) {
                    var files = event.detail.files;
                    if (files) {
                        this.field_data[this.field.field] = files[0].fileurl;
                    }
                },
                deleteItem: function () {
                    this.field_data[this.field.field] = '';
                }
            }
        });
    })
</script>