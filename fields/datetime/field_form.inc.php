<el-form-item label="时间格式">
    <el-radio v-model="formData.setting.fieldtype" label="date">
        日期（<?php echo date('Y-m-d'); ?>）
    </el-radio>
    <el-radio v-model="formData.setting.fieldtype" label="datetime_a">
        日期+12小时制时间（<?php echo date('Y-m-d h:i:s'); ?>）
    </el-radio>
    <el-radio v-model="formData.setting.fieldtype" label="datetime">
        日期+24小时制时间（<?php echo date('Y-m-d H:i:s'); ?>）
    </el-radio>

    <el-radio v-model="formData.setting.fieldtype" label="int">
        整数 显示格式
    </el-radio>

    <el-select v-model="formData.setting.format" placeholder="请选择" :style="{width: '50%'}">
        <el-option label="12小时制:<?php echo date('Y-m-d h:i:s'); ?>" value="Y-m-d Ah:i:s"></el-option>
        <el-option label="24小时制:<?php echo date('Y-m-d H:i:s'); ?>" value="Y-m-d H:i:s"></el-option>
        <el-option label="<?php echo date('Y-m-d H:i'); ?>" value="Y-m-d H:i"></el-option>
        <el-option label="<?php echo date('Y-m-d'); ?>" value="Y-m-d"></el-option>
        <el-option label="<?php echo date('m-d'); ?>" value="m-d"></el-option>
    </el-select>
</el-form-item>

<el-form-item label="默认值">
    <el-radio v-model="formData.setting.defaulttype" label="0">无</el-radio>
    <el-radio v-model="formData.setting.defaulttype" label="1">当前时间</el-radio>
</el-form-item>