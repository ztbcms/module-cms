<script type="text/x-template" id="field-form-select">
    <div class="field-form-select">
        <el-form-item :label="name">
            <el-select v-model="field_value" placeholder="请选择">
                <template v-for="(item, index) in option_list">
                    <el-option
                            :key="index"
                            :label="item.name"
                            :value="item.value">
                    </el-option>
                </template>
            </el-select>
        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-form-select', {
            template: '#field-form-select',
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
            computed: {
                option_list: function(){
                    var result = []
                    var arr = this.options.split('\n')
                    for(var i=0; i<arr.length; i++){
                        var item = arr[i].trim()
                        if(item){
                            var sp = item.split('|')
                            if(sp[0].trim() && sp[1].trim()){
                                result.push({
                                    name: sp[0].trim(),
                                    value: sp[1].trim()
                                })
                            }
                        }
                    }
                    return result
                }
            },
            data: function () {
                return {
                    field_value: '',
                    name: '',
                    options: '',

                }
            },
            mounted: function () {
                this.name = this.config.name || ''
                this.field_value = this.config.default || ''
                this.options = this.config.setting.options || ''
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