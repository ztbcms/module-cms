<script type="text/x-template" id="list-item-video">
    <div class="list-item-video">
        <template v-if="field_value">
            <el-link :href="field_value" type="primary" target="_blank">点击预览</el-link>
        </template>
    </div>
</script>
<script>
    $(function () {
        Vue.component('list-item-video', {
            template: '#list-item-video',
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
                }
            },
            data: function () {
                return {
                    field_value: ''
                }
            },
            computed: {},
            mounted: function () {
                this.field_value = this.value
            },
            methods: {}
        });
    })
</script>