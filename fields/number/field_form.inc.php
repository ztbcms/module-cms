<el-form-item label="取值范围" >
    <el-input v-model="formData.setting.minnumber" placeholder="最小取值" clearable :style="{width: '20%'}">
    </el-input>
    -
    <el-input v-model="formData.setting.maxnumber" placeholder="最大取值" clearable :style="{width: '20%'}">
    </el-input>
</el-form-item>

<el-form-item label="小数位数" >
    <el-select v-model="formData.setting.decimaldigits" placeholder="请选择" :style="{width: '100%'}">
        <el-option label="自动" value="-1"></el-option>
        <el-option label="0" value="0" selected></el-option>
        <el-option label="1" value="1"></el-option>
        <el-option label="2" value="2"></el-option>
        <el-option label="3" value="3"></el-option>
        <el-option label="4" value="4"></el-option>
        <el-option label="5" value="5"></el-option>
    </el-select>
</el-form-item>

<el-form-item label="输入框长度" >
    <el-input v-model="formData.setting.size" placeholder="输入框长度" clearable :style="{width: '100%'}">
    </el-input>
</el-form-item>

<el-form-item label="默认值" >
    <el-input v-model="formData.setting.defaultvalue" placeholder="默认值" clearable :style="{width: '100%'}">
    </el-input>
</el-form-item>