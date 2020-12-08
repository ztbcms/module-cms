<script type="text/x-template" id="default-author">
    <div>
        <div class="block">
            <el-form-item :label="field.name">
                <el-input v-model="field_data[field.field]" :placeholder="'请输入'+field.name" clearable :style="{width: '100%'}"  @change="handleChange">
                </el-input>
            </el-form-item>
        </div>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-author', {
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
            template: '#default-author',
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