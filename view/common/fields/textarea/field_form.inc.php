<script type="text/x-template" id="field-form-textarea">
    <div class="field-form-textarea">
        <el-form-item :label="name">
            <el-input v-model="field_value" clearable type="textarea" :rows="3"></el-input>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-form-textarea', {
            template: '#field-form-textarea',
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