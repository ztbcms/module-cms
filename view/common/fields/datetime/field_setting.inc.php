<script type="text/x-template" id="field-setting-datetime">
    <div class="field-setting-datetime">
        <el-form-item label="时间格式">
            <el-select v-model="format" placeholder="请选择" style="width: 100%">
                <el-option label="日期 (2021-1-1)" value="Y-m-d"></el-option>
                <el-option label="日期时间 (2021-1-1 8:00:00)" value="Y-m-d H:i:s"></el-option>
                <el-option label="日期时间 (2021-1-1 8:00)" value="Y-m-d H:i"></el-option>
            </el-select>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-datetime', {
            template: '#field-setting-datetime',
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
                format: function (val) {
                    this.setting.format = val
                    this.syncVModel()
                }
            },
            data: function () {
                return {
                    format: ''
                }
            },
            mounted: function () {
                this.format = this.setting.format || 'Y-m-d H:i:s'
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        format: this.format
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>