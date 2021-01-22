<script type="text/x-template" id="field-setting-images">
    <div class="field-setting-images">
        <el-form-item label="启用水印">
            <el-radio v-model="enable_watermark" label="0">关闭</el-radio>
            <el-radio v-model="enable_watermark" label="1">启用</el-radio>
        </el-form-item>
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
        Vue.component('field-setting-images', {
            template: '#field-setting-images',
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
                    enable_watermark: '0',
                    max_amount: 0,
                    limit_amount: 17
                }
            },
            computed: {},
            mounted: function () {
                this.enable_watermark = this.setting.enable_watermark || '0'
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        enable_watermark: this.enable_watermark,
                        max_amount: this.max_amount
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>