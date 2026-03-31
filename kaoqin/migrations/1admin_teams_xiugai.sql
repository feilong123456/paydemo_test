-- Teams 考勤机器人：admin 表增加 Teams 对话绑定字段
-- 执行后，后台员工可绑定 Teams，到点推送抽查打卡到 Teams
--
-- 后台 .env 需配置（用于 naozhong 推送到 Teams）：
--   teams.bot_service_url = http://你的Teams-Bot地址:3978

ALTER TABLE kao_admin
  ADD COLUMN teams_conversation_id VARCHAR(512) DEFAULT NULL COMMENT 'Teams 对话ID，用于主动推送',
  ADD COLUMN teams_service_url VARCHAR(512) DEFAULT NULL COMMENT 'Teams Bot ServiceUrl',
  ADD COLUMN teams_tenant_id VARCHAR(512) DEFAULT NULL COMMENT 'Teams 租户ID',
  ADD COLUMN teams_user_id VARCHAR(512) DEFAULT NULL COMMENT '';
