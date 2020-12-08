<script type="text/x-template" id="default-template">
    <div class="default-template">
        <el-form-item :label="field.name">
            <el-input v-model="field_data[field.field]" :autosize="{minRows: 8, maxRows: 8}" :placeholder="'请输入'+field.name" clearable type="textarea"  @change="handleChange">
            </el-input>
        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-template', {
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
            template: '#default-template',
            data: function() {
                return {

                }
            },
            created: function() {

            },
            mounted:function() {

            },
            methods: {
                handleChange:function(code) {

                }
            }
        });
    })
</script>