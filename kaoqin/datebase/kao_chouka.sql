CREATE TABLE `kao_chouka` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `tuisongtime` int(10) DEFAULT NULL COMMENT '系统机器人推送时间',
  `choukatime` int(10) NOT NULL COMMENT '系统分配的抽卡时间',
  `chouendtime` int(10) NOT NULL COMMENT '系统分配最晚打卡的抽卡时间',
  `admin_id` int(10) NOT NULL COMMENT '员工名称',
  `dakatime` int(10) DEFAULT NULL COMMENT '打卡时间',
  `dakalist` enum('0','1','2') DEFAULT '2' COMMENT '打卡方式:0=TG,1=Web,2=未定义',
  `year` int(5) DEFAULT NULL COMMENT '年',
  `month` int(5) DEFAULT NULL COMMENT '月',
  `day` int(5) DEFAULT NULL COMMENT '日',
  `jilunlist` enum('1','2','3','4') DEFAULT '1' COMMENT '第几轮:1=第一轮,2=第二轮,3=第三轮,4=第四轮',
  `dijige` int(10) DEFAULT '1' COMMENT '第几次抽查',
  `token` varchar(255) DEFAULT NULL COMMENT '随机字符串',
  `statuslist` enum('0','1','2') DEFAULT '0' COMMENT '状态:0=未打卡,1=正常打卡,2=已迟到',
  `istuisonglist` enum('0','1') DEFAULT '0' COMMENT '推送状态:0=未推送,1=已推送',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
