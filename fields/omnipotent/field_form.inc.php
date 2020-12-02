<el-form-item label="默认值" prop="formData.setting.formtext">
    <el-input type="textarea" v-model="formData.setting.formtext"  :autosize="{minRows: 8, maxRows: 8}" >
    </el-input>
    <span>
          <br/>例如：&lt;input type='text' name='info[<font style="color: #F00">当前字段名</font>]' id='voteid' value='<font style="color: #F00">{FIELD_VALUE}</font>' style='50' &gt;
            <br/><font style="color: #F00">{FIELD_VALUE}</font> 当前万能字段的值，<font style="color: #F00">{MODELID}</font> 当前模型ID，<font style="color: #F00">{ID}</font>当前信息ID，添加时为0。
            <br/>除了以上特定标签外，可以直接使用 “<font style="color: #F00"><b>$</b>字段名</font>”的方式，获取其他字段的值。
            <br/>在“表单”里可以直接使用<font style="color: #F00">php语法</font>或者<font style="color: #F00">模板标签</font>。
            <br/>提示：在这里，你可以把表单需要的任何效果，做成HTML+JS甚至是配合php来实现
            <br/><font style="color: #F00">如果要保存数组类的值，请字段类型选择“text，mediumtext，longtext”</font>
    </span>
</el-form-item>

<el-form-item label="字段类型" prop="formData.setting.fieldtype">
    <el-select v-model="formData.setting.fieldtype" placeholder="请选择" :style="{width: '100%'}">
        <el-option label="字符型0-255字节(VARCHAR)" value="varchar"></el-option>
        <el-option label="定长字符型0-255字节(CHAR)" value="char"></el-option>
        <el-option label="小型字符型(TEXT)" value="text"></el-option>
        <el-option label="中型字符型(MEDIUMTEXT)" value="mediumtext"></el-option>
        <el-option label="大型字符型(LONGTEXT)" value="longtext"></el-option>
        <el-option label="整数 TINYINT(3)" value="tinyint"></el-option>
        <el-option label="整数 SMALLINT(5)" value="smallint"></el-option>
        <el-option label="整数 MEDIUMINT(8)" value="mediumint"></el-option>
        <el-option label="整数 INT(10)" value="int"></el-option>
        <el-option label="超大数值型(BIGINT)" value="bigint"></el-option>
        <el-option label="数值浮点型(FLOAT)" value="float"></el-option>
        <el-option label="数值双精度型(DOUBLE)" value="double"></el-option>
        <el-option label="日期型(DATE)" value="date"></el-option>
        <el-option label="日期时间型(DATETIME)" value="datetime"></el-option>
    </el-select>

    <el-radio v-model="formData.setting.minnumber" label="-1">整数</el-radio>
    <el-radio v-model="formData.setting.minnumber" label="1">正整数</el-radio>
</el-form-item>