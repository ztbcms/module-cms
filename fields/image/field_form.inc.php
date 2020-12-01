<el-form-item label="文本框长度" >
    <el-input v-model="formData.setting.width" placeholder="请输入文本框长度" clearable :style="{width: '100%'}"></el-input> <span>px</span>
</el-form-item>

<el-form-item label="默认值" >
    <el-input v-model="formData.setting.defaultvalue" placeholder="请输入默认值" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="表单显示模式">
    <el-radio v-model="formData.setting.show_type" label="1">图片模式</el-radio>
    <el-radio v-model="formData.setting.show_type" label="0">文本框模式</el-radio>
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

<el-form-item label="自动裁减图像大小（px）">
    <el-input v-model="formData.setting.images_width" placeholder="宽" clearable :style="{width: '100%'}">
    </el-input>
    <br>
    <br>
    <el-input v-model="formData.setting.images_height" placeholder="高" clearable :style="{width: '100%'}">
    </el-input>
</el-form-item>