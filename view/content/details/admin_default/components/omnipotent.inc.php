<script type="text/x-template" id="default-omnipotent">
    <div class="default-omnipotent">
        <el-form-item :label="field.name" required>
            <el-input v-model="field_data[field.field]" :placeholder="'请输入'+field.name" clearable :style="{width: '100%'}"  @change="handleChange" disabled>
            </el-input>
        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-omnipotent', {
            props: {
                field: {
                    type: Object,
                    defalut :function() {
                        return {};
                    }
                },
                field_data : {
                    type: Object,
                    defalut : function() {
                        return {};
                    }
                }
            },
            watch: {

            },
            template: '#default-omnipotent',
            data: function() {
                return {

                }
            },
            created: function() {

            },
            mounted:function() {

            },
            methods: {
                handleChange :function(code) {

                }
            }
        });
    })
</script>