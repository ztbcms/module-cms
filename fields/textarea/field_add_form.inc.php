<el-form-item label="文本域宽度" prop="formData.setting.width">
    <el-input v-model="formData.setting.width" clearable :style="{width: '100%'}"></el-input>
    <span>%</span>
</el-form-item>

<el-form-item label="文本域高度" prop="formData.setting.height">
    <el-input v-model="formData.setting.height" clearable :style="{width: '100%'}"></el-input>
    <span>px</span>
</el-form-item>

<el-form-item label="默认值" prop="formData.setting.defaultvalue">
    <el-input type="textarea" v-model="formData.setting.defaultvalue"  :autosize="{minRows: 8, maxRows: 8}" >
    </el-input>
</el-form-item>

<el-form-item label="是否允许Html" prop="formData.setting.enablehtml">
    <el-radio v-model="formData.setting.enablehtml" label="1">是</el-radio>
    <el-radio v-model="formData.setting.enablehtml" label="0">否</el-radio>
</el-form-item>

<el-form-item label="字段类型" prop="formData.setting.fieldtype">
    <el-select v-model="formData.setting.fieldtype" placeholder="请选择" :style="{width: '100%'}">
        <el-option label="小型字符型(TEXT)" value="text"></el-option>
        <el-option label="中型字符型(MEDIUMTEXT)" value="mediumtext"></el-option>
        <el-option label="大型字符型(LONGTEXT)" value="longtext"></el-option>
    </el-select>
</el-form-item>

