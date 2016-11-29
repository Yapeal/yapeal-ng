-- Sql/Corp/CreateContracts.sql
-- version 20160629053418.719
CREATE TABLE "{schema}"."{tablePrefix}corpContracts" LIKE "{schema}"."{tablePrefix}charContracts";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053418.719');
COMMIT;
