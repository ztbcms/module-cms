<script type="text/x-template" id="field-setting-number">
    <div class="field-setting-number">
        <el-form-item label="默认值">
            <el-input v-model="default_value" clearable></el-input>
        </el-form-item>
        <el-form-item label="小数点位数">
            <el-select v-model="decimals_amount" placeholder="请选择">
                <el-option label="0" value="0"></el-option>
                <el-option label="1" value="1"></el-option>
                <el-option label="2" value="2"></el-option>
                <el-option label="3" value="3"></el-option>
                <el-option label="4" value="4"></el-option>
                <el-option label="5" value="5"></el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="字段类型">
            <el-select v-model="sql_type" placeholder="请选择">
                <el-option label="DECIMAL（小数）" value="DECIMAL"></el-option>
                <el-option label="INT（整数）" value="INT" :disabled="disable_int"></el-option>
                <el-option label="BIGINT（整数）" value="BIGINT" :disabled="disable_int"></el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="整数类型">
            <el-select v-model="integer_type" placeholder="请选择" :disabled="sql_type == 'DECIMAL'">
                <el-option label="无符号(UNSIGNED)" value="0"></el-option>
                <el-option label="有符号" value="1"></el-option>
            </el-select>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-number', {
            template: '#field-setting-number',
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
                sql_type: function (val) {
                    this.setting.sql_type = val
                    this.setting.integer_type = '0'
                    this.syncVModel()
                },
                decimals_amount: function (val) {
                    val = parseInt(val) || 0
                    this.setting.decimals_amount = val
                    this.disable_int = val > 0
                    if (this.disable_int) {
                        this.sql_type = 'DECIMAL'
                    }
                    this.syncVModel()
                },
                integer_type: function (val) {
                    this.setting.integer_type = val
                    this.syncVModel()
                }
            },
            data: function () {
                return {
                    default_value: '',
                    sql_type: '',
                    decimals_amount: '',
                    integer_type: '0',
                    disable_int: false
                }
            },
            computed: {},
            mounted: function () {
                this.default_value = this.setting.default_value || ''
                this.sql_type = this.setting.sql_type || 'INT'
                this.decimals_amount = this.setting.decimals_amount || 0
                this.integer_type = this.setting.integer_type || '0'
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        default_value: this.default_value,
                        sql_type: this.sql_type,
                        decimals_amount: this.decimals_amount,
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>