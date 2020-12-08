<script type="text/x-template" id="default-tags">
    <div class="default-tags">
        <el-form-item :label="field.name">
            <el-input v-model="field_data[field.field]" :placeholder="'请输入'+field.name" clearable :style="{width: '100%'}"  @change="handleChange">
            </el-input>
        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-tags', {
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
            template: '#default-tags',
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