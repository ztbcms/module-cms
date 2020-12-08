<script type="text/x-template" id="default-textarea">
    <div>
        <div class="block">
            <el-form-item :label="field.name">
                <el-input v-model="field_data[field.field]" :autosize="{minRows: 8, maxRows: 8}" :placeholder="'请输入'+field.name" clearable type="textarea"  @change="handleChange">
                </el-input>
            </el-form-item>
        </div>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-textarea', {
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
            template: '#default-textarea',
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