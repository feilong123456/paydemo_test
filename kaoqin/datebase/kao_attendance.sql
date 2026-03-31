CREATE TABLE `kao_attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(100) NOT NULL COMMENT '考勤分组名称',
  `attendanceshift_id` int(10) DEFAULT NULL COMMENT '考勤班次',
  `hobbydata` set('1','2','3','4','5','6','7') NOT NULL DEFAULT '' COMMENT '打卡时间:1=周一,2=周二,3=周三,4=周四,5=周五,6=周六,7=周日',
  `starttime` int(10) DEFAULT NULL COMMENT '打卡开始时间',
  `endtime` int(10) DEFAULT NULL COMMENT '下班打卡时间',
  `group_ids` varchar(100) NOT NULL COMMENT '适用人员范围',
  `maincontent` varchar(255) DEFAULT NULL COMMENT '适用范围说明',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '修改时间',
  `typedata` enum('0','1','2') DEFAULT '0' COMMENT '打卡方式:0=tg,1=web,2=两者均可',
  `dates` text COMMENT '休假时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考勤组设置\r\n';
