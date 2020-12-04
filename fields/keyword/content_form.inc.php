<el-form-item :label="item.name">
    <el-input type="textarea" v-model="formData[item.field]"  :autosize="{minRows: 8, maxRows: 8}" >
    </el-input>

    <span>多关键词之间用空格隔开</span>
</el-form-item>