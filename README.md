### 内容管理模块
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

```
暂未处理：
1、字段设置没有进行样式的控制，部分字段没有制作编辑管理
2、未进行推荐位的管理
3、未进行权限的控制  
```

TODO 内置默认的文章模型



varchar 255  （可参与索引的）
varchar 512 较长的 多图，多视频等（不参与索引的）
Text 内容详情，编辑器;  MEDIUMTEXT LONGTEXT
Int 11 
decimal(10,2)


字符 text   
文本 textarea 
编辑器 editor
数字 number
日期时间 datetime 2020-1-1 12:00:11
单图片 image
多图片 images 
单视频 video
多视频 videos
单文件 file
多文件 files
单选 radio
多选 checkbox
下拉单选 select
自定义 custom
栏目 catid

files: 保存名称name、文件url


