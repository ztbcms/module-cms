<el-form-item label="默认值" prop="formData.setting.defaultvalue">
    <el-input v-model="formData.setting.defaultvalue" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="文本框长度" prop="formData.setting.width">
    <el-input v-model="formData.setting.width" clearable :style="{width: '100%'}"></el-input>
    <span>px</span>
</el-form-item>