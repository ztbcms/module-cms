<script type="text/x-template" id="list-item-checkbox">
    <div class="list-item-checkbox">

        <template v-for="(item, index) in field_value">
            <span :key="index">{{ item }}&nbsp;</span>
        </template>

    </div>
</script>
<script>
    $(function () {
        Vue.component('list-item-checkbox', {
            template: '#list-item-checkbox',
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