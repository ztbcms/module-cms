<script type="text/x-template" id="list-item-image">
    <div class="list-item-image">

        <el-image
            v-if="field_value"
            style="width: 80px;"
            :src="field_value"
            :preview-src-list="[field_value]"
            fit="cover"
            lazy>
        </el-image>

    </div>
</script>
<script>
    $(function () {
        Vue.component('list-item-image', {
            template: '#list-item-image',
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