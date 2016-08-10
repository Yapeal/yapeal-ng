-- Sql/Corp/CreateContracts.sql
-- version 20160629053418.719
CREATE TABLE "{schema}"."{table_prefix}corpContracts" LIKE "{schema}"."{table_prefix}charContracts";
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053418.719')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
