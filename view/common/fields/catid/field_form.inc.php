<script type="text/x-template" id="field-form-catid">
    <div class="field-form-catid">
        <el-form-item :label="name">

            <el-select v-model="field_value" placeholder="请选择上级栏目" :style="{width: '100%'}">
                <el-option label="作为一级栏目" value="0"></el-option>
                <el-option
                        v-for="item in categoryList"
                        :key="item.id"
                        :label="item.catname"
                        :value="item.catid">
                    <template v-for="i in item.level * 2"><span>&nbsp;</span></template>
                    <template v-if="item.level > 0"><span> ∟</span></template>
                    <span>{{ item.catname }}</span>
                </el-option>
            </el-select>

        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-form-catid', {
            template: '#field-form-catid',
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
                },
                category_list: [] // 栏目列表
            },
            watch: {
                value: function(){
                    this.field_value = this.value
                },
                field_value: function(val){
                    this.syncVModel()
                }
            },
            computed: {},
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