<el-form-item :label="item.name" required >
    <el-input v-model="formData[item.field]" type="number" :placeholder="'请输入'+item.name" clearable
              :style="{width: '100%'}">
    </el-input>
</el-form-item>