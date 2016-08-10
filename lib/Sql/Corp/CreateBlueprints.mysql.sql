-- Sql/Corp/CreateBlueprints.sql
-- version 20160629053412.885
CREATE TABLE "{schema}"."{tablePrefix}corpBlueprints" LIKE "{schema}"."{tablePrefix}charBlueprints";
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES
('20160629053412.885')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
