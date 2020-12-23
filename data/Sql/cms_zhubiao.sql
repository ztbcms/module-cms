CREATE TABLE `testv` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `order` int unsigned NOT NULL DEFAULT '0' COMMENT '序号',
  `status` int unsigned NOT NULL DEFAULT '1'  COMMENT '状态状态 99审核通过 1待审核 0审核不通过',
  `user_id` varchar(255) NOT NULL DEFAULT ''  COMMENT '用户ID',
  `admin_id` varchar(255) NOT NULL DEFAULT ''  COMMENT '管理员ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0'  COMMENT '更新时间',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
