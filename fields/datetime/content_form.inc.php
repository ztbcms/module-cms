<el-form-item :label="item.name">
    <el-date-picker
        v-model="formData[item.field]"
        type="datetime"
        format="yyyy-MM-dd HH:mm"
        value-format="yyyy-MM-dd HH:mm"
        placeholder="选择日期时间">
    </el-date-picker>
</el-form-item>
