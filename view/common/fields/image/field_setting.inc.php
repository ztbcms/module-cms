<script type="text/x-template" id="field-setting-image">
    <div class="field-setting-image">
        <el-form-item label="默认图片">
            <el-input v-model="default_value" clearable placeholder="图片链接"></el-input>
        </el-form-item>
        <el-form-item label="启用水印">
            <el-radio v-model="enable_watermark" label="0">关闭</el-radio>
            <el-radio v-model="enable_watermark" label="1">启用</el-radio>
        </el-form-item>

    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-image', {
            template: '#field-setting-image',
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