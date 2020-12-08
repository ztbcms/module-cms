<script type="text/x-template" id="default-box">
    <div>
        <div class="block">
            <el-form-item :label="field.name" required>

                <div>
                    <el-radio v-for="(items,key) in field.setting.option_list"
                              v-model="field_data[field.field]"
                              :label="key">
                        {{ items }}
                    </el-radio>
                </div>

            </el-form-item>
        </div>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-box', {
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
            template: '#default-box',
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