-- Sql/Corp/CreateContracts.sql
-- version 20160629053418.719
CREATE TABLE "{database}"."{table_prefix}corpContracts" LIKE "{database}"."{table_prefix}charContracts";
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053418.719')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
