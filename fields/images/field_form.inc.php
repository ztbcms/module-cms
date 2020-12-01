<el-form-item label="允许上传的图片类型" >
    <el-input v-model="formData.setting.upload_allowext" placeholder="请输入允许上传的图片类型" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="是否从已上传中选择">
    <el-radio v-model="formData.setting.isselectimage" label="1">是</el-radio>
    <el-radio v-model="formData.setting.isselectimage" label="0">否</el-radio>
</el-form-item>

<el-form-item label="允许同时上传的个数" >
    <el-input v-model="formData.setting.upload_number" placeholder="请输入允许同时上传的个数" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="是否在图片上添加水印">
    <el-radio v-model="formData.setting.watermark" label="1">是</el-radio>
    <el-radio v-model="formData.setting.watermark" label="0">否</el-radio>
</el-form-item>