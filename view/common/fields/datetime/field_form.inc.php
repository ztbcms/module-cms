<script type="text/x-template" id="field-form-datetime">
    <div class="field-form-datetime">
        <el-form-item :label="name">
            <el-date-picker
                    v-model="field_value"
                    type="datetime"
                    :format="date_format"
                    :value-format="date_format"
                    placeholder="选择日期时间">
            </el-date-picker>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-form-datetime', {
            template: '#field-form-datetime',
            model: {
                // 指定接收 v-model 值/修改事件
                prop: 'value',
                event: 'change'
            },
            props: {
                value: {},
                config: {
                    type: Object,
                    default: function () {
                        return {};
                    }
                }
            },
            watch: {
                value: function(){
                    this.field_value = this.value
                },
                field_value: function(val){
                    this.syncVModel()
                }
            },
            data: function () {
                return {
                    field_value: '',
                    name: '',
                    format: 'Y-m-d H:i:s'
                }
            },
            computed: {
                date_format: function () {
                    switch (this.format) {
                        case 'Y-m-d H:i:s':
                            return 'yyyy-MM-dd HH:mm:ss';
                        case 'Y-m-d H:i':
                            return 'yyyy-MM-dd HH:mm';
                        case 'Y-m-d':
                            return 'yyyy-MM-dd';
                        default:
                            return 'yyyy-MM-dd HH:mm:ss';
                    }
                }
            },
            mounted: function () {
                this.name = this.config.name || ''
                this.format = this.config.setting.format || 'Y-m-d H:i:s'
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    this.$emit('change', this.field_value)
                }
            }
        });
    })
</script>