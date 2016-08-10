-- Sql/Corp/CreateAccountBalance.sql
-- version 20160629053411.529
CREATE TABLE "{schema}"."{tablePrefix}corpAccountBalance" LIKE "{schema}"."{tablePrefix}charAccountBalance";
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629053411.529')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
