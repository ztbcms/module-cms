<el-form-item :label="item.name" required>
    <div style="line-height: 0;">
        <textarea :id="item.field" style="height: 400px;width: 375px">{{ formData[item.field] }}</textarea>
    </div>
</el-form-item>
