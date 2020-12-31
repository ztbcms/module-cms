<script type="text/x-template" id="field-setting-file">
    <div class="field-setting-file">
        <el-form-item label="默认文件">
            <el-input v-model="default_value" clearable placeholder="文件链接"></el-input>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-file', {
            template: '#field-setting-file',
            model: {
                // 指定接收 v-model 值/修改事件
                prop: 'setting',
                event: 'change'
            },
            props: {
                setting: {
                    type: Object,
                    defalut: function () {
                        return {};
                    }
                },
            },
            watch: {
                default_value: function (val) {
                    this.setting.default_value = val
                    this.syncVModel()
                },
                enable_watermark: function (val) {
                    this.setting.enable_watermark = val
                    this.syncVModel()
                },
            },
            data: function () {
                return {
                    default_value: '',
                    enable_watermark: '0',
                }
            },
            computed: {},
            mounted: function () {
                this.default_value = this.setting.default_value || ''
                this.enable_watermark = this.setting.enable_watermark || '0'
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        default_value: this.default_value,
                        enable_watermark: this.enable_watermark,
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>