CREATE TABLE `kao_yuebao` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `admin_id` int(10) NOT NULL COMMENT '员工姓名',
  `year` int(5) DEFAULT NULL COMMENT '年',
  `month` int(2) DEFAULT NULL COMMENT '月',
  `needday` int(5) DEFAULT NULL COMMENT '应出勤天数',
  `shijiday` int(5) DEFAULT NULL COMMENT '实际出勤天数',
  `chidaoday` int(5) DEFAULT NULL COMMENT '迟到天数',
  `choukaday` int(5) DEFAULT NULL COMMENT '抽卡迟到天数',
  `zaotuiday` int(5) DEFAULT NULL COMMENT '早退天数',
  `qingjiaci` int(5) DEFAULT NULL COMMENT '请假次数',
  `qingjiaday` varchar(255) DEFAULT NULL COMMENT '请假天数',
  `remake` varchar(255) DEFAULT NULL COMMENT '备注',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考勤月报';
