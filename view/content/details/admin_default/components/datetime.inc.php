<script type="text/x-template" id="default-datetime">
    <div class="default-datetime">
        <el-form-item :label="field.name">
            <el-date-picker
                    v-model="field_data[field.field]"
                    type="datetime"
                    format="yyyy-MM-dd HH:mm"
                    value-format="yyyy-MM-dd HH:mm"
                    placeholder="选择日期时间">
            </el-date-picker>
        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-datetime', {
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
            template: '#default-datetime',
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