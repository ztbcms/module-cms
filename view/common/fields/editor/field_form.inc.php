<script type="text/x-template" id="field-form-editor">
    <div class="field-form-editor">
        <el-form-item :label="name">
            <div style="line-height: 0;">
                <textarea id="editor_content" style="height: 500px;width: 390px;"></textarea>
            </div>

        </el-form-item>
    </div>
</script>
<script>
    $(function () {
        Vue.component('field-form-editor', {
            template: '#field-form-editor',
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
                    content: '',
                    editorInstance: null

                }
            },
            mounted: function () {
                this.editorInstance = UE.getEditor('editor_content')
                this.editorInstance.addListener('contentChange', this.onEditorContentChange.bind(this))
                this.name = this.config.name || ''
                if (this.value !== undefined) {
                    this.field_value = this.value
                } else {
                    this.field_value = ''
                }
                this.syncVModel()
            },
            methods: {
                // 向父组件更新绑定值
                syncVModel: function () {
                    this.$emit('change', this.field_value)
                },
                onEditorContentChange: function(editor){
                    this.field_value = this.editorInstance.getContent()
                }
            }
        });
    })
</script>