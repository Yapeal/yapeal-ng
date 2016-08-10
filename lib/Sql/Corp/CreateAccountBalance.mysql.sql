-- Sql/Corp/CreateAccountBalance.sql
-- version 20160629053411.529
CREATE TABLE "{schema}"."{table_prefix}corpAccountBalance" LIKE "{schema}"."{table_prefix}charAccountBalance";
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053411.529')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
