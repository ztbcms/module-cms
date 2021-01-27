<script type="text/x-template" id="field-setting-editor">
    <div class="field-setting-editor">
        <el-form-item label="默认值">
            <el-input v-model="default_value" clearable></el-input>
        </el-form-item>
        <el-form-item label="字段类型">
            <el-select v-model="sql_type" placeholder="请选择">
                <el-option label="TEXT" value="TEXT"></el-option>
                <el-option label="MEDIUMTEXT" value="MEDIUMTEXT"></el-option>
                <el-option label="LONGTEXT" value="LONGTEXT"></el-option>
            </el-select>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-editor', {
            template: '#field-setting-editor',
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
                },
                sql_type: function (val) {
                    this.setting.sql_type = val
                    this.syncVModel()
                }
            },
            data: function () {
                return {
                    default_value: '',
                    sql_type: ''
                }
            },
            mounted: function () {
                this.default_value = this.setting.default_value || ''
                this.sql_type = this.setting.sql_type || 'MEDIUMTEXT'
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        default_value: this.default_value,
                        sql_type: this.sql_type
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>