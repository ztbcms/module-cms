
DROP TABLE IF EXISTS `cms_content_category`;
CREATE TABLE `cms_content_category` (
  `catid` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `catname` varchar(64) NOT NULL COMMENT '栏目名称',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类别 0内容 1栏目组 2外部链接',
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '模型ID',
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有父ID',
  `arrchildid` mediumtext COMMENT '所有子栏目ID',
  `catdir` varchar(30) NOT NULL COMMENT '栏目目录',
  `parentdir` varchar(100) NOT NULL DEFAULT '' COMMENT '父目录',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '链接地址',
  `setting` mediumtext COMMENT '相关配置信息',
  `order` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `add_customtemplate` varchar(64) NOT NULL DEFAULT '',
  `edit_customtemplate` varchar(64) NOT NULL DEFAULT '',
  `list_customtemplate` varchar(64) NOT NULL DEFAULT '',
  `category_template` varchar(64) NOT NULL DEFAULT '',
  `list_template` varchar(64) NOT NULL DEFAULT '',
  `show_template` varchar(64) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`catid`)
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
  `table` varchar(64) NOT NULL COMMENT '表名',
  `setting` text COMMENT '配置信息',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用 1禁用',
  `category_template` varchar(128) NOT NULL DEFAULT '' COMMENT '栏目模板',
  `list_template` varchar(128) NOT NULL DEFAULT '' COMMENT '列表模板',
  `show_template` varchar(128) NOT NULL DEFAULT '' COMMENT '内容模板',
  `list_customtemplate` varchar(128) NOT NULL DEFAULT '' COMMENT '后台列表页',
  `add_customtemplate` varchar(128) NOT NULL DEFAULT '' COMMENT '添加信息模板',
  `edit_customtemplate` varchar(128) NOT NULL DEFAULT '' COMMENT '编辑信息模板',
  `sort` tinyint(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `engine` varchar(32) NOT NULL DEFAULT 'InnoDB' COMMENT 'sql引擎',
  `charset` varchar(32) NOT NULL DEFAULT 'utf8mb4' COMMENT 'sql字符集',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='内容模型列表';


DROP TABLE IF EXISTS `cms_content_model_field`;
CREATE TABLE `cms_content_model_field` (
  `fieldid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '模型ID',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '别名',
  `tips` text COMMENT '字段提示',
  `setting` mediumtext,
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否内部字段 1是',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统字段 1 是',
  `isbase` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '作为基本信息',
  `listorder` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `form_type` varchar(32) NOT NULL COMMENT '字段的表单类型',
  `field` varchar(64) NOT NULL COMMENT '字段名',
  `field_type` varchar(32) NOT NULL COMMENT 'sql类型',
  `field_length` smallint(5) unsigned NOT NULL DEFAULT '255' COMMENT '字段长度',
  `default` varchar(255) NOT NULL DEFAULT '' COMMENT '默认值',
  `field_is_null` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'sql字段是否可为NULL',
  `field_key` varchar(32) NOT NULL DEFAULT '' COMMENT 'sql key类型',
  `field_extra` varchar(32) NOT NULL DEFAULT '' COMMENT 'sql extra ',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `enable_edit_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '编辑页是否可编辑 0否 1是(默认)',
  `enable_delete` tinyint(1) NOT NULL DEFAULT '1' COMMENT '字段是否可以删除 0否 1是(默认)',
  `enable_list_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '列表页中是否展示 0否 1是（默认）',
  PRIMARY KEY (`fieldid`),
  KEY `modelid` (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模型字段列表';
