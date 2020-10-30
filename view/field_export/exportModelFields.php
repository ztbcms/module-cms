<script src="/statics/admin/clipboard/clipboard.min.js"></script>
<div id="app">
    <el-card>
        <h4>数据字典</h4>
        <div class="h_a" >
            <el-button size="small" type="success" @click="runBack">
                返回列表
            </el-button>
            <el-button type="" size="small"  @click="initClipboard" id="btn_copy_text" data-clipboard-target="#previewtext">
                复制内容
            </el-button>
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
                exportInfos: []
            },
            mounted: function(){
                this.initClipboard();
                this.getData();
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
                    var data = {}
                    if(that.getUrlQuery('modelid')){
                        data['modelid'] = that.getUrlQuery('modelid');
                    }
                    $.ajax({
                        url: '{:api_url("cms/FieldExport/getExportModelFieldsInfo")}',
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
