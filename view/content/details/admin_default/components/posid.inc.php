<script type="text/x-template" id="default-posid">
    <div class="default-posid">
        <el-form-item :label="field.name" required>

        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-posid', {
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
            template: '#default-posid',
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