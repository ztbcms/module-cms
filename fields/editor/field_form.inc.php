
<el-form-item label="后台编辑器样式" prop="issystem">
    <el-select v-model="formData.setting.toolbar" placeholder="请选择" :style="{width: '100%'}">
        <el-option label="简洁型" value="basic"></el-option>
        <el-option label="标准型" value="full"></el-option>
    </el-select>
</el-form-item>

<el-form-item label="默认值" prop="formData.setting.defaultvalue">
    <el-input type="textarea" v-model="formData.setting.defaultvalue"  :autosize="{minRows: 8, maxRows: 8}" >
    </el-input>
</el-form-item>

<el-form-item label="是否保存远程图片">
    <el-select v-model="formData.setting.enablesaveimage" placeholder="请选择" :style="{width: '100%'}">
        <el-option label="是" value="1"></el-option>
        <el-option label="否" value="0"></el-option>
    </el-select>
</el-form-item>

<el-form-item label="编辑器默认高度">
    <el-input v-model="formData.setting.height" clearable :style="{width: '100%'}"></el-input>
    <span>px</span>
</el-form-item>

<el-form-item label="字段类型" prop="formData.setting.fieldtype">
    <el-select v-model="formData.setting.fieldtype" placeholder="请选择" :style="{width: '100%'}">
        <el-option label="小型字符型(TEXT)" value="text"></el-option>
        <el-option label="中型字符型(MEDIUMTEXT)" value="mediumtext"></el-option>
        <el-option label="大型字符型(LONGTEXT)" value="longtext"></el-option>
    </el-select>
</el-form-item>