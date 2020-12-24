
DROP TABLE IF EXISTS `cms_content_category`;
CREATE TABLE `cms_content_category` (
  `catid` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `module` varchar(15) NOT NULL DEFAULT '' COMMENT '所属模块[废弃]',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型：0内容栏目， 1栏目组',
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '模型ID',
  `domain` varchar(200) NOT NULL DEFAULT '' COMMENT '栏目绑定域名[废弃]',
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有父ID',
  `child` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否存在子栏目，1存在[废弃]',
  `arrchildid` mediumtext COMMENT '所有子栏目ID[废弃]',
  `catname` varchar(30) NOT NULL DEFAULT '' COMMENT '栏目名称',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '栏目图片',
  `description` mediumtext COMMENT '栏目描述',
  `parentdir` varchar(100) NOT NULL DEFAULT '' COMMENT '父目录',
  `catdir` varchar(30) NOT NULL DEFAULT '' COMMENT '栏目目录',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目点击数',
  `setting` mediumtext COMMENT '相关配置信息',
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `sethtml` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否生成静态[废弃]',
  `letter` varchar(30) NOT NULL DEFAULT '' COMMENT '栏目拼音[废弃]',
  PRIMARY KEY (`catid`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='栏目表';

-- TODO: 废弃
DROP TABLE IF EXISTS `cms_content_category_field`;
CREATE TABLE `cms_content_category_field` (
  `fid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '自增长id',
  `catid` smallint(5) NOT NULL DEFAULT '0' COMMENT '栏目ID',
  `fieldname` varchar(30) NOT NULL DEFAULT '' COMMENT '字段名',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT '类型,input',
  `setting` mediumtext COMMENT '其他',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='栏目扩展字段列表';


DROP TABLE IF EXISTS `cms_content_category_priv`;
CREATE TABLE `cms_content_category_priv` (
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `roleid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '角色或者组ID',
  `is_admin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为管理员 1、管理员',
  `action` char(30) NOT NULL DEFAULT '' COMMENT '动作',
  KEY `catid` (`catid`,`roleid`,`is_admin`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='栏目权限表';


DROP TABLE IF EXISTS `cms_content_model`;
CREATE TABLE `cms_content_model` (
  `modelid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL DEFAULT '' COMMENT '模型名称',
  `description` char(100) NOT NULL DEFAULT '' COMMENT '描述',
  `tablename` varchar(64) NOT NULL DEFAULT '' COMMENT '表名',
  `setting` text COMMENT '配置信息',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `items` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '信息数',
  `enablesearch` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启全站搜索',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用 1禁用',
  `default_style` char(30) NOT NULL DEFAULT '' COMMENT '风格',
  `category_template` varchar(128) NOT NULL DEFAULT '' COMMENT '栏目模板',
  `list_template` varchar(128) NOT NULL DEFAULT '' COMMENT '列表模板',
  `show_template` varchar(128) NOT NULL DEFAULT '' COMMENT '内容模板',
  `list_customtemplate` varchar(128) NOT NULL DEFAULT '' COMMENT '后台列表页',
  `js_template` varchar(128) NOT NULL DEFAULT '' COMMENT 'JS模板',
  `sort` tinyint(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '模块标识',
  `add_customtemplate` varchar(128) NOT NULL DEFAULT '' COMMENT '添加信息模板',
  `edit_customtemplate` varchar(128) NOT NULL DEFAULT '' COMMENT '编辑信息模板',
  `engine` varchar(32) NOT NULL DEFAULT 'InnoDB' COMMENT 'sql引擎',
  `charset` varchar(32) NOT NULL DEFAULT 'utf8mb4' COMMENT 'sql字符集',
  PRIMARY KEY (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='内容模型列表';


DROP TABLE IF EXISTS `cms_content_model_field`;
CREATE TABLE `cms_content_model_field` (
   `fieldid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '模型ID',
  `field` varchar(64) NOT NULL DEFAULT '' COMMENT '字段名',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '别名',
  `tips` text COMMENT '字段提示',
  `css` varchar(30) NOT NULL DEFAULT '' COMMENT '表单样式',
  `minlength` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最小值',
  `maxlength` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大值',
  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '数据校验正则',
  `errortips` varchar(255) NOT NULL DEFAULT '' COMMENT '数据校验未通过的提示信息',
  `formtype` varchar(20) NOT NULL DEFAULT '' COMMENT '字段类型',
  `setting` mediumtext,
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否内部字段 1是',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统字段 1 是',
  `isunique` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '值唯一',
  `isbase` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '作为基本信息',
  `listorder` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `legnth` smallint(5) unsigned NOT NULL DEFAULT '255' COMMENT '字段长度',
  `default` varchar(255) NOT NULL DEFAULT '' COMMENT '默认值',
  `field_type` varchar(32) NOT NULL DEFAULT '' COMMENT 'sql类型',
  `field_is_null` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'sql字段是否可为NULL',
  `field_key` varchar(32) NOT NULL DEFAULT '' COMMENT 'sql key类型',
  `field_extra` varchar(32) NOT NULL DEFAULT '' COMMENT 'sql extra ',
  PRIMARY KEY (`fieldid`),
  KEY `modelid` (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模型字段列表';
