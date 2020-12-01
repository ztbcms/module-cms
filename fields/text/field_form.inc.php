<el-form-item label="文本框长度" prop="formData.setting.size">
    <el-input v-model="formData.setting.size" clearable :style="{width: '100%'}"></el-input>
    <span>px</span>
</el-form-item>

<el-form-item label="默认值" prop="formData.setting.defaultvalue">
    <el-input v-model="formData.setting.defaultvalue" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="是否为密码框" prop="formData.setting.ispassword">
    <el-radio v-model="formData.setting.ispassword" label="1">是</el-radio>
    <el-radio v-model="formData.setting.ispassword" label="0">否</el-radio>
</el-form-item>