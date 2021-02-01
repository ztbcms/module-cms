<script type="text/x-template" id="field-setting-files">
    <div class="field-setting-files">

        <el-form-item label="限制个数">
            <el-select v-model="max_amount" placeholder="请选择">
                <template v-for="(item, index) in limit_amount">
                    <el-option :label="index" :value="index" :key="index"></el-option>
                </template>
            </el-select>
            <small> * 0为不限制</small>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-files', {
            template: '#field-setting-files',
            model: {
                // 指定接收 v-model 值/修改事件
                prop: 'setting',
                event: 'change'
            },
            props: {
                setting: {
                    type: Object,
                    default: function () {
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
                    max_amount: 0,
                    limit_amount: 17
                }
            },
            computed: {},
            mounted: function () {
                this.default_value = this.setting.default_value || ''
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        default_value: this.default_value,
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>