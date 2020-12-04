<el-form-item :label="item.name">
    <template v-if="formData[item.field] != ''">
        <div class="imgListItem">
            <img :src="formData[item.field]" style="width: 120px;height: 120px;">
            <div class="deleteMask" @click="uploadImg(item.field,'0')">
                <span style="line-height: 120px;font-size: 22px" class="el-icon-upload"></span>
            </div>
        </div>
    </template>
    <template v-else>
        <div class="imgListItem">
            <div @click="uploadImg(item.field,'0')" style="width: 120px;height: 120px;text-align: center;">
                <span style="line-height: 120px;font-size: 22px" class="el-icon-plus"></span>
            </div>
        </div>
    </template>
</el-form-item>