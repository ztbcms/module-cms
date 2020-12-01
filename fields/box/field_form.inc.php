<el-form-item label="选项列表" prop="formData.setting.options">
    <el-input type="textarea" v-model="formData.setting.options"  :autosize="{minRows: 8, maxRows: 8}" >
    </el-input>
    <span>选项名称1|选项值1</span>
</el-form-item>

<el-form-item label="选项类型" prop="formData.setting.boxtype">
    <el-radio v-model="formData.setting.boxtype" label="radio">单选按钮</el-radio>
    <el-radio v-model="formData.setting.boxtype" label="checkbox">复选框</el-radio>
    <el-radio v-model="formData.setting.boxtype" label="select">下拉框</el-radio>
    <el-radio v-model="formData.setting.boxtype" label="multiple">多选列表框</el-radio>
</el-form-item>

<el-form-item label="字段类型" prop="formData.setting.fieldtype">
    <el-select v-model="formData.setting.fieldtype" placeholder="请选择" :style="{width: '100%'}">
        <el-option label="字符 VARCHAR" value="varchar"></el-option>
        <el-option label="整数 TINYINT(3)" value="tinyint"></el-option>
        <el-option label="整数 SMALLINT(5)" value="smallint"></el-option>
        <el-option label="整数 MEDIUMINT(8)" value="mediumint"></el-option>
        <el-option label="整数 INT(10)" value="int"></el-option>
    </el-select>

    <el-radio v-model="formData.setting.minnumber" label="-1">整数</el-radio>
    <el-radio v-model="formData.setting.minnumber" label="1">正整数</el-radio>
</el-form-item>

<el-form-item label="每列宽度" >
    <el-input v-model="formData.setting.width" placeholder="请输入每列宽度" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="高度" >
    <el-input v-model="formData.setting.size" placeholder="请输入高度" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="默认值" >
    <el-input v-model="formData.setting.defaultvalue" placeholder="请输入默认值" clearable :style="{width: '100%'}"></el-input>
</el-form-item>

<el-form-item label="输出格式" >
    <el-radio v-model="formData.setting.outputtype" label="1">输出选项值</el-radio>
    <el-radio v-model="formData.setting.outputtype" label="0">输出选项名称</el-radio>
</el-form-item>