-- Sql/Corp/CreateAccountBalance.sql
-- version 20160629053411.529
CREATE TABLE "{database}"."{table_prefix}corpAccountBalance" LIKE "{database}"."{table_prefix}charAccountBalance";
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053411.529')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
