CREATE TABLE `kao_approve` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `admin_id` int(10) NOT NULL COMMENT '员工名称',
  `starttime` int(10) NOT NULL COMMENT '开始时间',
  `endtime` int(10) NOT NULL COMMENT '结束时间',
  `maincontent` text COMMENT '请假原因描述',
  `mainimage` varchar(255) DEFAULT NULL COMMENT '请假图片证明',
  `typelist` enum('0','1','2','3','4') DEFAULT '0' COMMENT '请假类型:0=事假,1=病假,2=年假,3=调休,4=其他',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '修改时间',
  `status` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '审核状态:0=申请中,1=已通过,2=已拒绝',
  `pushadmin_id` int(10) NOT NULL COMMENT '审核人',
  `shenhetime` int(10) DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工请假审批';
