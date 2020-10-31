### 内容管理模块

##### 安装以下内容（在tp6文件夹下）
composer require liliuwei/thinkphp-jump

```
│ 内容模块
│
├─ 内容管理
├──── 管理内容 {{domain}}/Content/Content/index => {{domain}}/home/cms/content/index
│
├─ 内容相关设置
├──── 栏目列表 {{domain}}/Content/Category/index => {{domain}}/home/cms/category/index
├──── 模型管理 {{domain}}/Content/Models/index => {{domain}}/home/cms/model/index
│
```

暂未处理：
1、模型管理-字段管理-预览模型
2、\app\admin\libs\system\Rbac::ableAccess  鉴权失败，暂且直接return true

