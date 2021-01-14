<script type="text/x-template" id="field-form-number">
    <div class="field-form-number">
        <el-form-item :label="name">
            <el-input v-model="field_value" clearable></el-input>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-form-number', {
            template: '#field-form-number',
            model: {
                // 指定接收 v-model 值/修改事件
                prop: 'value',
                event: 'change'
            },
            props: {
                value: {},
                config: {
                    type: Object,
                    defalut: function () {
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
                }
            },
            mounted: function () {
                this.name = this.config.name || ''
                this.field_value = this.config.default || ''
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