CREATE TABLE `kao_ribao` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `admin_id` int(10) NOT NULL COMMENT '员工名称',
  `maincontent` text COMMENT '日报',
  `createtime` int(10) DEFAULT NULL COMMENT '日报时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '修改时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工日报';
