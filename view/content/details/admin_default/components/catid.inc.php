<script type="text/x-template" id="default-catid">
    <div>
        <div class="block">
            <el-form-item :label="field.name" required>
                <el-input v-model="field_data[field.field]" :placeholder="'请输入'+field.name" clearable :style="{width: '100%'}"  @change="handleChange" disabled>
                </el-input>
            </el-form-item>
        </div>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-catid', {
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
            template: '#default-catid',
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