<el-form-item :label="item.name" required >
    <el-radio v-model="formData[item.field]" label="1">是</el-radio>
    <el-radio v-model="formData[item.field]" label="0">否</el-radio>
</el-form-item>