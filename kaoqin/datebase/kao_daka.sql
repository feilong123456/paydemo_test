CREATE TABLE `kao_daka` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `admin_id` int(10) NOT NULL COMMENT '员工名称',
  `dakatime` int(10) NOT NULL COMMENT '打卡年月日',
  `createtime` int(10) NOT NULL COMMENT '具体打卡时间',
  `typelist` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '打卡类型:0=上班打卡,1=抽查打卡,2=下班打卡',
  `year` int(5) NOT NULL COMMENT '年00888',
  `month` int(5) NOT NULL COMMENT '月',
  `ischidao` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否迟到:0=否,1=是',
  `iszaotui` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否早退:0=否,1=是',
  `isqueqin` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否缺勤:0=否,1=是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
