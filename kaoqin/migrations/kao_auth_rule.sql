-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-01-10 12:11:29
-- 服务器版本： 5.7.44-log
-- PHP 版本： 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `kaoqinguanli`
--

-- --------------------------------------------------------

--
-- 表的结构 `kao_auth_rule`
--

DROP TABLE IF EXISTS `kao_auth_rule`;

CREATE TABLE `kao_auth_rule` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` enum('menu','file') NOT NULL DEFAULT 'file' COMMENT 'menu为菜单,file为权限节点',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) DEFAULT '' COMMENT '图标',
  `url` varchar(255) DEFAULT '' COMMENT '规则URL',
  `condition` varchar(255) DEFAULT '' COMMENT '条件',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `menutype` enum('addtabs','blank','dialog','ajax') DEFAULT NULL COMMENT '菜单类型',
  `extend` varchar(255) DEFAULT '' COMMENT '扩展属性',
  `py` varchar(30) DEFAULT '' COMMENT '拼音首字母',
  `pinyin` varchar(100) DEFAULT '' COMMENT '拼音',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) DEFAULT '' COMMENT '状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点表' ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `kao_auth_rule`
--

INSERT INTO `kao_auth_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`) VALUES
(1, 'file', 0, 'dashboard', '首页', 'fa fa-dashboard', '', '', '用于展示当前系统中的统计数据、统计报表及重要实时数据', 1, 'addtabs', '', 'sy', 'shouye', 1491635035, 1704268148, 143, 'normal'),
(2, 'file', 0, 'general', 'General', 'fa fa-cogs', '', '', '', 0, NULL, '', 'cggl', 'changguiguanli', 1491635035, 1704167205, 137, 'normal'),
(3, 'file', 0, 'category', 'Category', 'fa fa-leaf', '', '', 'Category tips', 0, NULL, '', 'flgl', 'fenleiguanli', 1491635035, 1491635035, 119, 'normal'),
(4, 'file', 0, 'addon', 'Addon', 'fa fa-rocket', '', '', 'Addon tips', 0, NULL, '', 'cjgl', 'chajianguanli', 1491635035, 1704681772, 0, 'normal'),
(5, 'file', 0, 'auth', 'Auth', 'fa fa-group', '', '', '', 1, NULL, '', 'qxgl', 'quanxianguanli', 1491635035, 1491635035, 99, 'normal'),
(6, 'file', 2, 'general/config', 'Config', 'fa fa-cog', '', '', 'Config tips', 1, NULL, '', 'xtpz', 'xitongpeizhi', 1491635035, 1491635035, 60, 'normal'),
(7, 'file', 2, 'general/attachment', 'Attachment', 'fa fa-file-image-o', '', '', 'Attachment tips', 1, NULL, '', 'fjgl', 'fujianguanli', 1491635035, 1491635035, 53, 'normal'),
(8, 'file', 2, 'general/profile', 'Profile', 'fa fa-user', '', '', '', 1, NULL, '', 'grzl', 'gerenziliao', 1491635035, 1491635035, 34, 'normal'),
(9, 'file', 0, 'auth/admin', '员工管理', 'fa fa-user', '', '', '一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成', 1, 'addtabs', '', 'yggl', 'yuangongguanli', 1491635035, 1704167248, 118, 'normal'),
(10, 'file', 5, 'auth/adminlog', 'Admin log', 'fa fa-list-alt', '', '', 'Admin log tips', 1, NULL, '', 'glyrz', 'guanliyuanrizhi', 1491635035, 1491635035, 113, 'normal'),
(11, 'file', 5, 'auth/group', 'Group', 'fa fa-group', '', '', 'Group tips', 1, NULL, '', 'jsz', 'juesezu', 1491635035, 1491635035, 109, 'normal'),
(12, 'file', 5, 'auth/rule', 'Rule', 'fa fa-bars', '', '', 'Rule tips', 1, NULL, '', 'cdgz', 'caidanguize', 1491635035, 1491635035, 104, 'normal'),
(13, 'file', 1, 'dashboard/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 136, 'normal'),
(14, 'file', 1, 'dashboard/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 135, 'normal'),
(15, 'file', 1, 'dashboard/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 133, 'normal'),
(16, 'file', 1, 'dashboard/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 134, 'normal'),
(17, 'file', 1, 'dashboard/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 132, 'normal'),
(18, 'file', 6, 'general/config/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 52, 'normal'),
(19, 'file', 6, 'general/config/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 51, 'normal'),
(20, 'file', 6, 'general/config/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 50, 'normal'),
(21, 'file', 6, 'general/config/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 49, 'normal'),
(22, 'file', 6, 'general/config/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 48, 'normal'),
(23, 'file', 7, 'general/attachment/index', 'View', 'fa fa-circle-o', '', '', 'Attachment tips', 0, NULL, '', '', '', 1491635035, 1491635035, 59, 'normal'),
(24, 'file', 7, 'general/attachment/select', 'Select attachment', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 58, 'normal'),
(25, 'file', 7, 'general/attachment/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 57, 'normal'),
(26, 'file', 7, 'general/attachment/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 56, 'normal'),
(27, 'file', 7, 'general/attachment/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 55, 'normal'),
(28, 'file', 7, 'general/attachment/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 54, 'normal'),
(29, 'file', 8, 'general/profile/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 33, 'normal'),
(30, 'file', 8, 'general/profile/update', 'Update profile', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 32, 'normal'),
(31, 'file', 8, 'general/profile/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 31, 'normal'),
(32, 'file', 8, 'general/profile/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 30, 'normal'),
(33, 'file', 8, 'general/profile/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 29, 'normal'),
(34, 'file', 8, 'general/profile/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 28, 'normal'),
(35, 'file', 3, 'category/index', 'View', 'fa fa-circle-o', '', '', 'Category tips', 0, NULL, '', '', '', 1491635035, 1491635035, 142, 'normal'),
(36, 'file', 3, 'category/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 141, 'normal'),
(37, 'file', 3, 'category/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 140, 'normal'),
(38, 'file', 3, 'category/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 139, 'normal'),
(39, 'file', 3, 'category/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 138, 'normal'),
(40, 'file', 9, 'auth/admin/index', 'View', 'fa fa-circle-o', '', '', 'Admin tips', 0, NULL, '', '', '', 1491635035, 1491635035, 117, 'normal'),
(41, 'file', 9, 'auth/admin/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 116, 'normal'),
(42, 'file', 9, 'auth/admin/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 115, 'normal'),
(43, 'file', 9, 'auth/admin/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 114, 'normal'),
(44, 'file', 10, 'auth/adminlog/index', 'View', 'fa fa-circle-o', '', '', 'Admin log tips', 0, NULL, '', '', '', 1491635035, 1491635035, 112, 'normal'),
(45, 'file', 10, 'auth/adminlog/detail', 'Detail', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 111, 'normal'),
(46, 'file', 10, 'auth/adminlog/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 110, 'normal'),
(47, 'file', 11, 'auth/group/index', 'View', 'fa fa-circle-o', '', '', 'Group tips', 0, NULL, '', '', '', 1491635035, 1491635035, 108, 'normal'),
(48, 'file', 11, 'auth/group/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 107, 'normal'),
(49, 'file', 11, 'auth/group/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 106, 'normal'),
(50, 'file', 11, 'auth/group/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 105, 'normal'),
(51, 'file', 12, 'auth/rule/index', 'View', 'fa fa-circle-o', '', '', 'Rule tips', 0, NULL, '', '', '', 1491635035, 1491635035, 103, 'normal'),
(52, 'file', 12, 'auth/rule/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 102, 'normal'),
(53, 'file', 12, 'auth/rule/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 101, 'normal'),
(54, 'file', 12, 'auth/rule/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 100, 'normal'),
(55, 'file', 4, 'addon/index', 'View', 'fa fa-circle-o', '', '', 'Addon tips', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(56, 'file', 4, 'addon/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(57, 'file', 4, 'addon/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(58, 'file', 4, 'addon/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(59, 'file', 4, 'addon/downloaded', 'Local addon', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(60, 'file', 4, 'addon/state', 'Update state', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(63, 'file', 4, 'addon/config', 'Setting', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(64, 'file', 4, 'addon/refresh', 'Refresh', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(65, 'file', 4, 'addon/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(66, 'file', 0, 'user', 'User', 'fa fa-user-circle', '', '', '', 0, NULL, '', 'hygl', 'huiyuanguanli', 1491635035, 1704167202, 0, 'normal'),
(67, 'file', 66, 'user/user', 'User', 'fa fa-user', '', '', '', 1, NULL, '', 'hygl', 'huiyuanguanli', 1491635035, 1491635035, 0, 'normal'),
(68, 'file', 67, 'user/user/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(69, 'file', 67, 'user/user/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(70, 'file', 67, 'user/user/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(71, 'file', 67, 'user/user/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(72, 'file', 67, 'user/user/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(73, 'file', 66, 'user/group', 'User group', 'fa fa-users', '', '', '', 1, NULL, '', 'hyfz', 'huiyuanfenzu', 1491635035, 1491635035, 0, 'normal'),
(74, 'file', 73, 'user/group/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(75, 'file', 73, 'user/group/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(76, 'file', 73, 'user/group/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(77, 'file', 73, 'user/group/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(78, 'file', 73, 'user/group/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(79, 'file', 66, 'user/rule', 'User rule', 'fa fa-circle-o', '', '', '', 1, NULL, '', 'hygz', 'huiyuanguize', 1491635035, 1491635035, 0, 'normal'),
(80, 'file', 79, 'user/rule/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(81, 'file', 79, 'user/rule/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(82, 'file', 79, 'user/rule/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(83, 'file', 79, 'user/rule/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(84, 'file', 79, 'user/rule/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal'),
(85, 'file', 0, 'kaoqin', '考勤管理', 'fa fa-list', '', '', '', 1, 'addtabs', '', 'kqgl', 'kaoqinguanli', 1704264804, 1704266499, 0, 'normal'),
(116, 'file', 0, 'laoban', '报告', 'fa fa-list', '', '', '', 1, 'addtabs', '', 'bg', 'baogao', 1704272057, 1704522373, 45, 'normal'),
(117, 'file', 178, 'laoban/approve', '请假记录', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'qjjl', 'qingjiajilu', 1704272057, 1768017999, 0, 'normal'),
(118, 'file', 117, 'laoban/approve/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1704272057, 1704684511, 0, 'normal'),
(119, 'file', 117, 'laoban/approve/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1704272057, 1704684511, 0, 'normal'),
(120, 'file', 117, 'laoban/approve/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1704272057, 1704684511, 0, 'normal'),
(121, 'file', 117, 'laoban/approve/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1704272057, 1704684511, 0, 'normal'),
(122, 'file', 117, 'laoban/approve/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1704272057, 1704684511, 0, 'normal'),
(123, 'file', 116, 'laoban/yuebao', '月报记录', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'ybjl', 'yuebaojilu', 1704272084, 1704272162, 0, 'normal'),
(124, 'file', 123, 'laoban/yuebao/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1704272084, 1704272084, 0, 'normal'),
(125, 'file', 123, 'laoban/yuebao/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1704272084, 1704272084, 0, 'normal'),
(126, 'file', 123, 'laoban/yuebao/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1704272084, 1704272084, 0, 'normal'),
(127, 'file', 123, 'laoban/yuebao/del', '删除', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'sc', 'shanchu', 1704272084, 1704528670, 0, 'hidden'),
(128, 'file', 123, 'laoban/yuebao/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1704272084, 1704272084, 0, 'normal'),
(129, 'file', 178, 'laoban/daka', '打卡记录', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'dkjl', 'dakajilu', 1704272111, 1768017977, 0, 'normal'),
(130, 'file', 129, 'laoban/daka/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1704272111, 1704272111, 0, 'normal'),
(131, 'file', 129, 'laoban/daka/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1704272111, 1704272111, 0, 'normal'),
(132, 'file', 129, 'laoban/daka/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1704272111, 1704272111, 0, 'normal'),
(133, 'file', 129, 'laoban/daka/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1704272111, 1704272111, 0, 'normal'),
(134, 'file', 129, 'laoban/daka/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1704272111, 1704272111, 0, 'normal'),
(135, 'file', 178, 'laoban/ribao', '员工日报', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'ygrb', 'yuangongribao', 1704272270, 1768018006, 0, 'normal'),
(136, 'file', 135, 'laoban/ribao/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1704272270, 1704272270, 0, 'normal'),
(137, 'file', 135, 'laoban/ribao/recyclebin', '回收站', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hsz', 'huishouzhan', 1704272270, 1704272270, 0, 'normal'),
(138, 'file', 135, 'laoban/ribao/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1704272270, 1704272270, 0, 'normal'),
(139, 'file', 135, 'laoban/ribao/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1704272270, 1704272270, 0, 'normal'),
(140, 'file', 135, 'laoban/ribao/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1704272270, 1704272270, 0, 'normal'),
(141, 'file', 135, 'laoban/ribao/destroy', '真实删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zssc', 'zhenshishanchu', 1704272270, 1704272270, 0, 'normal'),
(142, 'file', 135, 'laoban/ribao/restore', '还原', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hy', 'huanyuan', 1704272270, 1704272270, 0, 'normal'),
(143, 'file', 135, 'laoban/ribao/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1704272270, 1704272270, 0, 'normal'),
(144, 'file', 85, 'kaoqin/attendance', '考勤组设置', 'fa fa-circle-o', '', '', '', 1, NULL, '', 'kqzsz', 'kaoqinzushezhi', 1704340898, 1704340898, 0, 'normal'),
(145, 'file', 144, 'kaoqin/attendance/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1704340898, 1704340898, 0, 'normal'),
(146, 'file', 144, 'kaoqin/attendance/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1704340898, 1704340898, 0, 'normal'),
(147, 'file', 144, 'kaoqin/attendance/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1704340898, 1704340898, 0, 'normal'),
(148, 'file', 144, 'kaoqin/attendance/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1704340898, 1704340898, 0, 'normal'),
(149, 'file', 144, 'kaoqin/attendance/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1704340898, 1704340898, 0, 'normal'),
(150, 'file', 85, 'kaoqin/attendanceshift', '考勤班次', 'fa fa-circle-o', '', '', '', 1, NULL, '', 'kqbc', 'kaoqinbanci', 1704340909, 1704340909, 0, 'normal'),
(151, 'file', 150, 'kaoqin/attendanceshift/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1704340909, 1704340909, 0, 'normal'),
(152, 'file', 150, 'kaoqin/attendanceshift/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1704340909, 1704340909, 0, 'normal'),
(153, 'file', 150, 'kaoqin/attendanceshift/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1704340909, 1704340909, 0, 'normal'),
(154, 'file', 150, 'kaoqin/attendanceshift/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1704340909, 1704340909, 0, 'normal'),
(155, 'file', 150, 'kaoqin/attendanceshift/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1704340909, 1704340909, 0, 'normal'),
(156, 'file', 0, 'command', '在线命令管理', 'fa fa-terminal', '', '', '', 0, NULL, '', 'zxmlgl', 'zaixianminglingguanli', 1704681431, 1768017962, 0, 'normal'),
(157, 'file', 156, 'command/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1704681431, 1704681431, 0, 'normal'),
(158, 'file', 156, 'command/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1704681431, 1704681431, 0, 'normal'),
(159, 'file', 156, 'command/detail', '详情', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'xq', 'xiangqing', 1704681431, 1704681431, 0, 'normal'),
(160, 'file', 156, 'command/command', '生成并执行命令', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'scbzxml', 'shengchengbingzhixingmingling', 1704681431, 1704681431, 0, 'normal'),
(161, 'file', 156, 'command/execute', '再次执行命令', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zczxml', 'zaicizhixingmingling', 1704681431, 1704681431, 0, 'normal'),
(162, 'file', 156, 'command/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1704681431, 1704681431, 0, 'normal'),
(163, 'file', 156, 'command/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1704681431, 1704681431, 0, 'normal'),
(164, 'file', 117, 'laoban/approve/shenhe', '审核通过请假', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'shtgqj', 'shenhetongguoqingjia', 1704684511, 1704684511, 0, 'normal'),
(165, 'file', 117, 'laoban/approve/jujue', '拒绝通过请假', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'jjtgqj', 'jujuetongguoqingjia', 1704684511, 1704684511, 0, 'normal'),
(166, 'file', 178, 'laoban/chouka', '抽卡记录', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'ckjl', 'choukajilu', 1705547278, 1768017959, 0, 'normal'),
(167, 'file', 166, 'laoban/chouka/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1705547278, 1705547278, 0, 'normal'),
(168, 'file', 166, 'laoban/chouka/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1705547278, 1705547278, 0, 'normal'),
(169, 'file', 166, 'laoban/chouka/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1705547278, 1705547278, 0, 'normal'),
(170, 'file', 166, 'laoban/chouka/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1705547278, 1705547278, 0, 'normal'),
(171, 'file', 166, 'laoban/chouka/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1705547278, 1705547278, 0, 'normal'),
(172, 'file', 116, 'laoban/choukaxin', '出勤打卡', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'cqdk', 'chuqindaka', 1705651561, 1706004157, 0, 'normal'),
(173, 'file', 172, 'laoban/choukaxin/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1705651561, 1705651561, 0, 'normal'),
(174, 'file', 172, 'laoban/choukaxin/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1705651561, 1705651561, 0, 'normal'),
(175, 'file', 172, 'laoban/choukaxin/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1705651561, 1705651561, 0, 'normal'),
(176, 'file', 172, 'laoban/choukaxin/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1705651561, 1705651561, 0, 'normal'),
(177, 'file', 172, 'laoban/choukaxin/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1705651561, 1705651561, 0, 'normal'),
(178, 'file', 0, 'kaoqinmingxi', '考勤明细', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'kqmx', 'kaoqinmingxi', 1768017939, 1768017950, 0, 'normal');

--
-- 转储表的索引
--

--
-- 表的索引 `kao_auth_rule`
--
ALTER TABLE `kao_auth_rule`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `name` (`name`) USING BTREE,
  ADD KEY `pid` (`pid`) USING BTREE,
  ADD KEY `weigh` (`weigh`) USING BTREE;

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `kao_auth_rule`
--
ALTER TABLE `kao_auth_rule`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
