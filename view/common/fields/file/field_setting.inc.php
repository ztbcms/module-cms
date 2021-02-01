<script type="text/x-template" id="field-setting-file">
    <div class="field-setting-file">
       <small>暂无</small>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-setting-file', {
            template: '#field-setting-file',
            model: {
                // 指定接收 v-model 值/修改事件
                prop: 'setting',
                event: 'change'
            },
            props: {
                setting: {
                    type: Object,
                    default: function () {
                        return {};
                    }
                },
            },
            watch: {
                default_value: function (val) {
                    this.setting.default_value = val
                    this.syncVModel()
                }
            },
            data: function () {
                return {
                    default_value: '',
                }
            },
            computed: {},
            mounted: function () {
                this.default_value = this.setting.default_value || ''
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    var setting = {
                        default_value: this.default_value,
                    }
                    this.$emit('change', setting)
                }
            }
        });
    })
</script>