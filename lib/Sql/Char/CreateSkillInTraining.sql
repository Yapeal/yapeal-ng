-- Sql/Char/CreateSkillInTraining.sql
-- version 20160201053950.120
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053950.120')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
