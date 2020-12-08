<script type="text/x-template" id="default-islink">
    <div class="default-islink">
        <el-form-item :label="field.name">

            <el-radio v-model="field_data[field.field]" :label="1">是</el-radio>
            <el-radio v-model="field_data[field.field]" :label="0">否</el-radio>

        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-islink', {
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
            template: '#default-islink',
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