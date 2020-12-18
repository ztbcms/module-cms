<script type="text/x-template" id="field-setting-text">
    <div class="field-setting-text">
        <el-form-item label="默认值">
            <el-input v-model="default_value" clearable></el-input>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-text', {
            template: '#field-setting-text',
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
                }
            },
            data: function () {
                return {
                    default_value: '',
                }
            },
            mounted: function () {
                this.default_value = this.setting.default_value || ''
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        default_value: this.default_value
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>