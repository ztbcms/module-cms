<el-form-item :label="item.name">

<!--    单选框-->
    <div v-if="item.setting.boxtype == 'radio'">
        <el-radio v-for="(items,key) in item.setting.option_list"
                  v-model="formData[item.field]"
                  :label="key">
            {{ items }}
        </el-radio>
    </div>

</el-form-item>