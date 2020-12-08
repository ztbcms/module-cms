<script type="text/x-template" id="default-editor">
    <div>
        <div class="block">
            <el-form-item :label="field.name">
                <div style="line-height: 0;">
                <textarea :id="field.field" style="height: 400px;width: 375px">{{ field_data[field.field] }}</textarea>
                </div>
            </el-form-item>
        </div>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-editor', {
            props: {
                field: {
                    type: Object,
                    defalut:function() {
                        return {};
                    }
                },
                field_data : {
                    type: Object,
                    defalut:function() {
                        return {};
                    }
                }
            },
            watch: {

            },
            template: '#default-editor',
            data: function() {
                return {

                }
            },
            created: function() {

            },
            mounted:function() {

            },
            methods: {
                handleChange:function() {

                }
            }
        });
    })
</script>