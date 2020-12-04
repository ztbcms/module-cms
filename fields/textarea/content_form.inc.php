<el-form-item :label="item.name">
    <el-input type="textarea" v-model="formData[item.field]"  :autosize="{minRows: 8, maxRows: 8}" >
    </el-input>
</el-form-item>