<script type="text/x-template" id="field-setting-radio">
    <div class="field-setting-radio">
        <el-form-item label="选项">
            <el-input v-model="options" type="textarea" :rows="4" placeholder="请输入内容，每行一个选项，用|分割" clearable ></el-input>
        </el-form-item>
        <el-form-item label="默认值">
            <el-input v-model="default_value" clearable></el-input>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-radio', {
            template: '#field-setting-radio',
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
                options: function (val) {
                    this.setting.options = val
                    this.syncVModel()
                }
            },
            data: function () {
                return {
                    default_value: '',
                    options: ''
                }
            },
            mounted: function () {
                this.default_value = this.setting.default_value || ''
                this.options = this.setting.options || ''
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        default_value: this.default_value,
                        options: this.options
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>