<script type="text/x-template" id="default-downfiles">
    <div>
        <div class="block">
            <el-form-item :label="field.name">
                <div>
                    <template v-for="(items,index) in field_data[field.field]">
                        <p style="margin-top: 0;">
                            <a :href="items" target="_blank">
                                <span style="color: #0e85d5">{{ items }}</span>
                            </a>
                            <span class="el-icon-delete" @click="deleteItem(index)"></span>
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
        Vue.component('default-downfiles', {
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
            template: '#default-downfiles',
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
                    var that = this;
                    if (files) {
                        for(var k in files){
                            that.field_data[that.field.field].push(files[k].fileurl);
                        }
                    }
                },
                deleteItem: function (index) {
                    var that = this;
                    Vue.delete(that.field_data[that.field.field], index);
                }
            }
        });
    })
</script>