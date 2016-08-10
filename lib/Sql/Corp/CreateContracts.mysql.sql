-- Sql/Corp/CreateContracts.sql
-- version 20160629053418.719
CREATE TABLE "{schema}"."{tablePrefix}corpContracts" LIKE "{schema}"."{tablePrefix}charContracts";
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629053418.719')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
