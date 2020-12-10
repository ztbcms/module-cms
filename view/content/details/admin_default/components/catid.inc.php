<script type="text/x-template" id="default-catid">
    <div class="default-catid">
        <el-form-item v-if="loaded" :label="field.name" required>
            <el-select v-model="result" placeholder="请选择">
                <el-option
                        v-for="item in categoryList"
                        :key="item.catid"
                        :label="item.catname"
                        :value="item.catid">
                    <template v-for="i in item.level * 2"><span>&nbsp;</span></template>
                    <template v-if="item.level > 0"><span> ∟</span></template>
                    <span>{{ item.catname }}</span>
                </el-option>
            </el-select>
        </el-form-item>
    </div>
</script>


<script>
    $(function () {
        Vue.component('default-catid', {
            template: '#default-catid',
            model: {
                // 指定接收 v-model 值/修改事件
                prop: 'field_value',
                event: 'change'
            },
            props: {
                field: {
                    type: Object,
                    defalut: function () {
                        return {};
                    }
                },
                field_data: {
                    type: Object,
                    defalut: function () {
                        return {};
                    }
                },
                field_value: {
                    defualt: 0
                }
            },
            watch: {},
            computed: {
                result: {
                    get: function () {
                        return Number(this.field_value)
                    },
                    set: function (val) {
                        // 调用父组件的customFunc方法，将变量放在第二个参数，即可改变父组件的变量
                        this.$emit('change', val)
                    }
                }
            },
            data: function () {
                return {
                    // 是否已请求完列表数据
                    loaded: false,
                    categoryList: []
                }
            },
            created: function () {
            },
            mounted: function () {
                this.getCategoryList()
            },
            methods: {
                handleChange: function (code) {
                },
                getCategoryList: function () {
                    var that = this
                    this.httpGet("{:api_url('/cms/Content/details')}", {_action: 'getCategoryList'}, function (res) {
                        if (res.status) {
                            that.categoryList = res.data
                            that.loaded = true
                        }
                    })
                }
            }
        });
    })
</script>