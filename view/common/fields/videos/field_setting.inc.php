<script type="text/x-template" id="field-setting-videos">
    <div class="field-setting-videos">
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
        Vue.component('field-setting-videos', {
            template: '#field-setting-videos',
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
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>