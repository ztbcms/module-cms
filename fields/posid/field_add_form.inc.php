<el-form-item label="每个位置宽度" prop="formData.setting.width">
    <el-input v-model="formData.setting.width" clearable :style="{width: '100%'}"></el-input>
    <span>px</span>
</el-form-item>

<el-form-item label="默认选中项" prop="formData.setting.defaultvalue">
    <el-input v-model="formData.setting.defaultvalue" clearable :style="{width: '100%'}"></el-input>
    <span>多个之间用半角逗号隔开</span>
</el-form-item>