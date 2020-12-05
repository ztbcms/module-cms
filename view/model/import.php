<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>模型导入</h3>
        <el-row>
            <el-col :span="8">
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="100px">
                        <el-form-item label="模型名称：">
                            <el-input v-model="form.name"></el-input>
                            <span>为空时按配置文件中的</span>
                        </el-form-item>
                        <el-form-item label="模型表键名：">
                            <el-input v-model="form.tablename"></el-input>
                            <span>为空时按配置文件中的</span>
                        </el-form-item>
                        <el-form-item label="配置文件：">
                            <input type="file" class="file" id="file_obj" name="file_obj"
                                   accept=".txt"/>
                            <br>
                            <span>只支持.txt文件上传</span>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">导入模型</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
            <el-col :span="16">
                <div class="grid-content "></div>
            </el-col>
        </el-row>


    </el-card>
</div>

<style>

</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                form:{
                    name: '',
                    tablename: '',
                    file: null
                }
            },
            watch: {},
            filters: {},
            methods: {
                onSubmit: function () {
                    var that = this;
                    var file_obj = document.getElementById('file_obj').files[0];
                    if (typeof (file_obj) == "undefined") {
                        layer.msg("请选择需要导入的文件");
                        return;
                    }

                    var fd = new FormData();
                    fd.append('file', file_obj);
                    fd.append('name', this.form.name);
                    fd.append('tablename', this.form.tablename);

                    $.ajax({
                        url: '{:api_url("/cms/model/import")}',
                        type: 'POST',
                        data: fd,
                        processData: false,  //tell jQuery not to process the data
                        contentType: false,  //tell jQuery not to set contentType
                        success: function (result) {
                            layer.msg(result.msg);
                            that.onCancel(1000);
                        }
                    })
                },
                onCancel: function (time) {
                    if (window !== window.parent) {
                        setTimeout(function () {
                            window.parent.layer.closeAll();
                        }, time);
                    }
                }
            },
            mounted: function () {

            }

        })
    })
</script>

