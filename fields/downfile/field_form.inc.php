<el-form-item label="文本框长度" prop="formData.setting.width">
    <el-input v-model="formData.setting.width" clearable :style="{width: '100%'}"></el-input>
    <span>px</span>
</el-form-item>

<el-form-item label="默认值" prop="formData.setting.defaultvalue">
    <el-input v-model="formData.setting.defaultvalue" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="允许上传的图片类型" >
    <el-input v-model="formData.setting.upload_allowext" placeholder="请输入允许上传的图片类型" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="是否在图片上添加水印">
    <el-radio v-model="formData.setting.watermark" label="1">是</el-radio>
    <el-radio v-model="formData.setting.watermark" label="0">否</el-radio>
</el-form-item>

<el-form-item label="是否从已上传中选择">
    <el-radio v-model="formData.setting.isselectimage" label="1">是</el-radio>
    <el-radio v-model="formData.setting.isselectimage" label="0">否</el-radio>
</el-form-item>

<el-form-item label="下载统计字段">
    <el-input v-model="formData.setting.statistics" clearable :style="{width: '100%'}"></el-input>
    <span>下载次数统计字段只能在主表！</span>
</el-form-item>

<el-form-item label="文件链接方式">
    <el-radio v-model="formData.setting.downloadlink" label="1">链接到下载跳转页面</el-radio>
    <el-radio v-model="formData.setting.downloadlink" label="0">链接到真实软件地址 （无法进行验证和统计）</el-radio>
</el-form-item>