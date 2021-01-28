<script type="text/x-template" id="list-item-videos">
    <div class="list-item-videos">

        <template v-for="(item, index) in field_value">
            <el-link v-if="field_value" :href="item.url" type="primary" target="_blank">{{ item.name }}</el-link>
        </template>

    </div>
</script>
<script>
    $(function () {
        Vue.component('list-item-videos', {
            template: '#list-item-videos',
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
                    field_value: []
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