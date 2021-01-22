<script type="text/x-template" id="field-form-editor">
    <div class="field-form-editor">
        <el-form-item :label="name">
            <div style="line-height: 0;">
                <textarea :id="'editor_content_' + fieldid" style="height: 500px;width: 390px;"></textarea>
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
                    fieldid: '',
                    editorInstance: null
                }
            },
            created: function(){
                this.name = this.config.name || ''
                // 必须在这里初始化，否则 mounted 时 Editor 无法找到对应的ID
                this.fieldid = this.config.fieldid || ''

                if (this.value !== undefined) {
                    this.field_value = this.value
                    var that = this
                    this.editorInstance.ready(function(){
                        //设置编辑器的内容
                        that.editorInstance.setContent(that.field_value);
                    });
                } else {
                    this.field_value = ''
                }
            },
            mounted: function () {
                var that = this
                this.editorInstance = UE.getEditor('editor_content_' + this.fieldid)
                this.editorInstance.addListener('contentChange', this.onEditorContentChange.bind(this))
                this.editorInstance.ready(function(){
                    //设置编辑器的内容
                    that.editorInstance.setContent(that.field_value);
                });
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