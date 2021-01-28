<script type="text/x-template" id="list-item-images">
    <div class="list-item-images">

        <template v-for="item in field_value">
            <el-image
                    v-if="item"
                    style="width: 40px;margin: 2px"
                    :src="item"
                    :preview-src-list="[item]"
                    fit="cover"
                    lazy
                    >
            </el-image>
        </template>

    </div>
</script>
<script>
    $(function () {
        Vue.component('list-item-images', {
            template: '#list-item-images',
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