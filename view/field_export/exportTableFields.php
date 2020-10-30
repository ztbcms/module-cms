<script src="/statics/admin/clipboard/clipboard.min.js"></script>
<div id="app">
    <el-card>
        <h4>数据字典</h4>
        <div class="h_a" >
            <el-button size="" type="success" @click="runBack">
                返回列表
            </el-button>
            <el-button type="" size=""  @click="initClipboard" id="btn_copy_text" data-clipboard-target="#previewtext">
                复制内容
            </el-button>
        </div>
        <div style="margin-top: 20px;">
            <el-row>
                <el-col :span="12">
                    <el-form>
                        <div style="width: 60%;display: inline-block">
                            <label for="">表名：</label>
                            <el-input v-model="tablename" placeholder="请输入完整表名" style="width: 80%"></el-input>
                        </div>
                        <div style="width: 20%;display: inline-block">
                            <el-button @click="getData" type="">确认</el-button>
                        </div>
                    </el-form>
                </el-col>
            </el-row>
        </div>
        <div class="table_full">
            <pre id="previewtext" style="width: 100%;height: auto">{{ previewText }}</pre>
        </div>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                exportInfos: [],
                tablename: '',
            },
            mounted: function(){
                this.initClipboard();
            },
            computed: {
                previewText: function (){
                    var result = '';
                    if(this.exportInfos && this.exportInfos.length > 0){
                        this.exportInfos.forEach(function(exportInfo){
                            result += '## ' + exportInfo.tablename + ' ' + exportInfo.table_name;
                            result += '\n\n';
                            result += '| 字段名 | 字段别名 | 类型 | 说明 \n' +
                                '|:--- |:--- |:--- |:--- | \n';
                            var fields = exportInfo.fields, tips = '';
                            for(var i=0; i < fields.length; i++ ){
                                tips = fields[i]['tips'] ?  fields[i]['tips'] : '/' ;
                                result += '| ' + fields[i]['field'] + ' | ' + fields[i]['name'] + ' | ' + fields[i]['type'] + ' | '+ tips + ' | \n'
                            }

                            result += '\n\n';
                        })
                    }
                    return result;
                }
            },
            methods: {
                initClipboard: function(){
                    var clipboard = new ClipboardJS('#btn_copy_text')
                    clipboard.on('success', function(e) {
                        layer.msg('已复制到剪切板')

                        e.clearSelection();
                    });
                },
                getData: function () {
                    var that = this;
                    var data = {
                        tablename: this.tablename
                    }
                    if(!this.tablename){
                        layer.msg('请输入表名')
                        return
                    }

                    $.ajax({
                        url: '{:api_url("cms/FieldExport/getExportTableFieldsInfo")}',
                        type: 'post',
                        dataType: 'json',
                        data: data,
                        success: function (res) {
                            if(res.status){
                                that.exportInfos = res.data
                            }else{
                                layer.msg(res.msg)
                            }
                        }
                    });
                },
                runBack:function () {
                    history.back()
                }
            }
        })
    });
</script>
</body>
</html>
